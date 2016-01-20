<?php

/**
 * Station class
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class Station {
  public $param1;
  private $_param2;

  public function __construct($param1, $param2) {
    $this->param1 = $param1;
    $this->_param2 = $param2;
  }

  /**
   * Get station's photos
   *
   * @return {Array}
   */
  public function getPhotos() {

  }

  /**
   * Get station's log sheets
   *
   * @return {Array}
   */
  public function getLogSheets() {

  }
}
