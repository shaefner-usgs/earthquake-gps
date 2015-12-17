<?php

if (!isset($TEMPLATE)) {
  $TITLE = 'Example Leaflet Map';
  $FOOT = '
    <script src="/lib/leaflet-0.7.x/leaflet.js"></script>
    <script src="js/map/index.js"></script>
  ';
  $HEAD = '
    <link rel="stylesheet" href="/lib/leaflet-0.7.x/leaflet.css" />
    <link rel="stylesheet" href="css/map/index.css" />
  ';
  include_once 'template.inc.php';
}

?>

<div id="map"></div>
