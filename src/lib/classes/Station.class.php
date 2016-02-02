<?php

include '../conf/config.inc.php';

/**
 * Station model
 * - populates fields from db query using magic __set method
 * - also sets up other properites (e.g. links, map, and networkList)
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class Station {
  private $data = array();

  public function __construct ($networks = null) {
    //$this->name = $stationName;
    $this->data['links'] = $this->_getLinkList();
    $this->data['map'] = $this->_getMapImg();
    $this->data['networkList']= $networks;
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
    //$this->$name = $value;
    $this->data[$name] = $value;
  }

  private function _getLinkList () {
    $path = $GLOBALS['MOUNT_PATH'] . '/' . $this->network . '/' . $this->station;
    $logs = $path . '/logs/';
    //$photos = $path . '/photos/';
    $photos = $this->_getPhotosLink($path);
    $qc = $path . '/qc/';
    $weather = $this->_getWeatherLink();
    $ngs = $this->_getNgsLink();

    $links = array(
      'Field Logs' => $logs,
      'Photos' => $photos,
      'Quality Control Data' => $qc,
      'Weather' => $weather,
      'NGS Datasheets' => $ngs
    );
    return $links;
  }

  private function _getLogsLink () {

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
      'http://www.ngs.noaa.gov/cgi-bin/ds_radius.prl?selectedFormat=Decimal+Degrees&DLatBox=%f&DLonBox=%f&RadBox=0.5&SubmitBtn=Submit',
      $this->lat,
      $this->lon
    );
  }

  private function _getPhotosLink ($path) {
    $photos = [];
    $dir = sprintf('%s/stations/%s.dir/%s/photos/screen',
      $GLOBALS['DATA_DIR'],
      substr($this->station, 0, 1),
      $this->station
    );
    $dir_contents = scandir($dir, SCANDIR_SORT_DESCENDING);
    $photos['screen'] = array_diff($dir_contents, array('..', '.'));
    return $photos;
  }

  private function _getWeatherLink () {
    return sprintf(
      'http://forecast.weather.gov/MapClick.php?textField1=%.4f&textField2=%.4f',
      $this->lat,
      $this->lon
    );
  }
}
