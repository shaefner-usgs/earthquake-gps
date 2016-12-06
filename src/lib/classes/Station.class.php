<?php

include_once '../conf/config.inc.php'; // app config

/**
 * Model for GPS station
 * - populates fields from db query using magic __set method
 * - also sets up other properites (e.g. links, networkList, and velocities)
 *
 * Station Object (
 *   [links] => Array
 *   [networkList] => Array
 *   [stationPath] => String
 *   [velocities] => Array
 *   ... (+ all properties returned from db query)
 * )
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class Station {
  private $_data = array();

  public function __construct ($networkList=NULL, $rsVelocities=NULL) {

    $this->_data['stationPath'] = $GLOBALS['MOUNT_PATH'] . '/' . $this->network
      . '/' . $this->station;
    $this->_data['networkList'] = $networkList;
    $this->_data['velocities'] = $this->_createVelocitiesArray($rsVelocities);
    $this->_data['links'] = $this->_getLinkList();
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
   * Create an array of velocities grouped by station, type, and component
   *
   * the velocites table contains data in different reference frames ('type')
   * and only certain fields are applicable to each ref. frame
   *
   * @param $rsVelocities {Object} - PDOStatement object
   *
   * @return $velocites {Array}
   */
  private function _createVelocitiesArray ($rsVelocities) {
    while ($row = $rsVelocities->fetch(PDO::FETCH_ASSOC)) {
      // stations are stored in lowercase in db except in this table
      $station = strtolower($row['station']);
      $type = trim($row['type']);

      // Shared props
      $north = [
        'velocity' => $row['north_velocity'],
        'sigma' => $row['north_sigma']
      ];
      $east = [
        'velocity' => $row['east_velocity'],
        'sigma' => $row['east_sigma']
      ];
      $up = [
        'velocity' => $row['up_velocity'],
        'sigma' => $row['up_sigma']
      ];

      // Props based on type (cleaned, itrf2008, nafixed)
      if ($type === 'cleaned') {
        $north['whitenoise'] = $row['whitenoisenorth'];
        $north['randomwalk'] = $row['randomwalknorth'];
        $north['flickernoise'] = $row['flickernoisenorth'];
        $east['whitenoise'] = $row['whitenoiseeast'];
        $east['randomwalk'] = $row['randomwalkeast'];
        $east['flickernoise'] = $row['flickernoiseeast'];
        $up['whitenoise'] = $row['whitenoiseup'];
        $up['randomwalk'] = $row['randomwalkup'];
        $up['flickernoise'] = $row['flickernoiseup'];
      } else {
        $north['rms'] = $row['north_rms'];
        $east['rms'] = $row['east_rms'];
        $up['rms'] = $row['up_rms'];
      }

      $velocities['data'][$station][$type]['north'] = $north;
      $velocities['data'][$station][$type]['east'] = $east;
      $velocities['data'][$station][$type]['up'] = $up;

      // Lookup table for column names
      $velocities['lookup'] = [
        'flickernoise' => 'Flicker Noise',
        'randomwalk' => 'Random Walk',
        'rms' => 'RMS (mm)',
        'sigma' => 'Uncertainty (mm/yr)	',
        'velocity' => 'Velocity (mm/yr)	',
        'whitenoise' => 'White Noise'
      ];
    }

    return $velocities;
  }

  private function _getLinkList () {
    $kinematic = $this->stationPath . '/kinematic';
    $logs = $this->stationPath . '/logs';
    $ngs = $this->_getNgsLink();
    $photos = $this->stationPath . '/photos';
    $qc = $this->stationPath . '/qc';
    $weather = $this->_getWeatherLink();

    $links = array(
      'Photos' => $photos,
      'Field Logs' => $logs,
      'Quality Control Data' => $qc,
      '5-minute Kinematic Results' => $kinematic,
      'Weather' => $weather,
      '<abbr title="National Geodetic Survey">NGS</abbr> Datasheets' => $ngs
    );

    // campaign stations don't have kinematic data; continous don't have photos
    if ($this->stationtype === 'campaign') {
      unset($links['5-minute Kinematic Results']);
    } else if ($this->stationtype === 'continuous') {
      unset($links['Photos']);
    }

    return $links;
  }

  private function _getNgsLink () {
    return sprintf(
      'http://www.ngs.noaa.gov/cgi-bin/ds_radius.prl?selectedFormat=Decimal+Degrees&DLatBox=%f&DLonBox=%f&RadBox=0.5&StabilSelected=0&TypeSelected=X-0&SubmitBtn=Submit',
      $this->lat,
      $this->lon * -1 // ngs server 'balks' at negative values for W longitude
    );
  }

  private function _getWeatherLink () {
    return sprintf(
      'http://forecast.weather.gov/MapClick.php?textField1=%.4f&textField2=%.4f',
      $this->lat,
      $this->lon
    );
  }
}
