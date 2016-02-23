<?php

include '../lib/classes/Db.class.php'; // db connector, queries
include '../lib/functions/functions.inc.php'; // app functions

date_default_timezone_set('UTC');

$callback = safeParam('callback');
$network = safeParam('network', 'Pacific');
$now = date(DATE_RFC2822);

$db = new Db;

// Db query result: all stations in a given network
$rsStations = $db->queryStations($network);

// Initialize array template for json feed
$output = [
  'count' => $rsStations->rowCount(),
  'generated' => $now,
  'features' => [],
  'type' => 'FeatureCollection'
];

// Store results from db into features array
while ($row = $rsStations->fetch(PDO::FETCH_ASSOC)) {
  $secs = 86400; // secs in one day
  $days = ceil((strtotime($now) - strtotime($row['last_observation'])) / $secs);

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
      'color' => getColor($days),
      'days' => $days,
      'last_observation' => $row['last_observation'],
      'rms' => [
        'east' => floatval($row['east_rms']),
        'north' => floatval($row['north_rms']),
        'up' => floatval($row['up_rms'])
      ],
      'showcoords' => intval($row['showcoords']),
      'station' => $row['station'],
      'type' => $row['stationtype']
    ],
    'type' => 'Feature'
  ];

  array_push($output['features'], $feature);
}

// Send json stream to browser
showJson($output, $callback);

/**
 * Get color classification based on the number of days since the last update
 *
 * @param $days {Int}
 */
function getColor ($days) {
  if ($days > 14) {
    $color = 'red';
  } else if ($days >= 8) {
    $color = 'orange';
  } else if ($days >= 4) {
    $color = 'yellow';
  } else if ($days >= 0) {
    $color = 'blue';
  }
  return $color;
}
