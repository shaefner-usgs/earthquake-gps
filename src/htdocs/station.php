<?php

include_once 'functions.inc.php'; // template functions
include_once '../conf/config.inc.php'; // app config, db connection
include_once '../lib/functions/functions.inc.php'; // app functions
include_once '../lib/classes/Station.class.php'; // model
include_once '../lib/classes/StationView.class.php'; // view

// set default values so page loads without passing params
$stationName = param('station', 'aoa1');
$networkName = param('network', 'Pacific');

if (!isset($TEMPLATE)) {
  $TITLE = 'GPS Station ' . strtoupper($stationName) . " ($networkName Network)";
  $HEAD = '';
  $FOOT = '';

  include_once 'template.inc.php';
}

// Db query result for all networks matching a station
$rsNetworks = getNetworks($DB, $stationName);

// Create the networks array using the db result
$networks = array();
while ($row = $rsNetworks->fetch(PDO::FETCH_ASSOC)) {
  array_push($networks, $row['network']);
}

// Db query result for station details matching a station and network
$rsStation = getStation($DB, $stationName, $networkName);

// Create the station model using the db result and $networks array
$rsStation->setFetchMode(
  PDO::FETCH_CLASS,
  'Station',
  array($networks)
);
$station = $rsStation->fetch();

// Create the view and render it
if ($station) {
  $view = new StationView($station);
  $view->render();
} else {
  print "ERROR: Station / Network Not Found.";
}
