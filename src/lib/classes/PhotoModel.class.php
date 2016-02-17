<?php

/**
 * Model for station photos
 *
 * PhotoModel Object (
 *   [date] => Int
 *   [file] => String
 *   [type] => String
 * )
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class PhotoModel {
  public $date;
  public $file;
  public $type;

  private $_types = [
    'bay' => 'Tripod Bayonet',
    'bm' => 'Benchmark',
    'e' => 'East',
    'misc' => 'Miscellaneous',
    'n' => 'North',
    's' => 'South',
    'w' => 'West'
  ];

  public function __construct ($file) {
    $this->file = $file;

    $this->_parseFilename();
  }

  /**
   * Get datestring and type of photo from filename
   *
   * filename convention is {station}_{datestring}{type}[{num}].ext
   */
  private function _parseFilename () {
    $photo = preg_match('/\w{4}_(\d{8})([A-Za-z]+)\d*\.\w+/',
      $this->file, $matches
    );
    if ($photo) {
      $this->date = (int) $matches[1];
      $this->type = $this->_types[$matches[2]];
    }
  }
}
