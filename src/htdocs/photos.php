<?php

include_once '../lib/functions/functions.inc.php'; // app functions

// set default value so page loads without passing params
$station = safeParam('station', '7adl');

if (!isset($TEMPLATE)) {
  $TITLE = 'GPS Station ' . strtoupper($station) . ' Photos';
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="/css/photos/index.css" />';
  $FOOT = '';

  include '../conf/config.inc.php'; // app config
  include '../lib/classes/Db.class.php'; // db connector, queries
  include '../lib/classes/PhotoModel.class.php'; // model
  include '../lib/classes/PhotoCollection.class.php'; // collection
  include '../lib/classes/PhotosView.class.php'; // view
  include 'template.inc.php';
}

$db = new Db();

// Db query result: station details for selected station
$rsStation = $db->queryStation($station);
$station_exists = $rsStation->fetch();

if ($station_exists) {
  // Get a list of photos for selected station
  $dir = sprintf('%s/stations/%s.dir/%s/photos/screen',
    $DATA_DIR,
    substr($station, 0, 1),
    $station
  );
  $files = getDirContents($dir);

  // Add photos to collection
  $photoCollection = new PhotoCollection($station);
  foreach ($files as $file) {
    $photoModel = new PhotoModel($file);
    $photoCollection->add($photoModel);
  }

  // Render HTML
  $view = new PhotosView($photoCollection);
  $view->render();
} else {
  print '<p class="alert error">ERROR: Station Not Found</p>';
}
