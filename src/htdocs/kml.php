<?php

include '../lib/_functions.inc.php'; // app functions
include '../lib/classes/Kml.class.php'; // Waypoints class

$network = safeParam('network', 'Pacific');

$kml = new Kml($network);

//$kml->setPhpHeaders();
$kml->render();
