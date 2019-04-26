<?php

/* TODO

  1. nothing happens if layer is off and you click popup icon on button list

*/

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

// set default value so page loads without passing params
$networkParam = safeParam('network', 'Alaska');

if (!isset($TEMPLATE)) {
  $TITLE = $networkParam . ' Network';
  $NAVIGATION = true;
  $HEAD = '
    <link rel="stylesheet" href="/lib/leaflet-0.7.7/leaflet.css" />
    <link rel="stylesheet" href="css/network.css" />
  ';
  $FOOT = '
    <script>
      var MOUNT_PATH = "' . $MOUNT_PATH . '",
          NETWORK = "' . $networkParam . '";
    </script>
    <script src="/lib/leaflet-0.7.7/leaflet.js"></script>
    <script src="js/network.js"></script>
  ';
  $CONTACT = 'jsvarc';

  // importJsonToArray() sets headers -> needs to run before including template
  $stations = importJsonToArray(__DIR__ . '/_getStations.json.php', $networkParam);

  include 'template.inc.php';
}

$db = new Db();

// Db query result: network details for selected network
$rsNetwork = $db->queryNetwork($networkParam);
$row = $rsNetwork->fetch(PDO::FETCH_OBJ);

// Check to see if this is a valid network
if ($rsNetwork->rowCount() === 0) {
  print '<p class="alert info">Network Not Found</p>';
}
else if ($stations['count'] === 0) {
  print '<p class="alert info">No Stations Found</p>';
} else { // Begin: valid network block

// Create HTML for legend
$legendIcons = [
  'triangle+grey' => 'Campaign',
  'square+grey' => 'Continuous',
  'blue' => 'Past 3 days',
  'yellow' => '4&ndash;7 days ago',
  'orange' => '8&ndash;14 days ago',
  'red' => 'Over 14 days ago'
];

$legendHtml = '<ul class="legend no-style">';
foreach ($legendIcons as $key => $description) {
  $legendHtml .= sprintf('<li>
      <img src="img/pin-s-%s-2x.png" alt="%s icon" /><span>%s</span>
    </li>',
    $key,
    $key,
    $description
  );
}
$legendHtml .= '</ul>';

// Create HTML for station list
$height = ceil($stations['count'] / 8) * 36;
$starred = false;

$stationsHtml = '<ul class="stations no-style" style="height: '. $height . 'px;">';
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
  $stationsHtml .= sprintf('<li>
      <a href="%s/%s" class="link%d %s button" title="Go to station details">%s%s</a>
    </li>',
    $networkParam,
    $feature['properties']['station'],
    $feature['id'],
    getColor($feature['properties']['last_observation']),
    strtoupper($feature['properties']['station']),
    $star
  );
}
$stationsHtml .= '</ul>';

// Create HTML for Download links
$downloads = [
  'GPS Waypoints' => ['gpx', "$networkParam/waypoints"],
  'Most Recent XYZ Positions' => ['text', "networks/$networkParam/${networkParam}_xyz_file"],
  'ITRF2008 XYZ Time Series' => ['zip', "networks/$networkParam/${networkParam}_ITRF2008_xyz_files.tar.gz"]
];

$downloadsHtml = '<ul class="downloads no-style">';
$kmlFileBaseUri = $networkParam . '/kml';
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
$downloadsHtml .= $kmlFiles;

foreach ($downloads as $name => $file) {
  $type = $file[0];
  $path = $file[1];
  $fullPath = $DATA_DIR . '/' . $path;
  if (file_exists($fullPath) || $type === 'gpx') {
    $downloadsHtml .= sprintf('<li><a href="data/%s" class="%s">%s</a></li>',
      $file[1],
      $file[0],
      $name
    );
  }
}

$downloadsHtml .= '</ul>';

?>

<h2 class="subtitle">Station Map</h2>

<nav>
  <ul class="pipelist no-style">
    <li><strong>Station Map</strong></li>
    <li>
      <a href="<?php print $networkParam; ?>/velocities">Velocities and Uncertainties</a>
    </li>
    <li>
      <a href="<?php print $networkParam; ?>/offsets">Offsets</a>
    </li>
    <li>
      <a href="<?php print $networkParam; ?>/notupdated">Stations Not Updated in the Past 7 Days</a>
    </li>
  </ul>
</nav>

<section>
  <div class="map"></div>
  <?php print $legendHtml; ?>
  <small>Pin color indicates when station was last updated.</small>
  <h3 class="count"><?php print $stations['count']; ?> Stations on this Map</h3>
  <?php
    print $stationsHtml;
    if ($starred) {
      print '<p>* = high RMS values</p>';
    }
  ?>
</section>

<section>
  <h2>Downloads</h2>
  <?php print $downloadsHtml; ?>
</section>

<?php } // End: valid network block ?>

<p class="back">&laquo;
  <a href="<?php print $MOUNT_PATH; ?>">Back to All Networks</a>
</p>
