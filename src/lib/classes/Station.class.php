<?php

include_once '../conf/config.inc.php';

/**
 * Station model
 * - populates fields from db query using magic __set method
 * - also sets up other 'non-db field' properites (e.g. links and networkList)
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class Station {
  protected $data = array();

  public function __construct ($networks = NULL) {
    //$this->name = $stationName;
    $this->data['links'] = $this->getLinkList();
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

  protected function getGmapLink () {
    return sprintf('http://maps.google.com/?q=%f,%f+(Station+7ADL)&t=p&z=10',
      $this->lat,
      $this->lon
    );
  }

  protected function getLinkList () {
    $path = $GLOBALS['MOUNT_PATH'] . '/' . $this->network . '/' . $this->station;
    $logs = $path . '/logs/';
    $photos = $path . '/photos/';
    $qc = $path . '/qc/';
    $weather = $this->getWeatherLink();
    $ngs = $this->getNgsLink();
    $gmap = $this->getGmapLink();

    $links = array(
      'Field Logs' => $logs,
      'Photos' => $photos,
      'Quality Control Data' => $qc,
      'Weather' => $weather,
      'NGS Datasheets' => $ngs,
      'Map' => $gmap
    );
    return $links;
  }

  protected function getNgsLink () {
    return sprintf('http://www.ngs.noaa.gov/cgi-bin/ds_radius.prl?selectedFormat=Decimal+Degrees&DLatBox=%f&DLonBox=%f&RadBox=0.5&SubmitBtn=Submit',
      $this->lat,
      $this->lon
    );
  }

  protected function getWeatherLink () {
    return sprintf('http://forecast.weather.gov/MapClick.php?textField1=%.4f&textField2=%.4f',
      $this->lat,
      $this->lon
    );
  }
}
