<?php

include_once '../lib/functions/functions.inc.php'; // app functions

// set default value so page loads without passing params
$network = safeParam('network', 'Pacific');

if (!isset($TEMPLATE)) {
  $TITLE = $network . ' Network';
  $HEAD = '';
  $FOOT = '';

  include '../conf/config.inc.php'; // app config

  // importJsonToArray() sets headers -> needs to run before including template
  $stations = importJsonToArray(__DIR__ . '/_getStations.json.php');

  include 'template.inc.php';
}

// Create HTML for station list
$stations_html = '<ul>';
foreach ($stations['features'] as $feature) {
  $stations_html .= sprintf('<li class="%s">
      <a href="%s/%s/%s/" title="Go to station details">%s</a>
    </li>',
    $feature['properties']['color'],
    $MOUNT_PATH,
    $network,
    $feature['properties']['station'],
    strtoupper($feature['properties']['station'])
  );
}
$stations_html .= '</ul>';

?>

<section>
  <div class="map"></div>
  <p class="count"><?php print $stations['count']; ?> Stations on this map</p>
  <?php print $stations_html; ?>
  <p>* = high RMS values</p>
</section>

<section>
  <h2>Google Earth Files</h2>
  <ul>
    <li><a href="">Sorted by number of years occupied</a></li>
    <li><a href="">Sorted by last year occupied</a></li>
  </ul>
</section>
