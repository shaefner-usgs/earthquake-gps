<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/classes/Db.class.php'; // db connector, queries

/**
 * Generate GPS Waypoints (.gpx file) for stations in a network
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class Waypoints {
  private $_lats;
  private $_lons;
  private $_network;
  private $_rsStations;

  public function __construct($network) {
    $this->_network = $network;
    $this->_rsStations = $this->_getStations();
  }

  /**
   * Get XML body (waypoints)
   *
   * @return $body {Xml}
   */
  private function _getBody () {
    $body = '';
    $this->_lats = [];
    $this->_lons = [];

    while ($row = $this->_rsStations->fetch(PDO::FETCH_ASSOC)) {
      $ele = number_format($row['elevation'], 2, '.', '');
      $lat = number_format($row['lat'], 5, '.', '');
      $lon = number_format($row['lon'], 5, '.', '');
      $station = strtoupper($row['station']);
      $wpt = '  <wpt lat="' . $lat . '" lon="' . $lon . '">
        <ele>' . $ele . '</ele>
        <name>' . $station . '</name>
        <cmt>Station details from USGS GPS database</cmt>
        <desc>Campaign station ' . $station . '</desc>
        <sym>Triangle, Red</sym>
      </wpt>';
      $body .= "\n$wpt";

      // Store station lat, lon in array for calculating bounds of all stations
      array_push($this->_lats, $lat);
      array_push($this->_lons, $lon);
    }

    return $body;
  }

  /**
   * Get XML footer
   *
   * @return {Xml}
   */
  private function _getFooter () {
    return "\n</gpx>";
  }

  /**
   * Get XML header
   *
   * @return $header {Xml}
   */
  private function _getHeader () {
    $bounds = sprintf('<bounds minlat="%F" minlon="%F" maxlat="%F" maxlon="%F"/>',
      min($this->_lats),
      min($this->_lons),
      max($this->_lats),
      max($this->_lons)
    );
    $timestamp = date('Y-m-d\TH:i:s\Z');
    $header = '<?xml version="1.0" encoding="UTF-8"?>
    <gpx
      version="1.0"
      creator="USGS, Menlo Park - https://earthquake.usgs.gov/monitoring/gps"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xmlns="http://www.topografix.com/GPX/1/0"
      xsi:schemaLocation="http://www.topografix.com/GPX/1/0 http://www.topografix.com/GPX/1/0/gpx.xsd">';
    $header .= "\n  <time>$timestamp</time>";
    $header .= "\n  $bounds";

    return $header;
  }

  /**
   * Get DB recordset containing all stations in network
   *
   * @return {Object}
   */
  private function _getStations() {
    $db = new Db;

    // Db query: all stations in a given network
    return $db->queryStations($this->_network);
  }

  /**
   * Render XML content
   */
  public function render () {
    $body = $this->_getBody();
    print $this->_getHeader(); // header depends on arrays set by _getBody()
    print $body;
    print $this->_getFooter();
  }

  /**
   * Set PHP Headers for triggering file download w/ no caching
   */
  public function setHeaders () {
    $expires = date(DATE_RFC2822);

    header('Cache-control: no-cache, must-revalidate');
    header('Content-Disposition: attachment; filename="' .
      $this->_network . '.gpx"');
    header('Content-Type: application/xml');
    header("Expires: $expires");
  }
}
