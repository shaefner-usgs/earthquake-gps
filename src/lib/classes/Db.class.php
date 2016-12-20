<?php

/**
 * Database connector and queries for GPS app
 *
 * @author Scott Haefner <shaefner@usgs.gov>
 */
class Db {
  private static $db;

  public function __construct() {
    try {
      $this->db = new PDO($GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASS']);
      $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
      print '<p class="alert error">ERROR 1: ' . $e->getMessage() . '</p>';
    }
  }

  /**
   * Perform db query
   *
   * @param $sql {String}
   *     SQL query
   * @param $params {Array} default is NULL
   *     key-value substitution params for SQL query
   *
   * @return $stmt {Object} - PDOStatement object
   */
  private function _execQuery ($sql, $params=NULL) {
    try {
      $stmt = $this->db->prepare($sql);

      // bind sql params
      if (is_array($params)) {
        foreach ($params as $key => $value) {
          $type = $this->_getType($value);
          $stmt->bindValue($key, $value, $type);
        }
      }
      $stmt->execute();

      return $stmt;
    } catch(Exception $e) {
      print '<p class="alert error">ERROR 2: ' . $e->getMessage() . '</p>';
    }
  }

  /**
   * Get data type for a sql parameter (PDO::PARAM_* constant)
   *
   * @param $var {?}
   *     variable to identify type of
   *
   * @return $type {Integer}
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
   * Query db to get a list of real-time earthquakes
   *
   * @param $mag {Int} default is 2.5
   * @param $days {Int} default is 7
   *
   * @return {Function}
   */
  public function queryEarthquakes ($mag=2.5, $days=7) {
    $sql = 'SELECT * FROM earthquakes.recenteqs_pdl
      WHERE mag >= :mag AND `datetime (GMT)` >= (NOW() - INTERVAL :days DAY)
      ORDER BY `datetime (GMT)` ASC';

    return $this->_execQuery($sql, array(
      'mag' => $mag,
      'days' => $days
    ));
  }

  /**
   * Query db to get a list of stations for a given network that aren't up to date
   *
   * @param $network {String}
   * @param $days {Int} default is 7
   *
   * @return {Function}
   */
  public function queryLastUpdated ($network, $days=7) {
    $sql = 'SELECT `station`, `last_observation` FROM gps_velocities
      WHERE `datatype` = "na" AND `network` = :network
        AND `last_observation` < (NOW() - INTERVAL :days DAY)
      ORDER BY `last_observation` DESC, `station` ASC';

    return $this->_execQuery($sql, array(
      'network' => $network,
      'days' => $days
    ));
  }

  /**
   * Query db to get a list of "non-hidden" networks a given station belongs to
   *
   * @param $station {String}
   *
   * @return {Function}
   */
  public function queryNetworkList ($station) {
    $sql = 'SELECT r.network FROM gps_relations r
      LEFT JOIN gps_networks n ON r.network = n.network
      WHERE r.station = :station AND n.show = 1
      ORDER BY `network` ASC';

    return $this->_execQuery($sql, array(
      'station' => $station
    ));
  }

  /**
   * Query db to get details for a given network
   *
   * @param $network {String}
   *
   * @return {Function}
   */
  public function queryNetwork ($network) {
    $sql = 'SELECT * FROM gps_networks
      WHERE `network` = :network';

    return $this->_execQuery($sql, array(
      'network' => $network
    ));
  }

  /**
   * Query db to get all "non-hidden" networks
   *
   * @return {Function}
   */
  public function queryNetworks () {
    $sql = 'SELECT * FROM gps_networks
      WHERE `show` = 1
      ORDER BY `network` ASC';

    return $this->_execQuery($sql);
  }

  /**
   * Query db to get noise for a given network and optional station
   *
   * @param $network {String}
   * @param $station {String} default is NULL
   *
   * @return {Function}
   */
  public function queryNoise ($network, $station=NULL) {
    $params['network'] = $network;
    $where = "`network` = :network";

    if ($station) { // add station info to query
      $params['station'] = $station;
      $where .= ' AND  `station` = :station';
    }

    $sql = "SELECT * FROM gps_noise
      WHERE $where";

    return $this->_execQuery($sql, $params);
  }

  /**
   * Query db to get offsets for a given network and optional station
   *
   * @param $network {String}
   * @param $station {String} default is NULL
   *
   * @return {Function}
   */
  public function queryOffsets ($network, $station=NULL) {
    $params['network'] = $network;
    $where = "`network` = :network";

    if ($station) {
      $params['station'] = $station;
      $where .= ' AND  `station` = :station';
    }

    $sql = "SELECT * FROM gps_offsets
      WHERE $where
      ORDER BY `year` ASC";

    return $this->_execQuery($sql, $params);
  }

  /**
   * Query db to get post seismic data for a given network and optional station
   *
   * @param $network {String}
   * @param $station {String} default is NULL
   *
   * @return {Function}
   */
  public function queryPostSeismic ($network, $station=NULL) {
    $params['network'] = $network;
    $where = "`network` = :network";

    if ($station) { // add station info to query
      $params['station'] = $station;
      $where .= ' AND  `station` = :station';
    }

    $sql = "SELECT * FROM gps_postseismic
      WHERE $where
      ORDER BY `year` ASC";

    return $this->_execQuery($sql, $params);
  }

  /**
   * Query db to get a QC data for a given station
   *
   * @param $station {String}
   * @param $limit {Int} default is NULL
   *
   * @return {Function}
   */
  public function queryQcData ($station, $limit=NULL) {
    $sql = 'SELECT * FROM gps_qualitycontrol
      WHERE `station` = :station
      ORDER BY `date` DESC';

    if ($limit) {
      $sql .= " LIMIT $limit";
    }

    return $this->_execQuery($sql, array(
      'station' => $station
    ));
  }

  /**
   * Query db to get seasonal data for a given network and optional station
   *
   * @param $network {String}
   * @param $station {String} default is NULL
   *
   * @return {Function}
   */
  public function querySeasonal ($network, $station=NULL) {
    $params['network'] = $network;
    $where = "`network` = :network";

    if ($station) { // add station info to query
      $params['station'] = $station;
      $where .= ' AND  `station` = :station';
    }

    $sql = "SELECT * FROM gps_seasonal
      WHERE $where";

    return $this->_execQuery($sql, $params);
  }

  /**
   * Query db to get station details for a given station and (optional) network
   *
   * @param $station {String}
   * @param $network {String} default is NULL
   *
   * @return {Function}
   */
  public function queryStation ($station, $network=NULL) {
    $params['station'] = $station;
    $sql = 'SELECT s.lat, s.lon, s.elevation, s.x, s.y, s.z, s.station,
      s.showcoords, r.stationtype, r.network
      FROM gps_stations s
      LEFT JOIN gps_relations r USING (station)
      WHERE s.station = :station';

    if ($network) {
      $params['network'] = $network;
      $sql .= " AND r.network = :network";
    }

    return $this->_execQuery($sql, $params);
  }

  /**
   * Query db to get alphanumeric list (first char only) of stations for jumplist
   *
   * @return {Function}
   */
  public function queryStationChars () {
    $sql = 'SELECT DISTINCT LEFT(r.station, 1) AS `alphanum`
      FROM gps_relations r
      LEFT JOIN gps_networks n ON (r.network = n.network)
      WHERE n.show = 1
      ORDER BY
        CASE WHEN LEFT(alphanum, 1) REGEXP ("^[0-9]") THEN 1 ELSE 0 END,
          LEFT(alphanum, 1)';

    return $this->_execQuery($sql);
  }

  /**
   * Query db to get a list of stations and their associated networks
   *
   * @param $firstchar {String/Int} default is NULL
   *     optional char to filter stations (e.g. only stations starting w 'a')
   *
   * @return {Function}
   */
  public function queryStationList ($firstchar=NULL) {
    $filter = "$firstchar%";
    $show = 1; // show only stations in 'non-hidden' networks by default

    if ($firstchar === 'hidden') { // show stations in 'hidden' networks as well
      $filter = '%';
      $show = 0;
    }

    $sql = 'SELECT r.station, r.network
      FROM gps_relations r
      LEFT JOIN gps_networks n ON (r.network = n.network)
      WHERE r.station LIKE :filter AND n.show = :show
      ORDER BY `station` ASC, `network` ASC';

      return $this->_execQuery($sql, array(
        'filter' => $filter,
        'show' => $show
      ));
    }

  /**
   * Query db to get all stations (with the option to limit to a given network)
   *
   * @param $network {String} default is NULL
   *
   * @return {Function}
   */
  public function queryStations ($network=NULL) {
    $fields = 's.id, s.station, s.lat, s.lon, s.destroyed, s.showcoords,
      s.elevation, s.x, s.y, s.z, s.num_obs, s.obs_years,
      r.network, r.stationtype, n.show';
    $joinClause = 'LEFT JOIN gps_relations r USING (station)
      LEFT JOIN gps_networks n ON n.network = r.network';
    $where = 'n.show = 1';

    if ($network) { // add velocity fields and limit results to given network
      //$fields .= ', v.last_observation, v.up_rms, v.north_rms, v.east_rms';
      //$joinClause .= ' LEFT JOIN gps_velocities v USING (station)';
      //$where = 'r.network = :network AND v.network = :network
      //  AND v.datatype = "nafixed"';
      $where = 'r.network = :network';
    }

    $sql = "SELECT $fields
      FROM gps_stations s
      $joinClause
      WHERE $where
      #GROUP BY s.station
      ORDER BY s.station ASC";

    return $this->_execQuery($sql, array(
      'network' => $network
    ));
  }

  /**
   * Query db to get time series data for a given station
   *
   * @param $station {String}
   *
   * @return {Function}
   */
  public function queryTimeSeries ($station) {
    $sql = 'SELECT * FROM gps_timeseries
      WHERE station = :station
      ORDER BY `epoch` ASC';

    return $this->_execQuery($sql, array(
      'station' => $station
    ));
  }

  /**
   * Query db to get velocities data for a given network and optional station
   *
   * @param $network {String}
   * @param $station {String} default is NULL
   *
   * @return {Function}
   */
  public function queryVelocities ($network, $station=NULL) {
    $order = 'station ASC, datatype ASC';
    $params['network'] = $network;
    $where = 'network = :network';

    if ($station) { // add station info to query
      $order = 'last_observation DESC';
      $params['station'] = $station;
      $where .= ' AND station = :station';
    }

    $sql = "SELECT * FROM gps_velocities
      WHERE $where
      ORDER BY $order";

    return $this->_execQuery($sql, $params);
  }
}
