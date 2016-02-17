<?php

/**
 * Model for station logsheets
 *
 * LogsheetModel Object (
 *   [date] => Int
 *   [file] => String
 *   [type] => String
 * )
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class LogsheetModel {
  public $date;
  public $file;
  public $type;

  private $_types = [
    1 => 'Front', // scanned image
    2 => 'Back', // scanned image
    'txt' => 'Site log' // text-based
  ];

  public function __construct ($file) {
    $this->file = $file;

    $this->_parseFilename();
  }

  /**
   * Get datestring and type of logsheet from filename
   *
   * scan filename convention is {station}{datestring}{session/identifiers}{side}.ext
   * text filename convention is {station}{datestring}.txt
   */
  private function _parseFilename () {
    $scan = preg_match('/\w{4}(\d{8})[^\d]+(\d)\.\w+/',
      $this->file, $s_matches
    );

    if ($scan) { // found scanned log
      $this->date = (int) $s_matches[1];
      $this->type = $this->_types[$s_matches[2]];
    }
    else {
      $text = preg_match('/\w{4}(\d{8})\.\w+/', $this->file, $t_matches);
    }

    if ($text) { // found text log
      $this->date = (int) $t_matches[1];
      $this->type = $this->_types['txt'];
    }
  }
}
