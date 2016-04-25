<?php

include_once '../conf/config.inc.php'; // app config

if (!isset($TEMPLATE)) {
  $TITLE = 'Quality Control Plots';
  $NAVIGATION = true;
  $HEAD = '
    <link rel="stylesheet" href="lib/c3/c3.css" />
    <link rel="stylesheet" href="css/qc.css" />
  ';
  $FOOT = '
    <script>var MOUNT_PATH = "' . $MOUNT_PATH . '";</script>
    <script src="lib/d3/d3.js"></script>
    <script src="lib/c3/c3.js"></script>
    <script src="js/qc.js"></script>
  ';

  include_once 'template.inc.php';
}

?>

<div id="application">
  <noscript>Try javascript</noscript>
</div>
