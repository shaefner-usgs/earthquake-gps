<?php

include_once '../lib/_functions.inc.php'; // app functions

include_once '../lib/classes/Waypoints.class.php'; // Waypoints (.gpx) generator

$network = safeParam('network', 'Pacific');

$gpx = new Waypoints($network);

$gpx->setPhpHeaders();
$gpx->render();
