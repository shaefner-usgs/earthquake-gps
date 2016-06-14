<?php

/* TODO

1. media queries:
  - station page: content R of plots
  - map heights
2. pipelist "broken" when wrapping
3. add links to network pages
4. QC, kinematic pages
5. nothing happens if layer off and you click popup icon
6. legend / layer list organization (Stations label)
7. mouseovers clipped at edge of page

Ask Jerry

1. showcoords = 0 (none flagged; do we need? If so, do what?)
2. don't send empty plots...handle programmatically

dynamic GE files??

*/

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions

if (!isset($TEMPLATE)) {
  $TITLE = 'GPS Data';
  $NAVIGATION = true;
  $HEAD = '
    <link rel="stylesheet" href="/lib/leaflet-0.7.7/leaflet.css" />
    <link rel="stylesheet" href="css/index.css" />
  ';
  $FOOT = '
    <script src="/lib/leaflet-0.7.7/leaflet.js"></script>
    <script>var MOUNT_PATH = "' . $MOUNT_PATH . '";</script>
    <script src="js/index.js"></script>
  ';
  $CONTACT = 'jsvarc';

  // importJsonToArray() sets headers -> needs to run before including template
  $networks = importJsonToArray(__DIR__ . '/_getNetworks.json.php');

  include 'template.inc.php';
}

// Create HTML for network list
$height = ceil($networks['count'] / 4) * 32;
$networks_html = '<ul class="networks no-style" style="height: '. $height . 'px;">';
foreach ($networks['features'] as $feature) {
  if ($feature['geometry']['type'] === 'Point') { // skip polygons
    $networks_html .= sprintf('<li>
        <a href="./%s/" title="Go to map of stations" class="%s">%s</a>
      </li>',
      $feature['properties']['name'],
      str_replace('point', 'link', $feature['id']),
      $feature['properties']['name']
    );
  }
}
$networks_html .= '</ul>';

?>

<p>As part of the earthquake process, Earth's surface is being deformed as earthquake faults accumulate strain and slip or slowly creep over time. We use GPS to monitor this movement by measuring the precise position (within 5mm or less) of stations near active faults relative to each other. Months or years later, we occupy the same stations again. By determining how the stations have moved we calculate ground deformation. <a href="about.php">Read more</a> &raquo;</p>

<section>
  <h2>View Stations by Network</h2>
  <div class="map"></div>
  <h3 class="count"><?php print $networks['count']; ?> networks on this map</h3>
  <?php print $networks_html; ?>
</section>

<section>
  <h2>View Stations Alphabetically</h2>
  <p><a href="stations/">Station List</a></p>
</section>

<section>
  <h2>Google Earth Files</h2>
  <p>All stations surveyed since 1992:</p>
  <ul>
    <li><a href="data/networks/USGS_years_observed.kmz">Sorted by number of years occupied</a></li>
    <li><a href="data/networks/USGS_campaign_data.kmz">Sorted by last year occupied</a></li>
  </ul>
</section>
