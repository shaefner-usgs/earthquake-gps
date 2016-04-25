<?php

include_once '../lib/functions/functions.inc.php'; // app functions

// set default values so page loads without passing params
$station = safeParam('station', 'aoa1');
$network = safeParam('network', 'Pacific');

if (!isset($TEMPLATE)) {
  $TITLE = 'GPS Station ' . strtoupper($station) . " ($network Network)";
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="/css/station.css" />';
  $FOOT = '<script src="/js/station.js"></script>';

  include '../lib/classes/Db.class.php'; // db connector, queries
  include '../lib/classes/StationModel.class.php'; // model
  include '../lib/classes/StationView.class.php'; // view
  include 'template.inc.php';
}

$db = new Db;

// Db query result: all "non-hidden" networks that selected station belongs to
$rsNetworkList = $db->queryNetworkList($station);

// Create an array of networks
$networkList = array();
while ($row = $rsNetworkList->fetch(PDO::FETCH_ASSOC)) {
  array_push($networkList, $row['network']);
}
// Add currently selected network if it's not already in the list
// (this would happen if user is viewing a "hidden" network)
if (!in_array($network, $networkList)) {
  array_push($networkList, $network);
}

// Db query result: station details for selected station and network
$rsStation = $db->queryStation($station, $network);

// Create the station model using the station details and $networkList array
$rsStation->setFetchMode(
  PDO::FETCH_CLASS,
  'StationModel',
  array($networkList)
);
$stationModel = $rsStation->fetch();

// Create the view and render it
if ($stationModel) {
  $view = new StationView($stationModel);
  $view->render();
} else {
  print '<p class="alert error">ERROR: Station / Network Not Found</p>';
}
