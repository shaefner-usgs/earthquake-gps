<?php

//include_once 'functions.inc.php'; // template functions
include_once '../lib/functions/functions.inc.php'; // app functions

// set default values so page loads without passing params
$station = safeParam('station', 'aoa1');
$network = safeParam('network', 'Pacific');

if (!isset($TEMPLATE)) {
  include '../conf/config.inc.php'; // app config, db connection
  include '../lib/classes/Station.class.php'; // model
  include '../lib/classes/StationView.class.php'; // view

  $TITLE = 'GPS Station ' . strtoupper($station) . " ($network Network)";
  $HEAD = '';
  $FOOT = '';

  include 'template.inc.php';
}

// Db query result: all "non-hidden" networks that selected station belongs to
$rsNetworkList = queryNetworkList($DB, $station);

// Create an array of networks
$networkList = array();
while ($row = $rsNetworkList->fetch(PDO::FETCH_ASSOC)) {
  array_push($networkList, $row['network']);
}
// Add selected network if it's not already in the list (this would happen if
// user is currently viewing a "hidden" network)
if (!in_array($network, $networkList)) {
  array_push($networkList, $network);
}

// Db query result: station details for selected station and network
$rsStation = queryStation($DB, $station, $network);

// Create the station model using the station details and $networkList array
$rsStation->setFetchMode(
  PDO::FETCH_CLASS,
  'Station',
  array($networkList)
);
$stationModel = $rsStation->fetch();

// Create the view and render it
if ($stationModel) {
  $view = new StationView($stationModel);
  $view->render();
} else {
  print "ERROR: Station / Network Not Found.";
}
