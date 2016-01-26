<?php

include_once '../conf/config.inc.php';

if (!isset($TEMPLATE)) {
  $TITLE = 'GPS Data';
  $NAVIGATION = true;
  $HEAD = '';
  $FOOT = '';

  include_once 'template.inc.php';
}

?>

<p>As part of the earthquake process, Earth's surface is being deformed as earthquake faults accumulate strain and slip or slowly creep over time. We use GPS to monitor this movement by measuring the precise position (within 5mm or less) of stations near active faults relative to each other. Months or years later, we occupy the same stations again. By determining how the stations have moved we calculate ground deformation.</p>

<img src="data/networks/CentralUS/arfy/nafixed/arfy.png" />

<h2>GPS Networks</h2>

<div class="map"></div>

<h3>Networks on this map</h3>
