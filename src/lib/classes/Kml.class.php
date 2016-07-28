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
    $filename = 'AllNetworks';
    $namePrefix = 'All Networks';
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
      'station' => [ // default
        'description' => 'Campaign and continuous stations',
        'name' => $namePrefix . ' (sorted by station name)'
      ],
      'timespan' => [
        'description' => 'Campaign stations',
        'folder' => '%s year(s) between first/last surveys',
        'name' => $namePrefix . ' (sorted by time span between surveys)'
      ],
      'years' => [

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
    $stations = $this->_stations['list'];
    $this->_lats = [];
    $this->_lons = [];

    // Don't create folders when sorting by station name
    if ($sortBy === 'station') {
      $containsFolders = false;
    }

    // Only include campaign stations unless creating kml sorted by station
    if ($sortBy !== 'station') {
      $stations = array_filter($stations, function($value) {
        return $value['stationtype'] === 'campaign';
      });
    }

    foreach($stations as $station) {
      if (!$station['lat'] || !$station['lon']) {
        break; // skip stations w/o lat / lon values
      }
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
   * @param $station {Array}
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
   * @param $station {Array}
   *
   * @return $placeMark {String}
   */
  private function _getPlaceMark ($station) {
    $baseUri = sprintf('http://%s%s/%s/%s',
      $this->_domain,
      $GLOBALS['MOUNT_PATH'],
      $station['network'],
      $station['station']
    );
    $data_collected = false;
    $display_lat = number_format($station['lat'], 5, '.', '');
    $display_lon = number_format($station['lon'], 5, '.', '');
    $display_station = strtoupper($station['station']);
    $icon = $this->_getIcon($station);

    // Create HTML for links
    $links_html = 'Station ';
    $links = [
      'Details' => $baseUri,
      'Logs' => $baseUri . '/logs',
      'Photos' => $baseUri . '/photos'
    ];
    if ($station['stationtype'] === 'continuous') { // no photos
      unset($links['Photos']);
    }
    foreach ($links as $name => $uri) {
      $links_html .= sprintf ('<a href="%s">%s</a>, ',
        $uri,
        $name
      );
    }
    $links_html = preg_replace('/(,\s+)$/', '', $links_html); // strip final comma

    // Create HTML for logsheets
    // (only if filtered by network--it takes too long for all stations)
    $logsheets_html = '';
    if ($this->_network) {
      $logsheetsCollection = $this->_getLogSheets($station['station']);

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
      <p>%s</p>
      %s',
      $display_station,
      $display_lat,
      $display_lon,
      $links_html,
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
   * Create an array containing a list of stations and
   * stations grouped by years observed from DB recordset
   *
   * @return {Array}
   *   [
   *     'list' => {Array}
   *     'years' => {Array}
   *   ]
   */
  private function _getStations() {
    $db = new Db;
    $stations = [
      'list' => [],
      'years' => []
    ];

    // Db query result: all stations in non-hidden networks,
    // (with the option to limit to given network)
    $rsStations = $db->queryStations($this->_network);

    while ($row = $rsStations->fetch(PDO::FETCH_ASSOC)) {
      $obs_years = preg_split('/[\s,]+/', $row['obs_years'], NULL,
        PREG_SPLIT_NO_EMPTY
      );

      // Add fields needed for sorting stations
      if (empty($obs_years)) {
        $first = '';
        $last = '';
        $timespan = -1; // set to -1 for sorting purposes
      } else {
        $first = min($obs_years);
        $last = max($obs_years);
        $timespan = $last - $first;
      }
      $row['first'] = $first;
      $row['last'] = $last;
      $row['timespan'] = $timespan;

      // Array of stations, containing added fields
      array_push($stations['list'], $row);

      // Array of stations, grouped by years surveyed
      foreach($obs_years as $year) {
        if (!array_key_exists($year, $stations['years'])) {
          $stations['years'][$year] = [];
        }
        array_push($stations['years'][$year], $row);
      }
    }

    ksort($stations['years']);

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
  * Render stations array (useful for debugging)
  */
  public function renderStationsArray () {
    print '<pre>';
    print_r($this->_stations);
    print '</pre>';
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
   * Sort stations by last year, timespan, or years surveyed
   * (initial Db result is sorted by station name)
   *
   * @param $sortBy {String <last | timespan | years>}
   */
  public function sort ($sortBy) {
    if ($sortBy === 'last') {
      usort($this->_stations['list'], function ($a, $b) {
        return intval($b['last']) - intval($a['last']);
      });
      $this->_sortBy = $sortBy;
    }
    else if ($sortBy === 'timespan') {
      usort($this->_stations['list'], function ($a, $b) {
        return intval($b['timespan']) - intval($a['timespan']);
      });
      $this->_sortBy = $sortBy;
    }
    else if ($sortBy === 'years') {
      $this->_sortBy = $sortBy;
    }
  }
}
