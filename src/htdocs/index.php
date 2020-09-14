<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

if (!isset($TEMPLATE)) {
  $TITLE = 'GPS Data';
  $NAVIGATION = true;
  $HEAD = '
    <link rel="stylesheet" href="gps/lib/leaflet/leaflet.css" />
    <link rel="stylesheet" href="gps/css/index.css" />
  ';
  $FOOT = '
    <script>var MOUNT_PATH = "' . $MOUNT_PATH . '";</script>
    <script src="gps/lib/leaflet/leaflet.js"></script>
    <script src="gps/js/index.js"></script>
  ';
  $CONTACT = 'jsvarc';

  include 'template.inc.php';
}

$db = new Db;

// Db query: all "non-hidden" networks
$rsNetworks = $db->queryNetworks();

$count = $rsNetworks->rowCount();
$features = [];
$height = ceil($count / 4) * 36;
$legendIcons = [
  'triangle+grey' => 'Campaign',
  'square+grey' => 'Continuous'
];
$lis = '';

// Create features array for geoJson map layer and <li>'s for network list
while ($row = $rsNetworks->fetch(PDO::FETCH_OBJ)) {
  $id = intval($row->id);

  $features[] = [
    'coords' => [
      floatval($row->lon),
      floatval($row->lat)
    ],
    'id' => 'point' . $id,
    'props' => [
      'name' => $row->network,
      'type' => $row->type
    ],
    'type' => 'Point'
  ];

  if ($row->polygon) { // network has a polygon defined, add it
    $features[] = [
      'coords' => [json_decode($row->polygon, true)],
      'id' => 'poly' . $id,
      'props' => [],
      'type' => 'Polygon'
    ];
  }

  $lis .= sprintf('<li>
      <a href="gps/%s" class="link%d button" title="Go to map of stations">%s</a>
    </li>',
    $row->network,
    $id,
    $row->network
  );
}

// Create HTML for network list buttons
$networkListHtml = sprintf('<ul class="networks no-style" style="height: %spx">
    %s
  </ul>',
  $height,
  $lis
);

// Create HTML for legend
$legendHtml = '<ul class="legend no-style">';
foreach ($legendIcons as $key => $description) {
  $legendHtml .= sprintf('<li>
      <img src="gps/img/pin-s-%s-2x.png" alt="%s icon" /><span>%s</span>
    </li>',
    $key,
    $key,
    $description
  );
}
$legendHtml .= '</ul>';

// Create geoJson data for embedding in HTML
$geoJson = getGeoJson([
  'count' => $count,
  'features' => $features
]);

?>

<p>As part of the earthquake process, Earth&rsquo;s surface is being deformed as
  earthquake faults accumulate strain and slip or slowly creep over time. We
  use GPS to monitor this movement by measuring the precise position (within
  5mm or less) of stations near active faults relative to each other. Months
  or years later, we occupy the same stations again. By determining how the
  stations have moved we calculate ground deformation.
  <a href="gps/about.php">Read more</a> &raquo;</p>

<p class="alert info"><a href="gps/citation.php">How to Cite GPS Data</a></p>

<section>
  <h2>View Stations by Network</h2>
  <div class="map"></div>
  <?php print $legendHtml; ?>
  <h3 class="count"><?php print $count; ?> Networks on this Map</h3>
  <?php print $networkListHtml; ?>
</section>

<section>
  <h2>View Stations Alphabetically</h2>
  <p><a href="gps/stations">Station List</a></p>
</section>

<section>
  <h2>Downloads</h2>
  <ul class="downloads no-style">
    <li><a href="gps/kml/years" class="kml">Campaign Stations Sorted by Year(s) Surveyed</a></li>
    <li><a href="gps/kml/last" class="kml">Campaign Stations Sorted by Last Year Surveyed</a></li>
    <li><a href="gps/kml/timespan" class="kml">Campaign Stations Sorted by Timespan Between Surveys</a></li>
  </ul>
</section>

<section class="meta">
  <h2>Cooperators</h2>
  <p>The USGS Earthquake Hazards Program (EHP) supports GPS data collection
    throughout the western U.S. through cooperative agreements with Central
    Washington University, University of California at Berkeley, University of
    Memphis, and University of Nevada at Reno. We process GPS data that we
    collect, as well as data from the USGS Volcano Hazards Program (VHP), our
    cooperator institutions, UNAVCO Inc., and other sources (see a
    <a href="gps/sources.php">full listing of observing agencies</a>). These
    results are available on this website as time series of daily GPS positions.</p>
</section>

<script>
  var data = {
    networks: <?php print $geoJson; ?>
  };
</script>
