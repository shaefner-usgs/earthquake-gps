<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions

$network = safeParam('network', 'SFBayArea');
$station = safeParam('station', 'p271');

if (!isset($TEMPLATE)) {
  $TITLE = 'GPS Station ' . strtoupper($station) . ' Quality Control Data';
  $NAVIGATION = true;
  $HEAD = '
    <link rel="stylesheet" href="../../lib/c3/c3.css" />
    <link rel="stylesheet" href="../../css/qc.css" />
  ';
  $FOOT = '
    <script>var MOUNT_PATH = "' . $MOUNT_PATH . '";</script>
    <script>var STATION = "' . $station . '";</script>
    <script src="../../lib/d3/d3.js"></script>
    <script src="../../lib/c3/c3.js"></script>
    <script src="../../js/qc.js"></script>
  ';
  $CONTACT = 'jsvarc';

  include_once 'template.inc.php';
}

$backLink = sprintf('%s/%s/%s',
  $MOUNT_PATH,
  $network,
  $station
);
$name = strtoupper($station);

?>

<div id="application">
  <noscript>
    <p class="alert info">Javascript must be enabled to view these plots.</p>
  </noscript>
</div>

<p class="back">&laquo;
  <a href="<?php print $backLink; ?>">Back to station <?php print $name; ?></a>
</p>
