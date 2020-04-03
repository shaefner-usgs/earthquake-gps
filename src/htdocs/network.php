<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

// Set default network so page loads without passing param
$networkParam = safeParam('network', 'Alaska');

if (!isset($TEMPLATE)) {
  $TITLE = $networkParam . ' Network';
  $NAVIGATION = true;
  $HEAD = '
    <link rel="stylesheet" href="lib/leaflet/leaflet.css" />
    <link rel="stylesheet" href="css/network.css" />
  ';
  $FOOT = '
    <script>
      var MOUNT_PATH = "' . $MOUNT_PATH . '",
          NETWORK = "' . $networkParam . '";
    </script>
    <script src="lib/leaflet/leaflet.js"></script>
    <script src="js/network.js"></script>
  ';
  $CONTACT = 'jsvarc';

  include 'template.inc.php';
}

$db = new Db();

// Db query: network details for selected network
$rsNetwork = $db->queryNetwork($networkParam);

// Db query: all stations in a given network
$rsStations = $db->queryStations($networkParam);

$count = $rsStations->rowCount();

// Check to see if this is a valid network
if ($rsNetwork->rowCount() === 0) {
  print '<p class="alert info">Network Not Found</p>';
}
else if ($count === 0) {
  print '<p class="alert info">No Stations Found</p>';
} else { // Begin: valid network block

$downloads = [
  'GPS Waypoints' => ['gpx', "$networkParam/waypoints"],
  'Most Recent XYZ Positions' => ['text', "networks/$networkParam/${networkParam}_xyz_file"],
  'ITRF2008 XYZ Time Series' => ['zip', "networks/$networkParam/${networkParam}_ITRF2008_xyz_files.tar.gz"]
];
$features = [];
$height = ceil($count / 8) * 36;
$legendIcons = [
  'triangle+grey' => 'Campaign',
  'square+grey' => 'Continuous',
  'blue' => 'Past 3 days',
  'yellow' => '4&ndash;7 days ago',
  'orange' => '8&ndash;14 days ago',
  'red' => 'Over 14 days ago'
];
$kmlFileBaseUri = $networkParam . '/kml';
$lis = '';
$network = $rsNetwork->fetch(PDO::FETCH_OBJ);
$now = date(DATE_RFC2822);
$secs = 86400; // secs in one day

// Create features array for geoJson map layer and <li>'s for station list
while ($row = $rsStations->fetch(PDO::FETCH_OBJ)) {
  $id = intval($row->id);

  $features[] = [
    'coords' => [
      floatval($row->lon),
      floatval($row->lat)
    ],
    'id' => $id,
    'props' => [
      'days' => floor((strtotime($now) - strtotime($row->last_observation)) / $secs),
      'elevation' => $row->elevation,
      'last_observation' => $row->last_observation,
      'showcoords' => intval($row->showcoords),
      'station' => $row->station,
      'type' => $row->stationtype,
      'x' => $row->x,
      'y' => $row->y,
      'z' => $row->z
    ],
    'type' => 'Point'
  ];

  $lis .= sprintf('<li>
      <a href="%s/%s" class="link%d %s button" title="Go to station details">%s</a>
    </li>',
    $networkParam,
    $row->station,
    $id,
    getColor($row->last_observation),
    strtoupper($row->station)
  );
}

// Create HTML for station list buttons
$stationListHtml = sprintf('<ul class="stations no-style" style="height:%spx">
    %s
  </ul>',
  $height,
  $lis
);

// Create HTML for legend
$legendHtml = '<ul class="legend no-style">';
foreach ($legendIcons as $key => $description) {
  $legendHtml .= sprintf('<li class="%s">
      <img src="img/pin-s-%s-2x.png" alt="%s icon" /><span>%s</span>
    </li>',
    $key,
    $key,
    $key,
    $description
  );
}
$legendHtml .= '</ul>';

// Create HTML for Download links
$downloadsHtml = '<ul class="downloads no-style">';
if ($network->type === 'campaign') {
  $lis = '<li>
      <a href="' . $kmlFileBaseUri . '/years" class="kml">Campaign Stations Sorted by Year(s) Surveyed</a>
    </li>';
  $lis .= '<li>
      <a href="' . $kmlFileBaseUri . '/last" class="kml">Campaign Stations Sorted by Last Year Surveyed</a>
    </li>';
  $lis .= '<li>
      <a href="' . $kmlFileBaseUri . '/timespan" class="kml">Campaign Stations Sorted by Timespan Between Surveys</a>
    </li>';
} else { // continuous network
  $lis = '<li>
      <a href="' . $kmlFileBaseUri . '" class="kml">Stations Sorted by Station Name</a>
    </li>';
}
$downloadsHtml .= $lis;

foreach ($downloads as $name => $file) {
  $type = $file[0];
  $path = $file[1];
  $fullPath = $DATA_DIR . '/' . $path;

  if ($type === 'gpx') {
    $downloadsHtml .= sprintf('<li><a href="%s" class="%s">%s</a></li>',
      $path,
      $type,
      $name
    );
  } else if (file_exists($fullPath)) {
    $downloadsHtml .= sprintf('<li><a href="data/%s" class="%s">%s</a></li>',
      $path,
      $type,
      $name
    );
  }
}

$downloadsHtml .= '</ul>';

// Create geoJson data for embedding in HTML
$geoJson = getGeoJson([
  'count' => $count,
  'features' => $features
]);

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
  <h3 class="count"><?php print $count; ?> Stations on this Map</h3>
  <?php print $stationListHtml; ?>
</section>

<section>
  <h2>Downloads</h2>
  <?php print $downloadsHtml; ?>
</section>

<script>
  var data = {
    stations: <?php print $geoJson; ?>
  };
</script>

<?php } // End: valid network block ?>

<p class="back">&laquo;
  <a href="<?php print $MOUNT_PATH; ?>">Back to All Networks</a>
</p>
