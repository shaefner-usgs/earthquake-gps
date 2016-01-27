<?php

// Query db to get all networks a given station belongs to
// @return PDOStatement object
function getNetworks ($db, $station) {
  try {
    $sqlNetworks = "SELECT network
      FROM nca_gps_relations
      WHERE station = :station
      ORDER BY network ASC";

    $stmt = $db->prepare($sqlNetworks);
    $stmt->bindValue(':station', $station, PDO::PARAM_STR);
    $stmt->execute();

    return $stmt;
  } catch(Exception $e) {
    print 'ERROR: ' . $e->getMessage();
  }
}

// Query db to get details for a given station and network
// @return PDOStatement object
function getStation ($db, $station, $network) {
  try {
    $sqlStation = "SELECT s.lat, s.lon, s.elevation, s.x, s.y, s.z, s.station,
      r.stationtype, r.showcoords, r.network
      FROM nca_gps_stations s
      LEFT JOIN nca_gps_relations r USING (station)
      WHERE s.station = :station AND r.network = :network";

    $stmt = $db->prepare($sqlStation);
    $stmt->bindValue(':station', $station, PDO::PARAM_STR);
    $stmt->bindValue(':network', $network, PDO::PARAM_STR);
    $stmt->execute();

    return $stmt;
  } catch(Exception $e) {
    print 'ERROR: ' . $e->getMessage();
  }
}
