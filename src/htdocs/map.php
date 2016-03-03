<?php

if (!isset($TEMPLATE)) {
  $TITLE = 'Map';
  $HEAD = '
    <link rel="stylesheet" href="/lib/leaflet-0.7.x/leaflet.css" />
    <link rel="stylesheet" href="css/map/index.css" />
  ';
  $FOOT = '
    <script src="/lib/leaflet-0.7.x/leaflet.js"></script>
    <script src="js/map/index.js"></script>
  ';

  include 'template.inc.php';
}

?>

<div id="map"></div>
