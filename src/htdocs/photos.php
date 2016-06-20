<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

include_once '../lib/classes/Photo.class.php'; // model
include_once '../lib/classes/PhotoCollection.class.php'; // collection
include_once '../lib/classes/PhotoView.class.php'; // view

// set default values so page loads without passing params
$network = safeParam('network', 'WindKetchFlat_SGPS');
$station = safeParam('station', '7adl');

$name = strtoupper($station);

if (!isset($TEMPLATE)) {
  $TITLE = "GPS Station $name Photos";
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="' . $MOUNT_PATH . '/css/photos.css" />';
  $CONTACT = 'jsvarc';

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
  $photoCollection = new PhotoCollection($station, $network);
  foreach ($files as $file) {
    $photoModel = new Photo($file);
    $photoCollection->add($photoModel);
  }

  // Render HTML
  $view = new PhotoView($photoCollection);
  $view->render();
} else {
  print '<p class="alert error">ERROR: Station Not Found</p>';
}
