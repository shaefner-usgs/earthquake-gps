<?php

/**
 * Stations class
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class Stations {
  public $param1;
  private $_param2;

  public function __construct($param1, $param2) {
    $this->param1 = $param1;
    $this->_param2 = $param2;
  }

  /**
   * Get a list of stations beginning with a given letter / number
   *
   * @param $firstChar {String}
   * @return {Array}
   */
  public function getList($firstChar) {

  }

  /**
   * Get details for a given station code
   *
   * @param $stationCode {String}
   * @return {Array}
   */
  public function getDetails($stationCode) {

  }
}
