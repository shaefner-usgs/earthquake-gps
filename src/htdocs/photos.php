<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

include_once '../lib/classes/Photo.class.php'; // model
include_once '../lib/classes/PhotoCollection.class.php'; // collection
include_once '../lib/classes/PhotoView.class.php'; // view

// set default values so page loads without passing params
$networkParam = safeParam('network', 'WindKetchFlat_SGPS');
$stationParam = safeParam('station', '7adl');

if (!isset($TEMPLATE)) {
  $TITLE = "$networkParam Network";
  $SUBTITLE = sprintf ('<a href="../%s">Station %s</a> <span>Field Logs</span>',
    $stationParam,
    strtoupper($stationParam)
  );
  $TITLETAG = "$SUBTITLE | $TITLE";
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="../../css/photos.css" />';
  $FOOT = '
    <script src="../../lib/simplbox/simplbox.js"></script>
    <script src="../../js/photos.js"></script>
  ';
  $CONTACT = 'jsvarc';

  include 'template.inc.php';
}

$db = new Db();

// Db query result: station details for selected station
$rsStation = $db->queryStation($stationParam);
$station = $rsStation->fetch();

printf ('<h2 class="subtitle %s">%s</h2>',
  getColor($station['last_observation']),
  $SUBTITLE
);

if ($station) {
  // Get a list of photos for selected station
  $dir = sprintf('%s/stations/%s.dir/%s/photos/screen',
    $DATA_DIR,
    substr($stationParam, 0, 1),
    $stationParam
  );
  $files = getDirContents($dir);

  // Add photos to collection
  $photoCollection = new PhotoCollection($networkParam, $stationParam);
  foreach ($files as $file) {
    $photoModel = new Photo($file);
    $photoCollection->add($photoModel);
  }
  $photoCollection->sort();

  // Render HTML
  $view = new PhotoView($photoCollection);
  $view->render();
} else {
  print '<p class="alert error">ERROR: Station Not Found</p>';
}
