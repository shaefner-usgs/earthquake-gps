<?php

/* TODO

  1. nothing happens if layer is off and you click popup icon on button list

*/

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions

// set default value so page loads without passing params
$network = safeParam('network', 'Alaska');

if (!isset($TEMPLATE)) {
  $TITLE = $network . ' Network';
  $NAVIGATION = true;
  $HEAD = '
    <link rel="stylesheet" href="/lib/leaflet-0.7.7/leaflet.css" />
    <link rel="stylesheet" href="css/network.css" />
  ';
  $FOOT = '
    <script>
      var MOUNT_PATH = "' . $MOUNT_PATH . '",
          NETWORK = "' . $network . '";
    </script>
    <script src="lib/leaflet-0.7.7/leaflet.js"></script>
    <script src="js/network.js"></script>
  ';
  $CONTACT = 'jsvarc';

  // importJsonToArray() sets headers -> needs to run before including template
  $stations = importJsonToArray(__DIR__ .
    "/_getStations.json.php", $network);

  include 'template.inc.php';
}

// Check to see if this is a valid network
if ($stations['count'] === 0) {
  print '<p class="alert info">Network Not Found</p>';
} else { // Begin: valid network block

// Create HTML for link list
$links = [
  'GPS waypoints' => "$network/waypoints",
  'Velocities' => "$network/velocities",
  'Most recent XYZ positions' => "data/networks/$network/${network}_xyz_file",
  'ITRF2008 XYZ time series' => "data/networks/$network/${network}_xyz_files.tar.gz",
  'Stations not updated in the past 7 days' => "$network/notupdated"
];

$links_html = '<ul class="pipelist no-style">';
foreach($links as $name => $link) {
  $links_html .= sprintf('<li><a href="%s">%s</a></li>',
    $link,
    $name
  );
}
$links_html .= '</ul>';

// Create HTML for legend
$legend_icons = [
  'triangle+grey' => 'Campaign',
  'square+grey' => 'Continuous',
  'blue' => 'Past 3 days',
  'yellow' => '4&ndash;7 days ago',
  'orange' => '8&ndash;14 days ago',
  'red' => 'Over 14 days ago'
];

$legend_html = '<ul class="legend no-style">';
foreach ($legend_icons as $key => $description) {
  $legend_html .= sprintf('<li>
      <img src="img/pin-s-%s-2x.png" alt="%s icon" /><span>%s</span>
    </li>',
    $key,
    $key,
    $description
  );
}
$legend_html .= '</ul>';

// Create HTML for station list
$height = ceil($stations['count'] / 6) * 32;
$starred = false;

$stations_html = '<ul class="stations no-style" style="height: '. $height . 'px;">';
foreach ($stations['features'] as $feature) {
  // star high rms values
  $star = '';
  if ($feature['properties']['rms']['up'] > 15 ||
    $feature['properties']['rms']['north'] > 10 ||
    $feature['properties']['rms']['east'] > 10) {
      $star = '<span>*</span>';
      $starred = true;
  }
  $stations_html .= sprintf('<li class="%s">
      <a href="%s/%s" title="Go to station details">%s%s</a>
    </li>',
    getColor($feature['properties']['days']),
    $network,
    $feature['properties']['station'],
    strtoupper($feature['properties']['station']),
    $star
  );
}
$stations_html .= '</ul>';

?>

<section>
  <?php print $links_html; ?>
</section>

<section>
  <div class="map"></div>
  <?php print $legend_html; ?>
  <h3 class="count"><?php print $stations['count']; ?> stations on this map</h3>
  <?php
    print $stations_html;
    if ($starred) {
      print '<p>* = high RMS values</p>';
    }
  ?>
</section>

<section>
  <h2>Google Earth Files</h2>
  <ul>
    <li>
      <a href="<?php print $network; ?>/kml/total">Sorted by total years occupied</a>
    </li>
    <li>
      <a href="<?php print $network; ?>/kml/last">Sorted by last year occupied</a>
    </li>
  </ul>
</section>

<?php } // End: valid network block ?>

<p class="back">&laquo;
  <a href="<?php print $MOUNT_PATH; ?>">Back to all networks</a>
</p>
