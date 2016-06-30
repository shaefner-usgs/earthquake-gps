<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions

$network = safeParam('network', 'SFBayArea');
$station = safeParam('station', 'p271');

if (!isset($TEMPLATE)) {
  $TITLE = 'GPS Station ' . strtoupper($station) . ' Kinematic Data';
  $NAVIGATION = true;
  $HEAD = '
    <link rel="stylesheet" href="' . $MOUNT_PATH . '/css/kinematic.css" />
  ';
  $FOOT = '
    <script>var MOUNT_PATH = "' . $MOUNT_PATH . '";</script>
    <script>var STATION = "' . $station . '";</script>
    <script src="/lib/dygraph/dygraph-combined.js"></script>
    <script src="' . $MOUNT_PATH . '/js/kinematic.js"></script>
  ';
  $CONTACT = 'jsvarc';

  include_once 'template.inc.php';
}

$backLink = sprintf('%s/%s/%s/',
  $MOUNT_PATH,
  $network,
  $station
);
$downloadLink = sprintf('%s/_getKinematic.txt.php?station=%s',
  $MOUNT_PATH,
  $station
);
$name = strtoupper($station);

?>

<p>Click and drag on a plot to zoom in. Hold shift while dragging to pan.</p>

<nav>
  <button class="pan left"><i class="material-icons">&#xE5CB;</i> Left</button>
  <button class="pan right">Right <i class="material-icons">&#xE5CC;</i></button>
  <button class="reset"><i class="material-icons">&#xE5D5;</i> Reset</button>
</nav>

<section class="plots">
  <div class="north plot"><p class="loading">Loading&hellip;</p></div>
  <div class="east plot"><p class="loading">Loading&hellip;</p></div>
  <div class="vertical plot"><p class="loading">Loading&hellip;</p></div>
  <p>
    <a href="<?php print $downloadLink; ?>">Download data</a>
  </p>
</section>


<p class="back">&laquo;
  <a href="<?php print $backLink; ?>">Back to station <?php print $name; ?></a>
</p>
