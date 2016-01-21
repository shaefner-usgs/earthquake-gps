<?php

/**
 * Station model
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class Station {
  protected $data = array();

  public function __construct($id) {
    $this->id = $id;
  }

  public function __set($name, $value) {
    $this->data[$name] = $value;
  }
}
