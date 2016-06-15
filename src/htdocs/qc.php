<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions

$station = safeParam('station', 'coco');

if (!isset($TEMPLATE)) {
  $TITLE = 'GPS Station ' . strtoupper($station) . ' Quality Control Data';
  $NAVIGATION = true;
  $HEAD = '
    <link rel="stylesheet" href="/lib/c3/c3.css" />
    <link rel="stylesheet" href="' . $MOUNT_PATH . '/css/qc.css" />
  ';
  $FOOT = '
    <script>var MOUNT_PATH = "' . $MOUNT_PATH . '";</script>
    <script>var STATION = "' . $station . '";</script>
    <script src="/lib/d3/d3.js"></script>
    <script src="/lib/c3/c3.js"></script>
    <script src="' . $MOUNT_PATH . '/js/qc.js"></script>
  ';
  $CONTACT = 'jsvarc';

  include_once 'template.inc.php';
}

?>

<div id="application">
  <noscript>
    <p class="alert info">Javascript must be enabled to view these plots.</p>
  </noscript>
</div>
