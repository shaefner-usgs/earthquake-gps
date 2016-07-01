<?php

include '../conf/config.inc.php'; // app config
include '../lib/classes/Db.class.php'; // db connector, queries

/**
 * GPS Waypoints (.gpx file) for stations in a network
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class Kml {
  private $_network;
  private $_rsStations;

  public function __construct($network) {
    $this->_network = $network;
    $this->_rsStations = $this->_getStations();
  }

  /**
   * Get waypoints XML
   *
   * @return $body {Xml}
   */
  private function _getBody () {
    $body = '';

    while ($row = $this->_rsStations->fetch(PDO::FETCH_ASSOC)) {

      //$body .= '<Folder>';
      //$body .= '<name>Last surveyed in 2001</name>';

      $placeMark = '  <Placemark>
        <name>' . strtoupper($row['station']) . '</name>
        <styleUrl></styleUrl>
        <visibility>1</visibility>
        <Point>
          <coordinates>' . $row['lon'] . ',' . $row['lat'] . ',0</coordinates>
        </Point>
        <description></description>>
        <Snippet>' . $row['lat'] . ',' . $row['lon'] . '</Snippet>>
      </Placemark>';
      $body .= "\n$placeMark";

      //$body .= '</Folder>';
    }

    return $body;
  }

  /**
   * Get footer XML
   *
   * @return {Xml}
   */
  private function _getFooter () {
    return "\n  </Document>\n</kml>";
  }

  /**
   * Get header XML
   *
   * @return $header {Xml}
   */
  private function _getHeader () {
    $timestamp = date('D M J, Y H:i:s e');
    $header = '<?xml version="1.0" encoding="UTF-8"?>
    <kml xmlns="http://earth.google.com/kml/2.1">
	    <Document>
        <name>USGS Campaign data sorted by last occupation</name>
        <description>File last updated ' . $timestamp . '</description>';

    return $header;
  }

  /**
   * Get DB recordset containing all stations in network
   *
   * @return {Object}
   */
  private function _getStations() {
    $db = new Db;

    // Db query result: all stations in a given network
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
  public function setPhpHeaders () {
    $expires = date(DATE_RFC2822);

    header('Cache-control: no-cache, must-revalidate');
    header('Content-Disposition: attachment; filename="' .
      $this->_network . '.kml"');
    header('Content-Type: application/xml');
    header("Expires: $expires");
  }
}
