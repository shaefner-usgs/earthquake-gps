<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

include_once '../lib/classes/Station.class.php'; // model
include_once '../lib/classes/StationView.class.php'; // view

// Set default network/station so page loads without passing params
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

// Db queries for the selected station/network
$rsNetworkList = $db->queryNetworkList($stationParam);
$rsNoise = $db->queryNoise($networkParam, $stationParam);
$rsOffsets = $db->queryOffsets($networkParam, $stationParam);
$rsPostSeismic = $db->queryPostSeismic($networkParam, $stationParam);
$rsSeasonal = $db->querySeasonal($networkParam, $stationParam);
$rsStation = $db->queryStation($stationParam, $networkParam);
$rsStations = $db->queryStations($networkParam); // all stations in network
$rsVelocities = $db->queryVelocities($networkParam, $stationParam);

// Create an array of networks the selected station belongs to
$networkList = [];
while ($row = $rsNetworkList->fetch(PDO::FETCH_ASSOC)) {
  $networkList[] = [
    'name' => $row['network'],
    'show' => intval($row['show'])
  ];
}

// Create the Station model for the selected station
$rsStation->setFetchMode(
  PDO::FETCH_CLASS,
  'Station', [
    $rsVelocities->fetchAll(PDO::FETCH_ASSOC),
    $rsNoise->fetchAll(PDO::FETCH_ASSOC),
    $rsOffsets->fetchAll(PDO::FETCH_ASSOC),
    $rsPostSeismic->fetchAll(PDO::FETCH_ASSOC),
    $rsSeasonal->fetchAll(PDO::FETCH_ASSOC),
    $rsStations->fetchAll(PDO::FETCH_ASSOC),
    $networkList
  ]
);
$stationModel = $rsStation->fetch();

// Get the 5 closest stations
$rsClosestStations = $db->queryClosestStations(
  $stationModel->lat,
  $stationModel->lon,
  $stationModel->station
);
$closestStations = array_slice(
  $rsClosestStations->fetchAll(PDO::FETCH_GROUP),
  0, 5, true // keep closest 5
);

// Create a Station model for each of the closest stations and keep select props
foreach ($closestStations as $station => $paramsList) {
  $network = '';
  foreach ($paramsList as $params) {
    if ($params['network'] === $networkParam) { // currently selected network
      $distance = $params['distance'];
      $network = $params['network'];
    }
  }
  if (!$network) { // set to first result in list if no match found for network
    $distance = $paramsList[0]['distance'];
    $network = $paramsList[0]['network'];
  }

  $rsClosestStation = $db->queryStation($station, $network);
  $rsClosestVelocities = $db->queryVelocities($network, $station);

  $rsClosestStation->setFetchMode(
    PDO::FETCH_CLASS,
    'Station', [
      $rsClosestVelocities->fetchAll(PDO::FETCH_ASSOC)
    ]
  );
  $closestStationModel = $rsClosestStation->fetch();

  // Set props to keep for closest stations list
  $closestStations[$station] = [
    'distance' => $distance,
    'lastUpdate' => $closestStationModel->lastUpdate,
    'network' => $network,
    'stationtype' => $closestStationModel->stationtype
  ];
}

$stationModel->closestStations = $closestStations;

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
