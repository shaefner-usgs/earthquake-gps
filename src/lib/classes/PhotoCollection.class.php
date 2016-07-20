<?php

include_once '../conf/config.inc.php'; // app config

/**
 * Collection of station photos grouped by date
 *
 * PhotoCollection Object (
 *   [count] => Int
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
  private $_order;

  public $count;
  public $network;
  public $path;
  public $photos;
  public $station;

  public function __construct ($station, $network) {
    $this->count = 0;
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
   * Comparison function
   *
   * @return {Int} 1 or -1
   */
  private function _compare ($a, $b) {
    $aPosition = array_search($a->type, $this->_order);
    $bPosition = array_search($b->type, $this->_order);

    // Both photos are types in $_order array
    if ($aPosition !== false && $bPosition !== false) {
      return ($aPosition < $bPosition) ? -1 : 1;
    }

    // Only one photo is type in $_order array (put one in $_order first)
    if ($aPosition !== false) {
      return -1;
    }
    if ($bPosition !== false) {
      return 1;
    }

    // Neither photo is type in $_order array; alphabetize
    return ($a < $b) ? -1 : 1;
  }

  /**
   * Keep a count of photos added
   */
  private function _incrementCount () {
    $this->count ++;
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

    $this->_incrementCount();
  }

  /**
   * Sort photos in order of $order array
   */
  public function sort () {
    $this->_order = [
      'North', 'East', 'South', 'West', 'Bayonet', 'Benchmark', 'Miscellaneous'
    ];

    foreach($this->photos as $date => $photos) {
      usort($photos, array($this, '_compare'));
      $this->photos[$date] = $photos;
    }
  }
}
