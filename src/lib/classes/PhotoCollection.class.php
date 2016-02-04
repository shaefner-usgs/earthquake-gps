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

   public function __construct () {
     $this->photos = [];
     $this->path = $GLOBALS['MOUNT_PATH'] . '/data/stations/7.dir/7adl/photos';
   }

   /**
    * Add photo to photos array (grouped by date taken)
    *
    * @param $photo {Array}
    */
   public function add ($photo) {
     $date = $photo->date;
     if (!is_array($this->photos[$date])) {
       $this->photos[$date] = [];
     }
     array_push($this->photos[$date], $photo);
   }
 }
