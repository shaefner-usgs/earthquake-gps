<?php

include_once '../lib/_functions.inc.php'; // app functions

include_once '../lib/classes/Kml.class.php'; // Kml generator

$network = safeParam('network');
$sortBy = safeParam('sortBy'); // 'last', 'timespan', or 'year'

if ($network) {
  $kml = new Kml($network);
} else {
  $kml = new Kml();
}

// KML file is sorted by station by default
if ($sortBy) {
  $kml->sort($sortBy);
}

$kml->setPhpHeaders();
//$kml->renderStationsArray();
$kml->render();
