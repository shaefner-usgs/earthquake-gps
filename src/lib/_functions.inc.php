<?php

/**
 * Get color classification based on the number of days since the last update
 *
 * @param $days {Integer}
 *
 * @return $color {String}
 */
function getColor ($date) {
  $now = date(DATE_RFC2822);
  $secs = 86400; // seconds in a day
  $days = floor((strtotime($now) - strtotime($date)) / $secs);

  if ($days > 14) {
    $color = 'red';
  } else if ($days > 7) {
    $color = 'orange';
  } else if ($days > 3) {
    $color = 'yellow';
  } else if ($days >= 0) {
    $color = 'blue';
  } else {
    $color = 'grey';
  }

  return $color;
}

/**
 * Create GeoJson for Leaflet map overlay layers
 *
 * @param $data {Array}
 *   [
 *     count: {Integer}
 *     features: [
 *       coords: {Array}
 *       id: {String}
 *       props: {Array}
 *       type: {Point|Polygon}
 *     ]
 *   ]
 *
 * @return {String}
 */
function getGeoJson ($data) {
  $now = date(DATE_RFC2822);

  // Initialize array template for json feed
  $geoJson = [
    'generated' => $now,
    'count' => $data['count'],
    'type' => 'FeatureCollection',
    'features' => []
  ];

  // Add features
  foreach ($data['features'] as $feature) {
    $geoJsonFeature = [
      'geometry' => [
        'coordinates' => $feature['coords'],
        'type' => $feature['type']
      ],
      'id' => $feature['id'],
      'properties' => $feature['props'],
      'type' => 'Feature'
    ];
    array_push($geoJson['features'], $geoJsonFeature);
  }

  return json_encode($geoJson) . "\n";
}

/**
 * Get directory contents (checks first if it exists and doesn't return .., .)
 *
 * @param $dir {String}
 *     directory to scan
 * @param $order {Integer} default is SCANDIR_SORT_DESCENDING
 *
 * @return $r {Array}
 */
function getDirContents ($dir, $order=SCANDIR_SORT_DESCENDING) {
  $r = [];

  if (is_dir($dir)) {
    $contents = scandir($dir, $order);
    $r = array_diff($contents, array('..', '.'));
  }

  return $r;
}

/**
 * Get a request parameter from $_GET or $_POST
 *
 * @param $name {String}
 *     The parameter name
 * @param $default {?} default is NULL
 *     Optional default value if the parameter was not provided.
 * @param $filter {PHP Sanitize filter} default is FILTER_SANITIZE_STRING
 *     Optional sanitizing filter to apply
 *
 * @return $value {String}
 */
function safeParam ($name, $default=NULL, $filter=FILTER_SANITIZE_STRING) {
  $value = NULL;

  if (isset($_POST[$name]) && $_POST[$name] !== '') {
    $value = filter_input(INPUT_POST, $name, $filter);
  } else if (isset($_GET[$name]) && $_GET[$name] !== '') {
    $value = filter_input(INPUT_GET, $name, $filter);
  } else {
    $value = $default;
  }

  return $value;
}

/**
 * Convert an array to a json feed and print it
 *
 * @param $array {Array}
 *     Data from db
 * @param $callback {String} default is NULL
 *     optional callback for jsonp requests
 */
function showJson ($array, $callback=NULL) {
  header('Content-Type: application/json');
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: *');
  header('Access-Control-Allow-Headers: accept,origin,authorization,content-type');

  $json = json_encode($array);
  if ($callback) {
    $json = "$callback($json)";
  }
  print $json;
}
