<?php

/**
 * Photo model
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class PhotoModel {
  public $date;
  public $file;
  public $name;

  public function __construct ($file) {
    $this->file = $file;

    $this->_parseFilename();
  }

  /**
   * Get date string and type of photo from filename
   *
   */
  private function _parseFilename () {
    $types = [
      'bay' => 'Tripod Bayonet',
      'bm' => 'Benchmark',
      'e' => 'East',
      'misc' => 'Miscellaneous',
      'n' => 'North',
      's' => 'South',
      'w' => 'West'
    ];
    preg_match('/.*_(\d{8})([A-Za-z]+)\d?\.\w+/', $this->file, $matches);

    $this->date = $matches[1];
    $this->name = $types[$matches[2]];
  }
}
