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

<div class="north plot"><p>Loading&hellip;</p></div>
<div class="east plot"><p>Loading&hellip;</p></div>
<div class="vertical plot"><p>Loading&hellip;</p></div>

<p>
  <a href="<?php print $downloadLink; ?>">Download data</a>
</p>

<p class="back">&laquo;
  <a href="<?php print $backLink; ?>">Back to station <?php print $name; ?></a>
</p>
