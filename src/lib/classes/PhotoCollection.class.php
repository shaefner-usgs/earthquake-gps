<?php

include '../conf/config.inc.php'; // app config

/**
 * Collection of station photos grouped by date
 *
 * PhotoCollection Object (
 *   [network] => String
 *   [path] => String
 *   [photos] => Array (
 *     [(date)] => Array (
 *       PhotoModel Object (
 *         [code] => String
 *         [date] => Int
 *         [file] => String
 *         [type] => String
 *       )
 *     )
 *     ...
 *   )
 *   [station] => String
 * )
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class PhotoCollection {
  public $network;
  public $path;
  public $photos;
  public $station;

  public function __construct ($station, $network) {
    $this->network = $network;
    $this->path = sprintf('%s/data/stations/%s.dir/%s/photos',
      $GLOBALS['MOUNT_PATH'],
      substr($station, 0, 1),
      $station
    );
    $this->photos = [];
    $this->station = $station;
  }

  /**
  * Add photo to photos array (grouped by date taken)
  *
  * @param $photo {Object}
  */
  public function add ($photo) {
    $date = $photo->date;
    if (!is_array($this->photos[$date])) {
      $this->photos[$date] = [];
    }
    array_push($this->photos[$date], $photo);
  }
}
