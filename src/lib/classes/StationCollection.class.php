<?php

/**
 * GPS Station Collection
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class StationCollection {
  public $count,
    $network,
    $selectedStation,
    $stations;

  public function __construct ($network=NULL, $station=NULL) {
    $this->count = 0;
    $this->network = $network;
    $this->selectedStation = $station;
    $this->stations = [];
  }

  /**
   * Add a station to the collection
   *
   * @param $station {Object}
   */
  public function add ($station) {
    $this->count ++;
    $this->stations[] = $station;
  }

  /**
   * Get a station from the collection
   *
   * @param $stationParam {String}
   *     4-character station name
   *
   * @return $station {Object}
   */
  public function getStation ($stationParam) {
    foreach ($this->stations as $station) {
      if ($station->station === $stationParam) {
        return $station;
      }
    }
  }
}
