<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

include_once '../lib/classes/Station.class.php'; // Model
include_once '../lib/classes/StationCollection.class.php'; // Collection
include_once '../lib/classes/StationView.class.php'; // View

// Set default network/station so page loads without passing params
$networkParam = safeParam('network', 'Pacific');
$stationParam = strtolower(safeParam('station', 'aoa1'));

$stationName = strtoupper($stationParam);

if (!isset($TEMPLATE)) {
  $TITLE = sprintf ('<a href="../%s">%s Network</a>',
    $networkParam,
    $networkParam
  );
  $TITLETAG = "Station $stationName | $TITLE";
  $NAVIGATION = true;
  $HEAD = '
    <link rel="stylesheet" href="../lib/leaflet/leaflet.css" />
    <link rel="stylesheet" href="../css/station.css" />
  ';
  $FOOT = '
    <script>
      var MOUNT_PATH = "' . $MOUNT_PATH . '",
          NETWORK = "' . $networkParam . '",
          STATION = "' . $stationParam . '";
    </script>
    <script src="../lib/leaflet/leaflet.js"></script>
    <script src="../js/station.js"></script>
  ';
  $CONTACT = 'jsvarc';

  include 'template.inc.php';
}

$db = new Db;

// Db queries for the selected station
$rsNetworkList = $db->queryNetworkList($stationParam);
$rsNoise = $db->queryNoise($networkParam, $stationParam);
$rsOffsets = $db->queryOffsets($networkParam, $stationParam);
$rsPostSeismic = $db->queryPostSeismic($networkParam, $stationParam);
$rsSeasonal = $db->querySeasonal($networkParam, $stationParam);
$rsStation = $db->queryStation($stationParam, $networkParam);
$rsVelocities = $db->queryVelocities($networkParam, $stationParam);

// Db queries for all stations in selected network
$rsStations = $db->queryStations($networkParam);
$rsVelocitiesAll = $db->queryVelocities($networkParam);

// Create the Station Model for the selected station
$rsStation->setFetchMode(
  PDO::FETCH_CLASS,
  'Station', [
    $rsVelocities->fetchAll(PDO::FETCH_OBJ),
    $rsNetworkList->fetchAll(PDO::FETCH_OBJ),
    $rsNoise->fetchAll(PDO::FETCH_OBJ),
    $rsOffsets->fetchAll(PDO::FETCH_OBJ),
    $rsPostSeismic->fetchAll(PDO::FETCH_OBJ),
    $rsSeasonal->fetchAll(PDO::FETCH_OBJ)
  ]
);
$selectedStationModel = $rsStation->fetch();

// Create the Station Collection for the selected network
$stationCollection = new StationCollection($networkParam, $stationParam);

$rsStations->setFetchMode(
  PDO::FETCH_CLASS,
  'Station', [
    $rsVelocitiesAll->fetchAll(PDO::FETCH_UNIQUE|PDO::FETCH_OBJ)
  ]
);
$stationModels = $rsStations->fetchAll();

foreach($stationModels as $stationModel) {
  if ($stationModel->station === $stationParam) {
    $stationModel = $selectedStationModel; // use selected station's model which is more complete
  }
  $stationCollection->add($stationModel);
}

// Create the view and render it
if ($selectedStationModel) {
  $view = new StationView($stationCollection);
  $view->render();
} else {
  print '<p class="alert error">ERROR: Station / Network Not Found</p>';
}
