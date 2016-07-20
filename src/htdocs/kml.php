<?php

include_once '../lib/_functions.inc.php'; // app functions

include_once '../lib/classes/Kml.class.php'; // Kml generator

$network = safeParam('network', 'Pacific');
$sortKey = safeParam('sortKey'); // 'last' or 'total'

$sort = [
  'last' => 'last_obs',
  'total' => 'total_years'
];

$kml = new Kml($network);

// KML file is sorted by station by default
$sortby = $sort[$sortKey];
if ($sortby) {
  $kml->sort($sortby);
}

$kml->setPhpHeaders();
$kml->render();
