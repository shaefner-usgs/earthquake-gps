<?php

include '../lib/_functions.inc.php'; // app functions
include '../lib/classes/Kml.class.php'; // Waypoints class

$network = safeParam('network', 'Pacific');
$sortKey = safeParam('sortKey'); // 'last' or 'total'

$sort = [
  'last' => 'last_obs',
  'total' => 'total_years'
];

$kml = new Kml($network);

$kml->sort($sort[$sortKey]);
$kml->setPhpHeaders();
$kml->render();
