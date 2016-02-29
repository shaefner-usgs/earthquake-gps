<?php

if (!isset($TEMPLATE)) {
  $TITLE = 'GPS Data';
  $NAVIGATION = true;
  $HEAD = '';
  $FOOT = '';

  include '../conf/config.inc.php'; // app config
  include '../lib/functions/functions.inc.php'; // app functions

  // importJsonToArray() sets headers -> needs to run before template included
  $networks = importJsonToArray(__DIR__ . '/_getNetworks.json.php');

  include 'template.inc.php';
}

// Create HTML for network list
$networks_html = '<ul>';
foreach ($networks['features'] as $feature) {
  $networks_html .= sprintf('<li>
    <a href="./%s/" title="Go to map of stations" class="feature%d">%s</a>',
    $feature['properties']['name'],
    $feature['id'],
    $feature['properties']['name']
  );
}
$networks_html .= '</ul>';

?>

<p>As part of the earthquake process, Earth's surface is being deformed as earthquake faults accumulate strain and slip or slowly creep over time. We use GPS to monitor this movement by measuring the precise position (within 5mm or less) of stations near active faults relative to each other. Months or years later, we occupy the same stations again. By determining how the stations have moved we calculate ground deformation. <a href="about.php">Read more</a> &raquo;</p>

<section>
  <h2>View Stations by Network</h2>
  <div class="map"></div>
  <p class="count"><?php print $networks['count']; ?> Networks on this map</p>
  <?php print $networks_html; ?>
</section>

<section>
  <h2>View Stations Alphabetically</h2>
  <p><a href="stationlist/">Station List</a></p>
</section>

<section>
  <h2>Google Earth Files</h2>
  <p>All stations surveyed since 1992:</p>
  <ul>
    <li><a href="data/networks/USGS_years_observed.kmz">Sorted by number of years occupied</a></li>
    <li><a href="data/networks/USGS_campaign_data.kmz">Sorted by last year occupied</a></li>
  </ul>
</section>
