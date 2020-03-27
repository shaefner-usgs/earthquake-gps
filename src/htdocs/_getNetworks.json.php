<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$callbackParam = safeParam('callback');

$now = date(DATE_RFC2822);

$db = new Db;

// Db query: all "non-hidden" networks
$rsNetworks = $db->queryNetworks();

// Initialize array template for json feed
$output = [
  'generated' => $now,
  'count' => $rsNetworks->rowCount(),
  'type' => 'FeatureCollection',
  'features' => []
];

// Store results from db into features array
while ($row = $rsNetworks->fetch(PDO::FETCH_ASSOC)) {
  // Points
  $feature = [
    'geometry' => [
      'coordinates' => [
        floatval($row['lon']),
        floatval($row['lat'])
      ],
      'type' => 'Point'
    ],
    'id' => 'point' . intval($row['id']),
    'properties' => [
      'name' => $row['network'],
      'type' => $row['type']
    ],
    'type' => 'Feature'
  ];
  array_push($output['features'], $feature);

  // Polygons
  if ($row['polygon']) { // not all networks necessarily have a polygon defined
    $poly = array(
      'geometry' => array(
        'coordinates' => array(json_decode($row['polygon'], true)),
        'type' => 'Polygon'
      ),
      'id' => 'poly' . intval($row['id']),
      'properties' => [],
      'type' => 'Feature'
    );
    array_push($output['features'], $poly);
  }
}

// Send json stream to browser
showJson($output, $callbackParam);
