<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions

$network = safeParam('network', 'Pacific');
$stations = importJsonToArray(__DIR__ . "/_getStations.json.php", $network);
$timestamp = date('Y-m-d\TH:i:s\Z');

$header = '<?xml version="1.0" encoding="UTF-8"?>
<gpx
  version="1.0"
  creator="USGS, Menlo Park - http://earthquake.usgs.gov/monitoring/gps/"
  xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
  xmlns="http://www.topografix.com/GPX/1/0"
  xsi:schemaLocation="http://www.topografix.com/GPX/1/0 http://www.topografix.com/GPX/1/0/gpx.xsd">';

$time = "<time>$timestamp</time>";

$body = '';
$lats = [];
$lons = [];
foreach ($stations['features'] as $feature) {
  $ele = number_format($feature['properties']['elevation'], 2, '.', '');
  $lat = number_format($feature['geometry']['coordinates'][1], 5, '.', '');
  $lon = number_format($feature['geometry']['coordinates'][0], 5, '.', '');
  $station = strtoupper($feature['properties']['station']);
  $wpt = '  <wpt lat="' . $lat . '" lon="' . $lon . '">
    <ele>' . $ele . '</ele>
    <name>' . $station . '</name>
    <cmt>Position created from information contained in USGS GPS database</cmt>
    <desc>Campaign station ' . $station . ' waypoint</desc>
    <sym>Triangle, Red</sym>
  </wpt>';
  $body .= "\n$wpt";

  array_push($lats, $lat);
  array_push($lons, $lon);
}

$bounds = sprintf('<bounds minlat="%F" minlon="%F" maxlat="%F" maxlon="%F"/>',
  min($lats),
  min($lons),
  max($lats),
  max($lons)
);

$footer = "</gpx>";

$expires = date(DATE_RFC2822);
header('Cache-control: no-cache, must-revalidate');
header("Expires: $expires");
header('Content-Type: application/xml');
header('Content-Disposition: attachment; filename="' . $network . '.gpx"');

print "$header\n";
print "$time\n";
print $bounds;
print "$body\n";
print $footer;
