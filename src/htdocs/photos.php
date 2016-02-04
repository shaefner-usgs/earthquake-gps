<?php

include_once '../lib/functions/functions.inc.php'; // app functions

// set default value so page loads without passing params
$station = safeParam('station', '7adl');

if (!isset($TEMPLATE)) {
  include '../lib/classes/Db.class.php'; // db connector, queries
  include '../lib/classes/Photos.class.php'; // model
  include '../lib/classes/PhotosView.class.php'; // view

  $TITLE = 'GPS Station ' . strtoupper($station) . ' Photos';
  $HEAD = '';
  $FOOT = '';

  include_once 'template.inc.php';
}

$db = new Db();

// Db query result: station details for selected station
$rsStation = $db->queryStation($station);
$station_exists = $rsStation->fetch();

if ($station_exists) {
  $photosModel = new Photos($station);
  $view = new PhotosView($photosModel);
  $view->render();
} else {
  print '<p class="alert error">ERROR: Station Not Found.';
}
