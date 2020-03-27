<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

include_once '../lib/classes/Station.class.php'; // model
include_once '../lib/classes/StationView.class.php'; // view

// Set params, including default values so page loads with no params
$networkParam = safeParam('network', 'Pacific');
$stationParam = strtolower(safeParam('station', 'aoa1'));

$stationName = strtoupper($stationParam);

if (!isset($TEMPLATE)) {
  $TITLE = sprintf ('<a href="../%s">%s Network</a>',
    $networkParam,
    $networkParam
  );
  $SUBTITLE = 'Station ' . $stationName;
  $TITLETAG = "$SUBTITLE | $TITLE";
  $NAVIGATION = true;
  $HEAD = '
    <link rel="stylesheet" href="/lib/leaflet/leaflet.css" />
    <link rel="stylesheet" href="../css/station.css" />
  ';
  $FOOT = '
    <script>
      var MOUNT_PATH = "' . $MOUNT_PATH . '",
          NETWORK = "' . $networkParam . '",
          STATION = "' . $stationParam . '";
    </script>
    <script src="/lib/leaflet/leaflet.js"></script>
    <script src="../js/station.js"></script>
  ';
  $CONTACT = 'jsvarc';

  include 'template.inc.php';
}

$db = new Db;

// Db queries for selected network and station
$rsNetworkList = $db->queryNetworkList($stationParam);
$rsNoise = $db->queryNoise($networkParam, $stationParam);
$rsOffsets = $db->queryOffsets($networkParam, $stationParam);
$rsPostSeismic = $db->queryPostSeismic($networkParam, $stationParam);
$rsSeasonal = $db->querySeasonal($networkParam, $stationParam);
$rsStation = $db->queryStation($stationParam, $networkParam);
$rsVelocities = $db->queryVelocities($networkParam, $stationParam);

// Create an array of networks station belongs to
$networkList = [];
while ($row = $rsNetworkList->fetch(PDO::FETCH_ASSOC)) {
  $networkList[] = [
    'name' => $row['network'],
    'show' => intval($row['show'])
  ];
}

// Create the Station model
$rsStation->setFetchMode(
  PDO::FETCH_CLASS,
  'Station', [
    $networkList,
    $rsNoise->fetchAll(PDO::FETCH_ASSOC),
    $rsOffsets->fetchAll(PDO::FETCH_ASSOC),
    $rsPostSeismic->fetchAll(PDO::FETCH_ASSOC),
    $rsSeasonal->fetchAll(PDO::FETCH_ASSOC),
    $rsVelocities->fetchAll(PDO::FETCH_ASSOC)
  ]
);
$stationModel = $rsStation->fetch();

// Add closest stations which depend on Station model already being created
$rsClosestStations = $db->queryClosestStations(
  $stationModel->lat,
  $stationModel->lon,
  $stationModel->station
);
$closestStations = $rsClosestStations->fetchAll(PDO::FETCH_GROUP);
$stationModel->closestStations = array_slice($closestStations, 0, 5, true); // closest 5

// Create the view and render it
printf ('<h2 class="subtitle"><a class="%s button">%s</a></h2>',
  getColor($stationModel->lastUpdate),
  $SUBTITLE
);
if ($stationModel) {
  $view = new StationView($stationModel);
  $view->render();
} else {
  print '<p class="alert error">ERROR: Station / Network Not Found</p>';
}
