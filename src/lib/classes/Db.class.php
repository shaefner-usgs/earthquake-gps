<?php

include '../conf/config.inc.php'; // app config

/**
 * Database connector and queries for GPS app
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class Db {
  private $db;

  public function __construct() {
    try {
      $this->db = new PDO($GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASS']);
      $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
      print 'Error: ' . $e->getMessage();
    }
  }

  /**
   * Perform db query
   *
   * @param $sql {String}
   *        SQL query
   * @param $params {Array} default is null
   *        array of key-value substitution params for SQL query
   * @return {Object} PDOStatement object
   */
  private function _execQuery ($sql, $params=null) {
    try {
      $stmt = $this->db->prepare($sql);

      // bind sql params
      foreach ($params as $key => $value) {
        $type = $this->_getType($value);
        $stmt->bindValue($key, $value, $type);
      }

      $stmt->execute();
      return $stmt;
    } catch(Exception $e) {
      print 'ERROR: ' . $e->getMessage();
    }
  }

/**
 *
 *
 */
  private function _getType ($var) {
    $varType = gettype($var);
    $pdoTypes = array(
      'boolean' => PDO::PARAM_BOOL,
      'integer' => PDO::PARAM_INT,
      'NULL' => PDO::PARAM_NULL,
      'string' => PDO::PARAM_STR
    );

    $type = $pdoTypes['string']; // default
    if (isset($pdoTypes[$varType])) {
      $type = $pdoTypes[$varType];
    }

    return $type;
  }

  /**
   * Query db to get a list of "non-hidden" networks a given station belongs to
   *
   */
  public function queryNetworkList ($station) {
    $sql = 'SELECT r.network
      FROM nca_gps_relations r
      LEFT JOIN nca_gps_networks n ON r.network = n.name
      WHERE r.station = :station AND n.show = "1"
      ORDER BY `network` ASC';

    return $this->_execQuery($sql, array(
      'station' => $station
    ));
  }

  // Query db to get all "non-hidden" networks
  // @return PDOStatement object
  public function queryNetworks () {
    $sql = 'SELECT *
      FROM nca_gps_networks
      WHERE `show` = "1"
      ORDER BY `name` ASC';

    return $this->_execQuery($sql);
  }

  // Query db to get station details for a given station and network
  // @return PDOStatement object
  public function queryStation ($station, $network) {
    $sql = 'SELECT s.lat, s.lon, s.elevation, s.x, s.y, s.z, s.station,
      s.showcoords, r.stationtype, r.network
      FROM nca_gps_stations s
      LEFT JOIN nca_gps_relations r USING (station)
      WHERE s.station = :station AND r.network = :network';

    return $this->_execQuery($sql, array(
      'network' => $network,
      'station' => $station
    ));
  }

  // Query db to get all stations in a given network
  // @return PDOStatement object
  public function queryStations ($network) {
    $sql = 'SELECT s.id, s.station, s.lat, s.lon, s.destroyed, s.showcoords,
      v.last_observation, v.up_rms, v.north_rms, v.east_rms
      FROM nca_gps_stations s
      LEFT JOIN nca_gps_relations r USING (station)
      LEFT JOIN nca_gps_velocities v USING (station)
      WHERE r.network = :network AND v.network = :network AND v.type = "nafixed"
      GROUP BY `station`
      ORDER BY `station` ASC';

    return $this->_execQuery($sql, array(
      'network' => $network
    ));
  }
}
