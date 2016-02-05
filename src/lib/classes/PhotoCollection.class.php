<?php

include '../conf/config.inc.php'; // app config

/**
 * Photo collection
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
    $this->photos = [];
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
