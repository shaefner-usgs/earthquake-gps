<?php

/**
 * Create an array of all valid URLs for every page in app and then feed each
 *   URL into python invalidation script.
 */

include_once '../conf/config.inc.php'; // app config
include_once dirname(__FILE__) . '/classes/Db.class.php'; // db connector, queries

$db = new Db;

$urlBase = 'https://earthquake.usgs.gov/monitoring/gps';

// First, add URLs to "one-off" pages, then add dynmically created network/station pages
$urls = [
  $urlBase,
  "$urlBase/about.php",
  "$urlBase/citation.php",
  "$urlBase/sources.php",
  "$urlBase/stations"
];

// Db query: all non-hidden networks
$rsNetworks = $db->queryNetworks();

while ($row1 = $rsNetworks->fetch(PDO::FETCH_OBJ)) {
  $network = $row1->network;

  array_push($urls,
    "$urlBase/$network/notupdated",
    "$urlBase/$network/offsets",
    "$urlBase/$network/velocities"
  );

  // Db query: all stations in a given network
  $rsStations = $db->queryStations($network);

  while ($row2 = $rsStations->fetch(PDO::FETCH_OBJ)) {
    $station = $row2->station;

    array_push($urls,
      "$urlBase/$network/$station",
      "$urlBase/$network/$station/kinematic",
      "$urlBase/$network/$station/logs",
      "$urlBase/$network/$station/photos",
      "$urlBase/$network/$station/qc",
      "$urlBase/$network/$station/qc/table"
    );
  }
}

// Now call python script with URLs
foreach ($urls as $url) {
  exec("python3 invalidate.py $url");
}
