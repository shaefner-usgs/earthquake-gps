<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$callbackParam = safeParam('callback');

/* This script is called via js (as an ajax request) or php (using
 * importJsonToArray(), which is declared in _functions.inc.php).
 *
 * - js mode: $networkParam is set via querystring
 * - php mode: $networkParam is set before including this script
 */
if (!isset($networkParam)) {
  $networkParam = safeParam('network', 'Pacific');
}

$now = date(DATE_RFC2822);

$db = new Db;

// Db query result: all stations in a given network
$rsStations = $db->queryStations($networkParam);

// Initialize array template for json feed
$output = [
  'count' => $rsStations->rowCount(),
  'generated' => $now,
  'features' => [],
  'network' => $networkParam,
  'type' => 'FeatureCollection'
];

// Store results from db into features array
while ($row = $rsStations->fetch(PDO::FETCH_ASSOC)) {
  $secs = 86400; // secs in one day
  $days = floor((strtotime($now) - strtotime($row['last_observation'])) / $secs);

  $feature = [
    'id' => intval($row['id']),
    'geometry' => [
      'coordinates' => [
        floatval($row['lon']),
        floatval($row['lat'])
      ],
      'type' => 'Point'
    ],
    'properties' => [
      'days' => $days,
      'elevation' => $row['elevation'],
      'last_observation' => $row['last_observation'],
      'showcoords' => intval($row['showcoords']),
      'station' => $row['station'],
      'type' => $row['stationtype'],
      'x' => $row['x'],
      'y' => $row['y'],
      'z' => $row['z']
    ],
    'type' => 'Feature'
  ];

  array_push($output['features'], $feature);
}

// Send json stream to browser
showJson($output, $callbackParam);
