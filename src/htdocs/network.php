<?php

/* TODO:

    1 add links above Map
    2 add mouseover labels for list below map
    3 add alert for network not found
    4 add legend
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
    <link rel="stylesheet" href="' . $MOUNT_PATH . '/css/network.css" />
  ';
  $FOOT = '
    <script>var NETWORK = "' . $network . '";</script>
    <script>var MOUNT_PATH = "' . $MOUNT_PATH . '";</script>
    <script src="/lib/leaflet-0.7.7/leaflet.js"></script>
    <script src="' . $MOUNT_PATH . '/js/network.js"></script>
  ';
  $CONTACT = 'jsvarc';

  // importJsonToArray() sets headers -> needs to run before including template
  $stations = importJsonToArray(__DIR__ .
    "/_getStations.json.php", $network);

  include 'template.inc.php';
}

// Create HTML for link list
$links = [
  'GPS waypoints' => "data/networks/$network/${network}.gpx",
  'Most recent XYZ positions' => "data/networks/$network/${network}_xyz_file",
  'ITRF2008 XYZ time series' => "data/networks/$network/${network}_xyz_files.tar.gz",
  'Stations not updated in the past 7 days' => "$network/notupdated/"
];

$links_html = '<ul class="pipelist no-style">';
foreach($links as $name => $link) {
  $links_html .= sprintf('<li><a href="%s/%s">%s</a></li>',
    $MOUNT_PATH,
    $link,
    $name
  );
}
$links_html .= '</ul>';

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
      <a href="%s/%s/%s/" title="Go to station details">%s%s</a>
    </li>',
    getColor($feature['properties']['days']),
    $MOUNT_PATH,
    $network,
    $feature['properties']['station'],
    strtoupper($feature['properties']['station']),
    $star
  );
}

$stations_html .= '</ul>';

$kmlLastYear = "$MOUNT_PATH/{$network}_lastyear.kmz";
$kmlNumYears = "$MOUNT_PATH/{$network}_nobs.kmz";

?>

<?php print $links_html; ?>

<section>
  <div class="map"></div>
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
      <a href="<?php print $kmlNumYears; ?>">Sorted by number of years occupied</a>
    </li>
    <li>
      <a href="<?php print $kmlLastYear; ?>">Sorted by last year occupied</a>
    </li>
  </ul>
</section>

<p class="back">&laquo;
  <a href="<?php print $MOUNT_PATH; ?>/">Back to all networks</a>
</p>
