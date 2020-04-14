<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/classes/Db.class.php'; // db connector, queries

/**
 * GPS Station Model
 * - populates fields from Station db query using magic __set method
 * - also sets up other properites (e.g. linkList, networkList, velocities, etc)
 *
 * Station Object (
 *   ['lastUpdate'] => String
 *   ['linkList'] => Object
 *   ['networkList'] => Array
 *   ['noise'] => Array
 *   ['offsets'] => Array
 *   ['path'] => String
 *   ['postSeismic'] => Array
 *   ['seasonal'] => Array
 *   ['velocities'] => Array
 *   ... (+ all properties returned from station db query)
 * )
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class Station {
  private $_data = []; // store props retrieved/set by PHP's magic get/set methods

  public function __construct (
    $velocities=NULL,
    $networkList=NULL,
    $noise=NULL,
    $offsets=NULL,
    $postSeismic=NULL,
    $seasonal=NULL
  ) {

    if ($velocities) {
      $this->lastUpdate = $this->_getLastUpdate($velocities);
    }

    $this->path = sprintf('%s/%s/%s',
      $GLOBALS['MOUNT_PATH'],
      $this->network,
      $this->station
    );
    $this->linkList = $this->_getLinkList();
    $this->networkList = $networkList;
    $this->noise = $noise;
    $this->offsets = $offsets;
    $this->postSeismic = $postSeismic;
    $this->seasonal = $seasonal;
  }

  public function __get ($name) {
    return $this->_data[$name];
  }

  public function __set ($name, $value) {
    if ($name === 'elevation'
      || $name === 'lat'
      || $name === 'lon'
      || $name === 'showcoords'
      || $name === 'x'
      || $name === 'y'
      || $name === 'z'
    ) {
      $value = floatval($value);
    }

    $this->_data[$name] = $value;
  }

  /**
   * Get 5 closest stations
   *
   * @param $collection {Object}
   *
   * @return $closestStations {Array}
   */
  public function getClosestStations ($collection) {
    $db = new Db;

    $rsClosestStations = $db->queryClosestStations( // query closest stations
      $this->lat,
      $this->lon,
      $this->station
    );
    $stations = array_slice(
      $rsClosestStations->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_OBJ), // group stations in multiple networks
      0, 5, true // keep closest 5
    );

    // Get a Station Model for each of the closest stations and set desired props
    $closestStations = [];
    foreach ($stations as $station => $networks) {
      $distance = $networks[0]->distance; // use first value (should all be the same)
      $network = '';

      foreach ($networks as $props) { // loop through networks station is in
        if ($props->network === $this->network) { // station is in selected network
          $model = $collection->getStation($station);
          $network = $this->network;
        }
      }
      if (!$network) { // station is *not* in selected network
        $network = $networks[0]->network; // use first network in list

        // Create Station Model
        $rsVelocities = $db->queryVelocities($network, $station);
        $rsStation = $db->queryStation($station, $network);
        $rsStation->setFetchMode(
          PDO::FETCH_CLASS,
          'Station', [
            $rsVelocities->fetchAll(PDO::FETCH_OBJ)
          ]
        );
        $model = $rsStation->fetch();
      }

      // Set props for closest stations list
      $closestStations[] = (object) [
        'distance' => $distance,
        'lastUpdate' => $model->lastUpdate,
        'network' => $network,
        'station' => $station,
        'stationtype' => $model->stationtype
      ];
    }

    return $closestStations;
  }

  /**
   * Get station icon key (shape/color)
   *
   * @param $type {String <campaign | continuous>}
   * @param $date {String}
   *
   * @return {String}
   */
  public function getIconKey ($type, $date) {
    $shapes = [
      'campaign' => 'triangle',
      'continuous' => 'square'
    ];
    $color = getColor($date);
    $shape = $shapes[$type];

    return "$shape+$color";
  }

  /**
   * Get number of logsheets (counting pairs of front/back pages as one)
   *
   * @param $logsheets {Array}
   *
   * @return {Integer}
   */
  public function getLogSheetCount ($logsheets) {
    $baseNames = [];

    foreach ($logsheets as $logsheet) {
      preg_match('/^\w{4}(\d{8})[^\d]+/', $logsheet, $matches);
      if (isSet($matches[1])) {
        $baseNames[] = $matches[1]; // store filename w/o front/back identifiers
      }
    }

    return count(array_unique($baseNames));
  }

  /**
   * Get filenames of log sheets
   *
   * @return $logsheets {Array}
   */
  public function getLogSheets () {
    global $DATA_DIR;

    $dir = sprintf('%s/stations/%s.dir/%s/logsheets',
      $DATA_DIR,
      substr($this->station, 0, 1),
      $this->station
    );
    $logsheets = getDirContents($dir);

    return $logsheets;
  }

  /**
   * Get filenames of photos
   *
   * @return $photos {Array}
   */
  public function getPhotos () {
    global $DATA_DIR;

    $dir = sprintf('%s/stations/%s.dir/%s/photos/screen',
      $DATA_DIR,
      substr($this->station, 0, 1),
      $this->station
    );
    $photos = getDirContents($dir);

    return $photos;
  }

  /**
   * Get last update time
   *
   * @param $velocities {Array}
   *
   * @return {String}
   */
  private function _getLastUpdate ($velocities) {
    if ($velocities[$this->station]) { // velocities array keyed by station
      return $velocities[$this->station]->last_observation;
    } else { // indexed velocities array; return first elem
      $this->velocities = $velocities; // add velocities to Model
      return $velocities[0]->last_observation;
    }
  }

  /**
   * Get link list parameters (material icon name and uri for links)
   *
   * @return {Object}
   */
  private function _getLinkList () {
    // Multi-dimensional array containing icon/href
    $links = [
      'Photos' => [
        'icon' => 'collections',
        'href' => $this->path . '/photos'
      ],
      'Field Logs' => [
        'icon' => 'assignment',
        'href' => $this->path . '/logs'
      ],
      'Quality Control Data' => [
        'icon' => 'scatter_plot',
        'href' => $this->path . '/qc'
      ],
      'Kinematic Data' => [
        'icon' => 'show_chart',
        'href' => $this->path . '/kinematic'
      ],
      'Weather' => [
        'icon' => 'wb_sunny',
        'href' => $this->_getWeatherLink()
      ],
      '<abbr title="National Geodetic Survey">NGS</abbr>&nbsp;Datasheets' => [
        'icon' => 'description',
        'href' => $this->_getNgsLink()
      ]
    ];

    // Campaign stations don't have kinematic data; continous don't have photos
    if ($this->stationtype === 'campaign') {
      unset($links['Kinematic Data']);
    } else if ($this->stationtype === 'continuous') {
      unset($links['Photos']);
    }

    return json_decode(json_encode($links)); // cast array to object
  }

  private function _getNgsLink () {
    return sprintf(
      'https://www.ngs.noaa.gov/cgi-bin/ds_radius.prl?selectedFormat=Decimal+Degrees&DLatBox=%f&DLonBox=%f&RadBox=0.5&StabilSelected=0&TypeSelected=X-0&SubmitBtn=Submit',
      $this->lat,
      $this->lon * -1 // ngs server 'balks' at negative values for W longitude
    );
  }

  private function _getWeatherLink () {
    return sprintf(
      'https://forecast.weather.gov/MapClick.php?textField1=%.4f&textField2=%.4f',
      $this->lat,
      $this->lon
    );
  }
}
