<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

include_once '../lib/classes/Station.class.php'; // model
include_once '../lib/classes/StationView.class.php'; // view

// set default values so page loads without passing params
$network = safeParam('network', 'Pacific');
$station = safeParam('station', 'aoa1');

if (!isset($TEMPLATE)) {
  $TITLE = "$network Network";
  $SUBTITLE = 'Station ' . strtoupper($station);
  $TITLETAG = "$SUBTITLE | $TITLE";
  $NAVIGATION = true;
  $HEAD = '
    <link rel="stylesheet" href="/lib/leaflet-0.7.7/leaflet.css" />
    <link rel="stylesheet" href="../css/station.css" />
  ';
  $FOOT = '
    <script>
      var MOUNT_PATH = "' . $MOUNT_PATH . '",
          NETWORK = "' . $network . '",
          STATION = "' . $station . '";
    </script>
    <script src="/lib/leaflet-0.7.7/leaflet.js"></script>
    <script src="../js/station.js"></script>
  ';
  $CONTACT = 'jsvarc';

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

// Db query results for selected network and station
$rsNoise = $db->queryNoise($network, $station);
$rsOffsets = $db->queryOffsets($network, $station);
$rsPostSeismic = $db->queryPostSeismic($network, $station);
$rsSeasonal = $db->querySeasonal($network, $station);
$rsVelocities = $db->queryVelocities($network, $station);

// Db query result: station details for selected station and network
$rsStation = $db->queryStation($station, $network);

// Create the station model using the station details + $networkList, etc.
$rsStation->setFetchMode(
  PDO::FETCH_CLASS,
  'Station',
  [
    $networkList,
    $rsNoise,
    $rsOffsets,
    $rsPostSeismic,
    $rsSeasonal,
    $rsVelocities
  ]
);
$stationModel = $rsStation->fetch();

// Create the view and render it
print '<h2 class="subtitle">' . $SUBTITLE . '</h2>';
if ($stationModel) {
  $view = new StationView($stationModel);
  $view->render();
} else {
  print '<p class="alert error">ERROR: Station / Network Not Found</p>';
}
