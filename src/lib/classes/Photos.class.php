<?php

include '../conf/config.inc.php';

/**
 * Photos model
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class Photos {
  public $photos;
  public $station;

  public function __construct ($station) {
    $this->photos = [];
    $this->station = $station;
    $this->_getPhotos();
  }

  /**
   * Get a directory contents (photos) for a given station
   *
   * @return $r {Array}
   */
  private function _getContents () {
    $r = [];
    $dir = sprintf('%s/stations/%s.dir/%s/photos/screen',
      $GLOBALS['DATA_DIR'],
      substr($this->station, 0, 1),
      $this->station
    );
    if (file_exists($dir)) {
      $contents = scandir($dir, SCANDIR_SORT_DESCENDING);
      $r = array_diff($contents, array('..', '.'));
    }

    return $r;
  }

  /**
   * Create photos object with photos grouped by date taken
   *
   */
  private function _getPhotos () {
    $files = $this->_getContents();
    foreach ($files as $file) {
      $details = $this->_parseFilename($file);
      $date = $details['date'];
      $name = $details['name'];
      $photo = [
        'name' => $name,
        'file' => $file
      ];
      if (!is_array($this->photos[$date])) {
        $this->photos[$date] = [];
      }
      array_push($this->photos[$date], $photo);
    }
  }

  /**
   * Get date string and type of photo from filename
   *
   * @return {Array}
   *    date and name of photo
   */
  private function _parseFilename ($file) {
    $types = [
      'bay' => 'Tripod Bayonet',
      'bm' => 'Benchmark',
      'e' => 'East',
      'misc' => 'Miscellaneous',
      'n' => 'North',
      's' => 'South',
      'w' => 'West'
    ];
    preg_match('/.*_(\d{8})([A-Za-z]+)\d?\.\w+/', $file, $matches);

    return [
      'date' => $matches[1],
      'name' => $types[$matches[2]]
    ];
  }
}
