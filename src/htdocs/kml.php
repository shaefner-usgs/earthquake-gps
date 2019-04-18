<?php

include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Kml.class.php'; // Kml generator

$networkParam = safeParam('network');
$sortByParam = safeParam('sortBy'); // 'last', 'timespan', or 'years'

if ($networkParam) {
  $kml = new Kml($networkParam);
} else {
  $kml = new Kml();
}

// KML file is sorted by station by default
if ($sortByParam) {
  $kml->sort($sortByParam);
}

$kml->setHeaders();
//$kml->renderStationsArray();
$kml->render();
