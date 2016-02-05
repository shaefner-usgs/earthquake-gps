<?php

include '../conf/config.inc.php'; // app config

/**
 * Station model
 * - populates fields from db query using magic __set method
 * - also sets up other properites (e.g. links, map, and networkList)
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class StationModel {
  private $data = array();

  public function __construct ($networks = null) {
    $this->data['stationPath'] = $GLOBALS['MOUNT_PATH'] . '/' . $this->network
      . '/' . $this->station;
    $this->data['networkList']= $networks;
    $this->data['links'] = $this->_getLinkList();
    $this->data['map'] = $this->_getMapImg();
  }

  public function __get ($name) {
    return $this->data[$name];
  }

  public function __set ($name, $value) {
    if ($name === 'lat'
      || $name === 'lon'
      || $name === 'elevation'
      || $name === 'showcoords'
      || $name === 'x'
      || $name === 'y'
      || $name === 'z'
    ) {
      $value = floatval($value);
    }
    $this->data[$name] = $value;
  }

  private function _getLinkList () {
    $logs = $this->stationPath . '/logs/';
    $ngs = $this->_getNgsLink();
    $photos = $this->stationPath . '/photos/';
    $qc = $this->stationPath . '/qc/';
    $weather = $this->_getWeatherLink();

    $links = array(
      'Field Logs' => $logs,
      'Photos' => $photos,
      'Quality Control Data' => $qc,
      'Weather' => $weather,
      'NGS Datasheets' => $ngs
    );
    return $links;
  }

  private function _getMapImg () {
    return sprintf(
      'http://maps.google.com/?q=%f,%f+(Station+7ADL)&t=p&z=10',
      $this->lat,
      $this->lon
    );
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
