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
  $TITLE = 'GPS Station ' . strtoupper($station) . " <em>($network Network)</em>";
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

// Db query result: velocities for selected station and network
$rsVelocities = $db->queryVelocities($station, $network);

// Create an array of velocities by type
while ($row = $rsVelocities->fetch(PDO::FETCH_ASSOC)) {
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

  $velocities[$type]['north'] = $north;
  $velocities[$type]['east'] = $east;
  $velocities[$type]['up'] = $up;
}

// Db query result: station details for selected station and network
$rsStation = $db->queryStation($station, $network);

// Create the station model using the station details + $networkList, $velocities
$rsStation->setFetchMode(
  PDO::FETCH_CLASS,
  'Station',
  [$networkList, $velocities]
);
$stationModel = $rsStation->fetch();

// Create the view and render it
if ($stationModel) {
  $view = new StationView($stationModel);
  $view->render();
} else {
  print '<p class="alert error">ERROR: Station / Network Not Found</p>';
}
