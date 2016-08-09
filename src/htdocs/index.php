<?php

/*
Table optimization:
1. velocities: add component field, then have only 1 field for velocity, sigma, etc
2. network: name field -> network
3. velocities / offsets: use same values for type fields; also name both fields the same
4. timeseries table: rename vertical field: up
*/

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions

if (!isset($TEMPLATE)) {
  $TITLE = 'GPS Data';
  $NAVIGATION = true;
  $HEAD = '
    <link rel="stylesheet" href="/lib/leaflet-0.7.7/leaflet.css" />
    <link rel="stylesheet" href="gps/css/index.css" />
  ';
  $FOOT = '
    <script>var MOUNT_PATH = "' . $MOUNT_PATH . '";</script>
    <script src="/lib/leaflet-0.7.7/leaflet.js"></script>
    <script src="gps/js/index.js"></script>
  ';
  $CONTACT = 'jsvarc';

  // importJsonToArray() sets headers -> needs to run before including template
  $networks = importJsonToArray(__DIR__ . '/_getNetworks.json.php');

  include 'template.inc.php';
}

// Create HTML for legend
$legend_icons = [
  'triangle+grey' => 'Campaign',
  'square+grey' => 'Continuous'
];

$legend_html = '<ul class="legend no-style">';
foreach ($legend_icons as $key => $description) {
  $legend_html .= sprintf('<li>
      <img src="gps/img/pin-s-%s-2x.png" alt="%s icon" /><span>%s</span>
    </li>',
    $key,
    $key,
    $description
  );
}
$legend_html .= '</ul>';

// Create HTML for network list
$height = ceil($networks['count'] / 4) * 32;
$networks_html = '<ul class="networks no-style" style="height: '. $height . 'px;">';

foreach ($networks['features'] as $feature) {
  if ($feature['geometry']['type'] === 'Point') { // skip polygons
    $networks_html .= sprintf('<li>
        <a href="gps/%s" title="Go to map of stations" class="%s">%s</a>
      </li>',
      $feature['properties']['name'],
      str_replace('point', 'link', $feature['id']),
      $feature['properties']['name']
    );
  }
}

$networks_html .= '</ul>';

?>

<p>As part of the earthquake process, Earth&rsquo;s surface is being deformed as
  earthquake faults accumulate strain and slip or slowly creep over time. We
  use GPS to monitor this movement by measuring the precise position (within
  5mm or less) of stations near active faults relative to each other. Months
  or years later, we occupy the same stations again. By determining how the
  stations have moved we calculate ground deformation.
  <a href="gps/about.php">Read more</a> &raquo;</p>

<section>
  <h2>View Stations by Network</h2>
  <div class="map"></div>
  <?php print $legend_html; ?>
  <h3 class="count"><?php print $networks['count']; ?> networks on this map</h3>
  <?php print $networks_html; ?>
</section>

<section>
  <h2>View Stations Alphabetically</h2>
  <p><a href="gps/stations">Station List</a></p>
</section>

<section>
  <h2>Google Earth Files</h2>
  <ul>
    <li><a href="gps/kml/years">Campaign stations sorted by year(s) surveyed</a></li>
    <li><a href="gps/kml/last">Campaign stations sorted by last year surveyed</a></li>
    <li><a href="gps/kml/timespan">Campaign stations sorted by timespan between surveys</a></li>
  </ul>
</section>

<h2>Cooperators</h2>

<p>The USGS Earthquake Hazards Program (EHP) supports GPS data collection
  throughout the western U.S. through cooperative agreements with Central
  Washington University, University of California at Berkeley, University of
  Memphis, and University of Nevada at Reno.  We process GPS data that we
  collect, plus data from the USGS Volcano Hazards Program (VHP), our cooperator
  institutions, UNAVCO Inc., and other network operators.  These results are
  available at this website as time series of daily GPS positions.</p>
