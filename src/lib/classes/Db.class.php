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
    $sql = 'SELECT `station`, `last_observation` FROM nca_gps_velocities
      WHERE `type` = "nafixed" AND `network` = :network
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
    $sql = 'SELECT r.network FROM nca_gps_relations r
      LEFT JOIN nca_gps_networks n ON r.network = n.name
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
    $sql = 'SELECT * FROM nca_gps_networks
      WHERE `name` = :network';

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
    $sql = 'SELECT * FROM nca_gps_networks
      WHERE `show` = 1
      ORDER BY `name` ASC';

    return $this->_execQuery($sql);
  }

  /**
   * Query db to get offsets for a given station
   *
   * @param $network {String}
   *
   * @return {Function}
   */
  public function queryOffsets ($station) {
    $sql = 'SELECT * FROM nca_gps_offsets
      WHERE `station` = :station
      ORDER BY `datatype` ASC, `component` ASC';

    return $this->_execQuery($sql, array(
      'station' => $station
    ));
  }

  /**
   * Query db to get a QC data for a given station
   *
   * @param $station {String}
   * @param $limit {Int}
   *
   * @return {Function}
   */
  public function queryQcData ($station, $limit=NULL) {
    $sql = 'SELECT * FROM nca_gps_qualitycontrol
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
      FROM nca_gps_stations s
      LEFT JOIN nca_gps_relations r USING (station)
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
      FROM nca_gps_relations r
      LEFT JOIN nca_gps_networks n ON (r.network = n.name)
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
    $sql = 'SELECT r.station, r.network
      FROM nca_gps_relations r
      LEFT JOIN nca_gps_networks n ON (r.network = n.name)
      WHERE r.station LIKE :filter AND n.show = 1
      ORDER BY `station` ASC, `network` ASC';

      return $this->_execQuery($sql, array(
        'filter' => $filter
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
      s.elevation, s.x, s.y, s.z, s.first_obs, s.last_obs, s.num_obs,
      s.total_years, r.network, r.stationtype';
    $joinClause = 'LEFT JOIN nca_gps_relations r USING (station)';
    $whereClause = '';

    if ($network) { // add velocity fields and limit results to given network
      $fields .= ', v.last_observation, v.up_rms, v.north_rms, v.east_rms';
      $joinClause .= ' LEFT JOIN nca_gps_velocities v USING (station)';
      $whereClause = 'WHERE r.network = :network AND v.network = :network
        AND v.type = "nafixed"';
    }

    $sql = "SELECT $fields
      FROM nca_gps_stations s
      $joinClause
      $whereClause
      GROUP BY `station`
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
    $sql = 'SELECT * FROM nca_gps_timeseries
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
    $order = 'station ASC, type ASC';
    $params['network'] = $network;
    $where = 'network = :network';

    if ($station) { // add station info to query
      $order = 'last_observation DESC';
      $params['station'] = $station;
      $where .= ' AND station = :station';
    }

    $sql = "SELECT * FROM nca_gps_velocities
      WHERE $where
      ORDER BY $order";

    return $this->_execQuery($sql, $params);
  }

  /**
   * Create an array of velocities grouped by station, type, and component
   *
   * the velocites table contains data in different reference frames ('type')
   * and only certain fields are applicable to each ref. frame
   *
   * @param $rsVelocities {Object} - PDOStatement object
   *
   * @return $velocites {Array}
   */
  public function createVelocitiesArray ($rsVelocities) {
    while ($row = $rsVelocities->fetch(PDO::FETCH_ASSOC)) {
      // stations are stored in lowercase in db except in this table
      $station = strtolower($row['station']);
      $type = trim($row['type']);

      // Shared props
      $north = [
        'velocity' => $row['north_velocity'],
        'sigma' => $row['north_sigma']
      ];
      $east = [
        'velocity' => $row['east_velocity'],
        'sigma' => $row['east_sigma']
      ];
      $up = [
        'velocity' => $row['up_velocity'],
        'sigma' => $row['up_sigma']
      ];

      // Props based on type (cleaned, itrf2008, nafixed)
      if ($type === 'cleaned') {
        $north['whitenoise'] = $row['whitenoisenorth'];
        $north['randomwalk'] = $row['randomwalknorth'];
        $north['flickernoise'] = $row['flickernoisenorth'];
        $east['whitenoise'] = $row['whitenoiseeast'];
        $east['randomwalk'] = $row['randomwalkeast'];
        $east['flickernoise'] = $row['flickernoiseeast'];
        $up['whitenoise'] = $row['whitenoiseup'];
        $up['randomwalk'] = $row['randomwalkup'];
        $up['flickernoise'] = $row['flickernoiseup'];
      } else {
        $north['rms'] = $row['north_rms'];
        $east['rms'] = $row['east_rms'];
        $up['rms'] = $row['up_rms'];
      }

      $velocities['data'][$station][$type]['north'] = $north;
      $velocities['data'][$station][$type]['east'] = $east;
      $velocities['data'][$station][$type]['up'] = $up;

      // Lookup table for column names
      $velocities['lookup'] = [
        'flickernoise' => 'Flicker Noise',
        'randomwalk' => 'Random Walk',
        'rms' => 'RMS (mm)',
        'sigma' => 'Uncertainty (mm/yr)	',
        'velocity' => 'Velocity (mm/yr)	',
        'whitenoise' => 'White Noise'
      ];
    }

    return $velocities;
  }
}
