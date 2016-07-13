<?php

include '../conf/config.inc.php'; // app config
include '../lib/classes/Db.class.php'; // db connector, queries

include '../lib/classes/Logsheet.class.php'; // logsheet model
include '../lib/classes/LogsheetCollection.class.php'; // logsheet collection

/**
 * KML files for stations in a network
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class Kml {
  private $_domain;
  private $_meta;
  private $_network;
  private $_stations;
  private $_sortField;

  public function __construct($network) {
    $this->_domain = 'localhost:9090';
    $this->_meta = [
      'station' => [
        'description' => $network . ' Network (sorted by station name)'
      ],
      'last_obs' => [
        'description' => $network . ' Network (sorted by last year occupied)',
        'folder' => 'Last surveyed in %s'
      ],
      'total_years' => [
        'description' => $network . ' Network (sorted by total years occupied)',
        'folder' => '%s year(s) between first/last surveys'
      ]
    ];
    $this->_network = $network;
    $this->_stations = $this->_getStations();
    $this->_sortField = 'station'; // sorted by station name by default
  }

  /**
   * Get KML Body
   *
   * @return $body {String}
   */
  private function _getBody () {
    $body = '';
    $prevValue = '';
    $sortField = $this->_sortField;

    foreach($this->_stations as $station) {

      // Create folders for grouping stations (except when sorting by station)
      if ($sortField !== 'station') {
        $value = $station->$sortField;
        if ($value !== $prevValue) {
          // Close previous folder
          if ($prevValue) {
            $body .= "\n    </Folder>";
          }
          // Open new folder
          $sub = $value;
          if (!$value) {
            $sub = '[unknown]';
          }
          $name = sprintf($this->_meta[$sortField]['folder'], $sub);
          $body .= "\n    <Folder><name>$name</name><open>0</open>";

          $prevValue = $value;
        }
      }

      $placeMark = $this->_getPlaceMark($station);
      $body .= "\n$placeMark";
    }

    // Close final folder (not using folders when sorting by station)
    if ($sortField !== 'station') {
      $body .= "\n    </Folder>";
    }

    return $body;
  }

  /**
   * Get KML footer
   *
   * @return {String}
   */
  private function _getFooter () {
    return "\n  </Document>\n</kml>";
  }

  /**
   * Get KML header
   *
   * @return $header {Xml}
   */
  private function _getHeader () {
    $timestamp = date('D M j, Y H:i:s e');
    $header = '<?xml version="1.0" encoding="UTF-8"?>
    <kml xmlns="http://earth.google.com/kml/2.1">
      <Document>
        <name>' . $this->_meta[$this->_sortField]['description'] . '</name>
        <description>File last updated ' . $timestamp . '</description>
        <Style id="marker">
          <BalloonStyle><text><![CDATA[
            <style>
              .coords { }
              li { }
            </style>
            $[description]
          ]]></text></BalloonStyle>
        </Style>';

    //<LabelStyle><scale>1</scale></LabelStyle>
    //<IconStyle><Icon><href></href></Icon></IconStyle>

    return $header;
  }

  /**
   * Get a list of logsheets for a station
   *
   * @param $station {String}
   *
   * @return $logsheetCollection {Object}
   */
  private function _getLogSheets ($station) {
    $dir = sprintf('%s/stations/%s.dir/%s/logsheets',
      $GLOBALS['DATA_DIR'],
      substr($station, 0, 1),
      $station
    );

    // sort ASC so that 'Front' page (1) is listed before 'Back' page (2)
    $files = getDirContents($dir, $order=SCANDIR_SORT_ASCENDING);

    // Add logsheets to collection
    $logsheetCollection = new LogsheetCollection($station, $this->_network);
    foreach ($files as $file) {
      $logsheetModel = new Logsheet($file);
      $logsheetCollection->add($logsheetModel);
    }

    // Sort collection by date DESC (default)
    $logsheetCollection->sort();

    return $logsheetCollection;
  }

  /**
   * Get KML placemark for a station
   *
   * @param $station {Object}
   *
   * @return $placeMark {String}
   */
  private function _getPlaceMark ($station) {
    $logsheetsCollection = $this->_getLogSheets($station->station);
    $data_collected = false;
    $display_lat = number_format($station->lat, 5, '.', '');
    $display_lon = number_format($station->lon, 5, '.', '');
    $display_station = strtoupper($station->station);

    $logsheets_html = '<ul>';
    foreach($logsheetsCollection->logsheets as $date => $logsheets) {
      $data_collected = true;
      $logsheets_html .= sprintf('<li><a href="http://%s%s/%s">%s</a></li>',
        $this->_domain,
        $logsheetsCollection->path,
        $logsheets[0]->file, // front page or txt-based log
        date('M d, Y', strtotime($date))
      );
    }
    $logsheets_html .= '</ul>';
    if ($data_collected) {
      $logsheets_html = '<p>GPS data was collected on the following dates:</p>' .
        $logsheets_html;
    }

    $description_html = sprintf('<h1>%s</h1>
      <p>%s, %s</p>
      <p><a href="http://%s%s/%s/%s">Station Details</a></p>
      %s',
      $display_station,
      $display_lat,
      $display_lon,
      $this->_domain,
      $GLOBALS['MOUNT_PATH'],
      $this->_network,
      $station->station,
      $logsheets_html
    );

    $placeMark = '      <Placemark>
      <name>' . $display_station . '</name>
      <Point>
        <coordinates>' . $station->lon . ',' . $station->lat . ',0</coordinates>
      </Point>
      <description>' . $description_html . '</description>
      <LookAt>
        <longitude>' . $station->lon . '</longitude>
        <latitude>' . $station->lat . '</latitude>
        <range>10000</range>
      </LookAt>
      <Snippet>' . $display_lat . ', ' . $display_lon . '</Snippet>
      <styleUrl>#marker</styleUrl>
      <visibility>1</visibility>
    </Placemark>';

    return $placeMark;
  }

  /**
   * Get DB recordset containing all stations in network
   *
   * @return {Object}
   */
  private function _getStations() {
    $db = new Db;

    // Db query result: all stations in a given network
    $result = $db->queryStations($this->_network);

    return $result->fetchAll(PDO::FETCH_OBJ);
  }

  /**
   * Render KML content
   */
  public function render () {
    print $this->_getHeader();
    print $this->_getBody();
    print $this->_getFooter();
  }

  /**
   * Set PHP Headers for triggering file download w/ no caching
   */
  public function setPhpHeaders () {
    $expires = date(DATE_RFC2822);
    $filename = $this->_network . '-' . $this->_sortField . '.kml';

    header('Cache-control: no-cache, must-revalidate');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Type: application/xml');
    header("Expires: $expires");
  }

  /**
   * Sort stations by last observation or total years
   * (initial Db result is sorted by station name)
   *
   * @param $col {String <last_obs | total_years>}
   */
  public function sort ($col) {
    if ($col === 'last_obs') {
      usort($this->_stations, function ($a, $b) {
        return intval($b->last_obs) - intval($a->last_obs);
      });
      $this->_sortField = $col;
    }
    else if ($col === 'total_years') {
      usort($this->_stations, function ($a, $b) {
        return intval($b->total_years) - intval($a->total_years);
      });
      $this->_sortField = $col;
    }
  }
}
