<?php

include_once 'functions.inc.php';
include_once '../conf/config.inc.php'; // db connection
include_once '../lib/classes/Station.class.php'; // model
include_once '../lib/classes/StationView.class.php'; // view

$stationName = param('station', 'HALY');
$networkName = param('network', 'WindKetchFlat_SGPS');

if (!isset($TEMPLATE)) {
  $TITLE = "GPS Station $stationName";
  $HEAD = '';
  $FOOT = '';

  include_once 'template.inc.php';
}

// Create a station obj using the db result
try {
  $sqlStation = "SELECT s.lat, s.lon, s.elevation, s.x, s.y, s.z,
    r.stationtype, r.showcoords
    FROM nca_gps_stations s
    LEFT JOIN nca_gps_relations r USING (station)
    WHERE s.station = :station AND r.network = :network";

  $rsStation = $DB->prepare($sqlStation);
  $rsStation->bindValue(':station', $stationName, PDO::PARAM_STR);
  $rsStation->bindValue(':network', $networkName, PDO::PARAM_STR);
  $rsStation->execute();

  // Instantiate Station class which creates the station model
  $rsStation->setFetchMode(PDO::FETCH_CLASS,
    'Station', array($stationName, $networkName));
  $station = $rsStation->fetch();

  if (!$station) {
    print "ERROR: Cannot find station ($stationName) and/or network ($networkName).";
  }
} catch(Exception $e) {
  print 'ERROR: ' . $e->getMessage();
}

// Add networks to station obj
try {
  $sqlNetworks = "SELECT network FROM nca_gps_relations WHERE station = :station";

  $rsNetworks = $DB->prepare($sqlNetworks);
  $rsNetworks->bindValue(':station', $stationName, PDO::PARAM_STR);
  $rsNetworks->execute();
  $rsNetworks->setFetchMode(PDO::FETCH_INTO, 'Station');
} catch(Exception $e) {
  print 'ERROR: ' . $e->getMessage();
}

// Create the view and render it
$view = new StationView($station);
$view->render();

?>
