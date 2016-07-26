<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/classes/Db.class.php'; // db connector, queries

include_once '../lib/classes/Logsheet.class.php'; // logsheet model
include_once '../lib/classes/LogsheetCollection.class.php'; // logsheet collection

/**
 * Generate KML files for GPS stations
 *
 * @param $network {String} default is NULL
 *     KML file includes all stations with the option to limit to a given network
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class Kml {
  private $_domain;
  private $_lats;
  private $_lons;
  private $_meta;
  private $_network;
  private $_sortBy;
  private $_stations;

  public function __construct($network=NULL) {
    $filename = 'GPS';
    $namePrefix = 'All Stations';
    if ($network) {
      $filename = $network;
      $namePrefix = "$network Network";
    }

    $this->_domain = $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'];
    $this->_meta = [
      'filename' => $filename,
      'last' => [
        'description' => 'Campaign stations',
        'folder' => 'Last surveyed in %s',
        'name' => $namePrefix . ' (sorted by last year occupied)'
      ],
      'timespan' => [
        'description' => 'Campaign stations',
        'folder' => '%s year(s) between first/last surveys',
        'name' => $namePrefix . ' (sorted by time span between surveys)'
      ],
      'station' => [
        'description' => 'Campaign and continuous stations',
        'name' => $namePrefix . ' (sorted by station name)'
      ]
    ];
    $this->_network = $network;
    $this->_sortBy = 'station'; // sorted by station name by default
    $this->_stations = $this->_getStations();
  }

  /**
   * Get KML Body
   *
   * @return $body {String}
   */
  private function _getBody () {
    $body = '';
    $containsFolders = true;
    $prevValue = NULL;
    $sortBy = $this->_sortBy;
    $this->_lats = [];
    $this->_lons = [];

    // Don't create folders when sorting by station name
    if ($sortBy === 'station') {
      $containsFolders = false;
    }

    // Only include campaign stations unless creating kml sorted by station
    if ($sortBy !== 'station') {
      $this->_stations = array_filter($this->_stations, function($value) {
        return $value['stationtype'] === 'campaign';
      });
    }

    foreach($this->_stations as $station) {

      // Create folders for grouping stations
      if ($containsFolders) {
        $value = $station[$sortBy];
        if ($value !== $prevValue) {
          // Close previous folder
          if (isset($prevValue)) {
            $body .= "\n    </Folder>";
          }
          // Open new folder
          $sub = $value;
          if ($value === '' || $value === -1) {
            $sub = '[unknown]';
          }
          $folder = sprintf($this->_meta[$sortBy]['folder'], $sub);
          $body .= "\n    <Folder><name>$folder</name><open>0</open>";

          $prevValue = $value;
        }
      }

      // Store station lat, lon in array for calculating bounds of all stations
      array_push($this->_lats, $station['lat']);
      array_push($this->_lons, $station['lon']);

      $placeMark = $this->_getPlaceMark($station);
      $body .= "\n$placeMark";
    }

    // Close final folder (not using folders when sorting by station)
    if ($sortBy !== 'station') {
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
   * @return $header {String}
   */
  private function _getHeader () {
    $description = $this->_meta[$this->_sortBy]['description'];
    $name = $this->_meta[$this->_sortBy]['name'];
    $latCenter = (max($this->_lats) + min($this->_lats)) / 2;
    $legendUrl = sprintf ('http://%s%s/img/kmlLegend-%s.png',
      $this->_domain,
      $GLOBALS['MOUNT_PATH'],
      $this->_sortBy
    );
    $lonCenter = (max($this->_lons) + min($this->_lons)) / 2;
    $timestamp = date('D M j, Y H:i:s e');

    $header = '<?xml version="1.0" encoding="UTF-8"?>
    <kml xmlns="http://earth.google.com/kml/2.1">
      <Document>
        <name>' . $name . '</name>
        <Snippet maxLines="1">' . $timestamp . '</Snippet>
        <description>' . $description . '</description>
        <LookAt>
          <heading>0</heading>
          <latitude>' . $latCenter . '</latitude>
          <longitude>' . $lonCenter . '</longitude>
          <range>1000000</range>
          <tilt>0</tilt>
        </LookAt>
        <ScreenOverlay>
          <name>Legend</name>
          <open>0</open>
          <overlayXY x="0" y="1" xunits="fraction" yunits="fraction"/>
          <screenXY x=".005" y=".995" xunits="fraction" yunits="fraction"/>
          <size x="150" y="0" xunits="pixels" yunits="pixels"/>
          <visibility>1</visibility>
          <Icon>
            <href>' . $legendUrl . '</href>
          </Icon>
        </ScreenOverlay>
        <Style id="marker">
          <BalloonStyle><text><![CDATA[
            <style>
              ul { margin: 0; padding-left: 0em; line-height: 1.4; }
              .coords { margin-top: -1em; }
              .data { border-top: 1px solid #eee; padding-top: 1em; }
            </style>
            $[description]
          ]]></text></BalloonStyle>
        </Style>';

    return $header;
  }

  /**
   * Get appropriate icon based on station properties for current sort type
   *
   * @param $station {Object}
   *
   * @return $icon {String}
   *     Absolute URL of icon
   */
  private function _getIcon ($station) {
    $shapes = [
      'campaign' => 'triangle',
      'continuous' => 'square'
    ];

    // Get color
    if ($this->_sortBy === 'station') {
      $color = 'grey';
    }
    else if ($this->_sortBy === 'last') {
      if ($station['last']) {
        $years = ceil(date('Y') - $station['last']);
      } else { // set $years to '-1' if $station['last'] is empty
        $years = -1;
      }

      if ($years > 15) {
        $color = 'red';
      } else if ($years > 12) {
        $color = 'orange';
      } else if ($years > 9) {
        $color = 'yellow';
      } else if ($years > 6) {
        $color = 'green';
      } else if ($years > 3) {
        $color = 'blue';
      } else if ($years >= 0) {
        $color = 'purple';
      } else { // $years is flagged '-1' when unknown
        $color = 'grey';
      }
        
    } else if ($this->_sortBy === 'timespan') {
      $years = $station['timespan'];

      if ($years > 15) {
        $color = 'purple';
      } else if ($years > 12) {
        $color = 'blue';
      } else if ($years > 9) {
        $color = 'green';
      } else if ($years > 6) {
        $color = 'yellow';
      } else if ($years > 3) {
        $color = 'orange';
      } else if ($years >= 0) {
        $color = 'red';
      } else { // $years is flagged '-1' when unknown
        $color = 'grey';
      }
    }

    $icon = sprintf ('http://%s%s/img/pin-s-%s+%s.png',
      $this->_domain,
      $GLOBALS['MOUNT_PATH'],
      $shapes[$station['stationtype']],
      $color
    );

    return $icon;
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
    $data_collected = false;
    $display_lat = number_format($station['lat'], 5, '.', '');
    $display_lon = number_format($station['lon'], 5, '.', '');
    $display_station = strtoupper($station['station']);
    $icon = $this->_getIcon($station);
    $logsheets_html = '';

    // Get logsheets for each station
    // (but only if filtered by network--takes too long for all stations)
    if ($this->_network) {
      $logsheetsCollection = $this->_getLogSheets($station['station']);

      // Create HTML for logsheets
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
        $logsheets_html = '<p class="data">GPS data was collected on the
          following date(s):</p>' . $logsheets_html;
      }
    }

    // Create HTML for description
    $description_html = sprintf('<h1>%s</h1>
      <p class="coords">%s, %s</p>
      <p><a href="http://%s%s/%s/%s">Station Details</a></p>
      %s',
      $display_station,
      $display_lat,
      $display_lon,
      $this->_domain,
      $GLOBALS['MOUNT_PATH'],
      $station['network'],
      $station['station'],
      $logsheets_html
    );

    $placeMark = '    <Placemark>
      <name>' . $display_station . '</name>
      <description>' . $description_html . '</description>
      <styleUrl>#marker</styleUrl>
      <visibility>1</visibility>
      <LookAt>
        <longitude>' . $station['lon'] . '</longitude>
        <latitude>' . $station['lat'] . '</latitude>
        <range>10000</range>
      </LookAt>
      <Point>
        <coordinates>' . $station['lon'] . ',' . $station['lat'] . ',0</coordinates>
      </Point>
      <Snippet>' . $display_lat . ', ' . $display_lon . '</Snippet>
      <Style>
        <IconStyle><Icon><href>'. $icon . '</href></Icon></IconStyle>
      </Style>
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
    $stations = [];

    // Db query result: all stations, optionally limited to given network
    $rsStations = $db->queryStations($this->_network);

    // Add fields needed for sorting stations
    while ($row = $rsStations->fetch(PDO::FETCH_ASSOC)) {
      $obs_years = preg_split('/[\s,]+/', $row['obs_years']);

      $row['first'] = min($obs_years);
      $row['last'] = max($obs_years);

      // set $timespan default to -1 to flag stations with no observations
      // (useful for sorting)
      $timespan = -1;
      if ($row['obs_years']) {
        $timespan = $row['last'] - $row['first'];
      }
      $row['timespan'] = $timespan;

      array_push($stations, $row);
    }

    return $stations;
  }

  /**
   * Render KML content
   */
  public function render () {
    $body = $this->_getBody();
    print $this->_getHeader(); // header depends on arrays set by _getBody()
    print $body;
    print $this->_getFooter();
  }

  /**
   * Set PHP Headers for triggering file download w/ no caching
   */
  public function setPhpHeaders () {
    $expires = date(DATE_RFC2822);
    $filename = $this->_meta['filename'] . '-' . $this->_sortBy . '.kml';

    header('Cache-control: no-cache, must-revalidate');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Type: application/xml');
    header("Expires: $expires");
  }

  /**
   * Sort stations by last year or timespan of years surveyed
   * (initial Db result is sorted by station name)
   *
   * @param $sortBy {String <last | timespan>}
   */
  public function sort ($sortBy) {
    if ($sortBy === 'last') {
      usort($this->_stations, function ($a, $b) {
        return intval($b['last']) - intval($a['last']);
      });
      $this->_sortBy = $sortBy;
    }
    else if ($sortBy === 'timespan') {
      usort($this->_stations, function ($a, $b) {
        return intval($b['timespan']) - intval($a['timespan']);
      });
      $this->_sortBy = $sortBy;
    }
  }
}
