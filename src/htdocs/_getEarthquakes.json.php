<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

date_default_timezone_set('UTC');

$callback = safeParam('callback');
$days = safeParam('days', 30);
$mag = safeParam('mag', 2.5);

$now = date(DATE_RFC2822);
$time = time();

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

  // Convert to roman numerals
  $dyfi = $row['dyfi_maxcdi'];
  if ($dyfi) {
    $dyfi = toNumeral(intval(round($dyfi)));
  }
  $shakemap = $row['shakemap_maxmmi'];
  if ($shakemap) {
    $shakemap = toNumeral(intval(round($shakemap)));
  }

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
      'depth' => round($row['depth'], 1),
      'dyfi' => $dyfi,
      'dyfi_responses' => intval($row['dyfi_responses']),
      'mag' => round($row['mag'], 1),
      'place' => $row['place'],
      'pager' => $row['pager_alertlevel'],
      'shakemap' => $shakemap,
      'timestamp' => $timestamp,
      'tsunami' => intval($row['tsunami'])
    ],
    'type' => 'Feature'
  ];

  array_push($output['features'], $feature);

}

// Send json stream to browser
showJson($output, $callback);

/**
 * Get eq's age classification - use global time so calculated age is consistent
 *
 * @param $timestamp {Int}
 *        Unix timestamp of eq
 */
function getAge($timestamp) {
  $secs_ago = $GLOBALS['time'] - $timestamp; // how many secs ago eq occurred
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
 * Convert integer to Roman Numeral (taken from PEAR library)
 *
 * @param $num {Int}
 *        An integer between 0 and 3999 to convert to roman numeral
 * @param $uppercase {Boolean}
 *        Uppercase output, deafult true
 *
 * @return $roman {String}
 */
function toNumeral($num, $uppercase = true) {
  $conv = array(10 => array('X', 'C', 'M'),
  5 => array('V', 'L', 'D'),
  1 => array('I', 'X', 'C'));
  $roman = '';

  if ($num < 0) {
    return '';
  }

  $num = (int) $num;

  $digit = (int) ($num / 1000);
  $num -= $digit * 1000;
  while ($digit > 0) {
    $roman .= 'M';
    $digit--;
  }

  for ($i = 2; $i >= 0; $i--) {
    $power = pow(10, $i);
    $digit = (int) ($num / $power);
    $num -= $digit * $power;

    if (($digit == 9) || ($digit == 4)) {
      $roman .= $conv[1][$i] . $conv[$digit+1][$i];
    } else {
      if ($digit >= 5) {
        $roman .= $conv[5][$i];
        $digit -= 5;
      }
      while ($digit > 0) {
        $roman .= $conv[1][$i];
        $digit--;
      }
    }
  }

  return $roman;
}
