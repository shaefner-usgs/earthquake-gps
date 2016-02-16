<?php

include '../conf/config.inc.php'; // app config

/**
 * PhotoCollection Object (
 *   [path] => String
 *   [station] => String
 *   [photos] => Array (
 *     [(date)] => Array (
 *       PhotoModel Object (
 *         [date] => Int
 *         [file] => String
 *         [name] => String
 *       )
 *     )
 *     ...
 *   )
 * )
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
 class PhotoCollection {
   public $path;
   public $photos;

   public function __construct ($station) {
    $dir = substr($station, 0, 1);

    $this->path = sprintf('%s/data/stations/%s.dir/%s/photos',
      $GLOBALS['MOUNT_PATH'],
      $dir,
      $station
    );
    $this->station = $station;
    $this->photos = [];
  }

  /**
  * Add photo to photos array (grouped by date taken)
  *
  * @param $photo {Object}
  */
  public function add ($photo) {
    $date = (int) $photo->date;
    if (!is_array($this->photos[$date])) {
      $this->photos[$date] = [];
    }
    array_push($this->photos[$date], $photo);
  }
}
