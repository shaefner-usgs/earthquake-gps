<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$networkParam = safeParam('network', 'SFBayArea');
$stationParam = strtolower(safeParam('station', 'p271'));

$stationName = strtoupper($stationParam);

if (!isset($TEMPLATE)) {
  $TITLE = "$networkParam Network";
  $SUBTITLE = sprintf ('<a href="../%s">Station %s</a> <span>Kinematic Data</span>',
    $stationParam,
    $stationName
  );
  $TITLETAG = "$SUBTITLE | $TITLE";
  $NAVIGATION = true;
  $HEAD = '
    <link rel="stylesheet" href="../../lib/dygraph/dygraph.css" />
    <link rel="stylesheet" href="../../css/kinematic.css" />
  ';
  $FOOT = '
    <script>
      var MOUNT_PATH = "' . $MOUNT_PATH . '",
          STATION = "' . $stationParam . '";
    </script>
    <script src="../../lib/dygraph/dygraph.js"></script>
    <script src="../../js/kinematic.js"></script>
  ';
  $CONTACT = 'jsvarc';

  include 'template.inc.php';
}

$db = new Db();

// Db query result: station details for selected station
$rsStation = $db->queryStation($stationParam);

$station = $rsStation->fetch();
$color = getColor($station['last_observation']);

$backLink = sprintf('%s/%s/%s',
  $MOUNT_PATH,
  $networkParam,
  $stationParam
);

?>

<h2 class="subtitle <?php print $color; ?>">
  <?php print $SUBTITLE; ?>
</h2>

<p>5-minute Kinematic Results.</p>

<nav>
  <button class="pan left"><i class="material-icons">&#xE5CB;</i> Left</button>
  <button class="pan right">Right <i class="material-icons">&#xE5CC;</i></button>
  <button class="reset"><i class="material-icons">&#xE5D5;</i> Reset</button>
</nav>

<section class="plots">
  <div class="north plot"><p class="loading">Loading&hellip;</p></div>
  <div class="east plot"><p class="loading">Loading&hellip;</p></div>
  <div class="up plot"><p class="loading">Loading&hellip;</p></div>
  <p>Click and drag on a plot to zoom in. Hold shift while dragging to pan.</p>
</section>

<h3>Download</h3>
<ul class="downloads no-style">
  <li><a href="kinematic/data" class="text">Plot Data</a></li>
</ul>

<p class="back">&laquo;
  <a href="<?php print $backLink; ?>">Back to Station <?php print $stationName; ?></a>
</p>
