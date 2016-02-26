<?php

include '../lib/classes/Db.class.php'; // db connector, queries
include '../lib/functions/functions.inc.php'; // app functions

date_default_timezone_set('UTC');

$callback = safeParam('callback');
$days = safeParam('days', 30);
$mag = safeParam('mag', 2.5);

$now = date(DATE_RFC2822);

$db = new Db;

// Db query result: real-time earthquakes, mag>=$mag, past $days days
$rsEarthquakes = $db->queryEarthquakes($mag, $days);

// Initialize array template for json feed
$output = array(
  'count' => $rsEarthquakes->rowCount(),
  'generated' => $now,
  'type' => 'FeatureCollection',
  'features' => []
);

// Store results from db into features array
while ($row = $rsEarthquakes->fetch(PDO::FETCH_ASSOC)) {
  $timestamp = strtotime($row['datetime (GMT)']);

  $feature = [
    'id' => $row['src'] . $row['eqid'],
    'geometry' => [
      'coordinates' => [
        floatval($row['lon']),
        floatval($row['lat'])
      ],
      'type' => 'Point'
    ],
    'properties' => [
      'age' => getAge($timestamp),
      'datetime' => date('D, M j H:i:s', $timestamp) . ' UTC',
      'depth' => number_format($row['depth'], 1),
      'dyfi' => roman(intval(round($row['dyfi_maxcdi']))),
      'dyfi_responses' => $row['dyfi_responses'],
      'mag' => number_format($row['mag'], 1),
      'place' => $row['place'],
      'pager' => $row['pager_alertlevel'],
      //'shakemap' => roman(round($row['shakemap_maxmmi'])),
      'timestamp' => $timestamp,
      'tsunami' => $row['tsunami']
    ],
    'type' => 'Feature'
  ];

  array_push($output['features'], $feature);

}

// Send json stream to browser
showJson($output, $callback);

/**
 * Get eq's age classification
 *
 * @param $timestamp {Int}
 *        Unix timestamp of eq
 */
function getAge($timestamp) {
  $secs_ago = time() - $timestamp; // how many secs ago eq occurred
  if ($secs_ago <= 60 * 60) {
    $age = 'pasthour';
  } else if ($secs_ago > 60 * 60 && $secs_ago <= 60 * 60 * 24) {
    $age = 'pastday';
  } else if ($secs_ago > 60 * 60 * 24 && $secs_ago <= 60 * 60 * 24 * 7) {
    $age = 'pastweek';
  } else {
    $age = 'pastmonth';
  }

  return $age;
}

/**
 * Convert integer to Roman Numeral
 *
 * @param $N {Int}
 */
 function roman($N) {

   var_dump($N);

   $c='IVXLCDM';
   for($a=5,$b=$s='';$N;$b++,$a^=7)
     for($o=$N%$a,$N=$N/$a^0;$o--;$s=$c[$o>2?$b+$N-($N&=-2)+$o=1:$b].$s);

   return $s;
 }
