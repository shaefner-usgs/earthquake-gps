<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

include_once '../lib/classes/Logsheet.class.php'; // model
include_once '../lib/classes/LogsheetCollection.class.php'; // collection
include_once '../lib/classes/LogsheetView.class.php'; // view

// set default values so page loads without passing params
$networkParam = safeParam('network', 'WindKetchFlat_SGPS');
$stationParam = safeParam('station', '7adl');

if (!isset($TEMPLATE)) {
  $TITLE = 'GPS Station ' . strtoupper($stationParam) . ' - Field Logs';
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="../../css/base.css" />';
  $CONTACT = 'jsvarc';

  include 'template.inc.php';
}

$db = new Db();

// Db query result: station details for selected station
$rsStation = $db->queryStation($stationParam);
$station = $rsStation->fetch(PDO::FETCH_ASSOC);

if ($station) {
  // Get a list of logsheets for selected station
  $dir = sprintf('%s/stations/%s.dir/%s/logsheets',
    $DATA_DIR,
    substr($stationParam, 0, 1),
    $stationParam
  );
  // sort ASC so that 'Front' page (1) is listed before 'Back' page (2)
  $files = getDirContents($dir, $order=SCANDIR_SORT_ASCENDING);

  // Add logsheets to collection
  $logsheetCollection = new LogsheetCollection(
    $networkParam, $stationParam, $station['stationtype']
  );
  foreach ($files as $file) {
    $logsheetModel = new Logsheet($file);
    $logsheetCollection->add($logsheetModel);
  }

  // Sort collection by date DESC (default)
  $logsheetCollection->sort();

  // Render HTML
  $view = new LogsheetView($logsheetCollection);
  $view->render();
} else {
  print '<p class="alert error">ERROR: Station Not Found</p>';
}
