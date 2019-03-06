<?php

/* TODO

  1. nothing happens if layer is off and you click popup icon on button list

*/

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

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
    <script src="/lib/leaflet-0.7.7/leaflet.js"></script>
    <script src="js/network.js"></script>
  ';
  $CONTACT = 'jsvarc';

  // importJsonToArray() sets headers -> needs to run before including template
  $stations = importJsonToArray(__DIR__ . '/_getStations.json.php', $network);

  include 'template.inc.php';
}

$db = new Db();

// Db query result: network details for selected network
$rsNetwork = $db->queryNetwork($network);
$row = $rsNetwork->fetch(PDO::FETCH_OBJ);

// Check to see if this is a valid network
if ($stations['count'] === 0) {
  print '<p class="alert info">Network Not Found</p>';
} else { // Begin: valid network block

// Create HTML for link list
$links = [
  'Velocities and Uncertainties' => "$network/velocities",
  'Offsets' => "$network/offsets",
  'Stations Not Updated in the Past 7 Days' => "$network/notupdated",
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
$height = ceil($stations['count'] / 8) * 36;
$starred = false;

$stations_html = '<ul class="stations no-style" style="height: '. $height . 'px;">';
foreach ($stations['features'] as $feature) {
  // star high rms values
  $star = '';
  /* 2017-03-13: Comment out for now b/c rms values no longer in db
  if ($feature['properties']['rms']['up'] > 15 ||
    $feature['properties']['rms']['north'] > 10 ||
    $feature['properties']['rms']['east'] > 10) {
      $star = '<span>*</span>';
      $starred = true;
  }*/
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

// Create HTML for Download links
$downloads = [
  'GPS Waypoints' => ['gpx', "$network/waypoints"],
  'Most Recent XYZ Positions' => ['text', "data/networks/$network/${network}_xyz_file"],
  'ITRF2008 XYZ Time Series' => ['zip', "data/networks/$network/${network}_ITRF2008_xyz_files.tar.gz"]
];
$kmlFileBaseUri = $network . '/kml';

$downloads_html = '<ul class="downloads no-style">';
if ($row->type === 'campaign') {
  $kmlFiles = '<li>
      <a href="' . $kmlFileBaseUri . '/years" class="kml">Campaign Stations Sorted by Year(s) Surveyed</a>
    </li>';
  $kmlFiles .= '<li>
      <a href="' . $kmlFileBaseUri . '/last" class="kml">Campaign Stations Sorted by Last Year Surveyed</a>
    </li>';
  $kmlFiles .= '<li>
      <a href="' . $kmlFileBaseUri . '/timespan" class="kml">Campaign Stations Sorted by Timespan Between Surveys</a>
    </li>';
} else { // continuous network
  $kmlFiles = '<li>
      <a href="' . $kmlFileBaseUri . '" class="kml">Stations Sorted by Station Name</a>
    </li>';
}
$downloads_html .= $kmlFiles;
foreach ($downloads as $name=>$file) {
  $downloads_html .= sprintf('<li><a href="%s" class="%s">%s</a></li>',
    $file[1],
    $file[0],
    $name
  );
}
$downloads_html .= '</ul>';

?>

<section>
  <?php print $links_html; ?>
</section>

<section>
  <div class="map"></div>
  <?php print $legend_html; ?>
  <p class="small">Pin color indicates when station was last updated.</p>
  <h3 class="count"><?php print $stations['count']; ?> Stations on this Map</h3>
  <?php
    print $stations_html;
    if ($starred) {
      print '<p>* = high RMS values</p>';
    }
  ?>
</section>

<section>
  <h2>Downloads</h2>
  <?php print $downloads_html; ?>
</section>

<?php } // End: valid network block ?>

<p class="back">&laquo;
  <a href="<?php print $MOUNT_PATH; ?>">Back to All Networks</a>
</p>
