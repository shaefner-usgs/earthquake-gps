<?php

include '../lib/_functions.inc.php'; // app functions
include '../lib/classes/Waypoints.class.php'; // Waypoints class

$network = safeParam('network', 'Pacific');

$gpx = new Waypoints($network);

$gpx->setPhpHeaders();
$gpx->render();
