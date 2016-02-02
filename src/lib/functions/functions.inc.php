<?php

// Query db to get a list of "non-hidden" networks a given station belongs to
// @return PDOStatement object
function queryNetworkList ($db, $station) {
  try {
    $sql = 'SELECT r.network
      FROM nca_gps_relations r
      LEFT JOIN nca_gps_networks n ON r.network = n.name
      WHERE r.station = :station AND n.show = "1"
      ORDER BY `network` ASC';

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':station', $station, PDO::PARAM_STR);
    $stmt->execute();

    return $stmt;
  } catch(Exception $e) {
    print 'ERROR: ' . $e->getMessage();
  }
}

// Query db to get all "non-hidden" networks
// @return PDOStatement object
function queryNetworks ($db) {
  try {
    $sql = 'SELECT *
      FROM nca_gps_networks
      WHERE `show` = "1"
      ORDER BY `name` ASC';

    $stmt = $db->query($sql);

    return $stmt;
  } catch(Exception $e) {
    print 'ERROR: ' . $e->getMessage();
  }
}

// Query db to get station details for a given station and network
// @return PDOStatement object
function queryStation ($db, $station, $network) {
  try {
    $sql = 'SELECT s.lat, s.lon, s.elevation, s.x, s.y, s.z, s.station,
      s.showcoords, r.stationtype, r.network
      FROM nca_gps_stations s
      LEFT JOIN nca_gps_relations r USING (station)
      WHERE s.station = :station AND r.network = :network';

    $stmt = $db->prepare($sql);
    $stmt->bindValue(':station', $station, PDO::PARAM_STR);
    $stmt->bindValue(':network', $network, PDO::PARAM_STR);
    $stmt->execute();

    return $stmt;
  } catch(Exception $e) {
    print 'ERROR: ' . $e->getMessage();
  }
}

// Query db to get all stations in a given network
// @return PDOStatement object
function queryStations ($db, $network) {
  try {
    $sql = 'SELECT s.id, s.station, s.lat, s.lon, s.destroyed, s.showcoords,
      v.last_observation, v.up_rms, v.north_rms, v.east_rms
      FROM nca_gps_stations s
      LEFT JOIN nca_gps_relations r USING (station)
      LEFT JOIN nca_gps_velocities v USING (station)
      WHERE r.network = :network AND v.network = :network AND v.type = "nafixed"
      GROUP BY `station`
      ORDER BY `station` ASC';

    $stmt = $db->prepare($sql);
    $stmt->execute();

    return $stmt;
  } catch(Exception $e) {
    print 'ERROR: ' . $e->getMessage();
  }
}

/**
 * Get a request parameter from $_GET or $_POST.
 *
 * @param $name {String}
 *        The parameter name.
 * @param $default {?} default is null.
 *        Optional default value if the parameter was not provided.
 * @param $filter {PHP Sanitize filter} default is FILTER_SANITIZE_STRING
 *        Optional sanitizing filter to apply
 * @return $value {String}
 */
function safeParam ($name, $default=null, $filter=FILTER_SANITIZE_STRING) {
	$value = null;

	if (isset($_POST[$name])) {
		$value = filter_input(INPUT_POST, $name, $filter);
	} else if (isset($_GET[$name])) {
    $value = filter_input(INPUT_GET, $name, $filter);
	} else {
		$value = $default;
	}

	return $value;
}
