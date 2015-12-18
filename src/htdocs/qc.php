<?php

if (!isset($TEMPLATE)) {
  $TITLE = 'Quality Control Plots';
  $HEAD = '
    <link rel="stylesheet" href="lib/c3/c3.css" />
    <link rel="stylesheet" href="css/qc/index.css" />
  ';
  $FOOT = '
    <script src="lib/d3/d3.js"></script>
    <script src="lib/c3/c3.js"></script>
    <script src="js/qc/index.js"></script>
  ';

  include_once 'template.inc.php';
}

?>

<div id="application">
  <noscript>Try javascript</noscript>
</div>
