<?php

include_once '../conf/config.inc.php'; // app config

/**
 * Collection of station logsheets grouped by date
 *
 * LogsheetCollection Object (
 *   [logsheets] => Array (
 *     [(date)] => Array (
 *       LogsheetModel Object (
 *         [date] => Int
 *         [file] => String
 *         [type] => String
 *       )
 *     )
 *     ...
 *   )
 *   [network] => String
 *   [path] => String
 *   [station] => String
 *   [stationType] => String
 * )
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class LogsheetCollection {
  public $logsheets;
  public $network;
  public $path;
  public $station;
  public $stationType;

  public function __construct ($network, $station, $stationType=NULL) {
    $this->logsheets = [];
    $this->network = $network;
    $this->path = sprintf('%s/data/stations/%s.dir/%s/logsheets',
      $GLOBALS['MOUNT_PATH'],
      substr($station, 0, 1),
      $station
    );
    $this->station = $station;
    $this->stationType = $stationType;
  }

  /**
  * Add logsheet to logsheets array (grouped by date)
  *
  * @param $logsheet {Object}
  */
  public function add ($logsheet) {
    $date = $logsheet->date;
    if (!is_array($this->logsheets[$date])) {
      $this->logsheets[$date] = [];
    }
    array_push($this->logsheets[$date], $logsheet);
  }

  /**
  * Sort logsheets by date
  *
  * @param $order {String} default is 'ASC'
  *
  * @return {Array}
  */
  public function sort ($order='DESC') {
    if ($order === 'DESC') {
      return krsort($this->logsheets);
    }
    elseif ($order === 'ASC') {
      return ksort($this->logsheets);
    }
    throw new Exception('ERROR: Invalid sort order parameter');
  }
}
