<?php

include_once '../lib/functions/functions.inc.php'; // app functions

// set default value so page loads without passing params
$station = safeParam('station', '7adl');

if (!isset($TEMPLATE)) {
  $TITLE = 'GPS Station ' . strtoupper($station) . ' Field Logs';
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href"/css/logsheets/index.scss" />';
  $FOOT = '';

  include '../conf/config.inc.php'; // app config
  include '../lib/classes/Db.class.php'; // db connector, queries
  include '../lib/classes/LogsheetModel.class.php'; // model
  include '../lib/classes/LogsheetCollection.class.php'; // collection
  include '../lib/classes/LogsheetsView.class.php'; // view
  include 'template.inc.php';
}

$db = new Db();

// Db query result: station details for selected station
$rsStation = $db->queryStation($station);
$station_exists = $rsStation->fetch();

if ($station_exists) {
  // Get a list of logsheets for selected station
  $dir = sprintf('%s/stations/%s.dir/%s/logsheets',
    $DATA_DIR,
    substr($station, 0, 1),
    $station
  );
  // sort ASC so that 'Front' page (1) is listed before 'Back' page (2)
  $files = getDirContents($dir, $order=SCANDIR_SORT_ASCENDING);

  // Add logsheets to collection
  $logsheetCollection = new LogsheetCollection($station);
  foreach ($files as $file) {
    $logsheetModel = new LogsheetModel($file);
    $logsheetCollection->add($logsheetModel);
  }

  // Sort collection by date DESC (default)
  $logsheetCollection->sort();

  // Render HTML
  $view = new LogsheetsView($logsheetCollection);
  $view->render();
} else {
  print '<p class="alert error">ERROR: Station Not Found</p>';
}

?>
