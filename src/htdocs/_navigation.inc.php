<?php

include_once '../conf/config.inc.php'; // app config

$section = $MOUNT_PATH;
$url = $_SERVER['REQUEST_URI'];

// Set up page matches for 'Data' tab
$matches = false;
if (
  // index
  preg_match("@^$section(/index.php)?$@", $url) ||
  // network
  preg_match("@^$section/(?!stations)[\w-]+$@", $url) ||
  // stations not updated, velocities
  preg_match("@^$section/[\w]+/(notupdated|offsets|velocities)$@", $url) ||
  // station
  preg_match("@^$section/[\w-]+/\w{4}$@", $url) ||
  // kinematic, logs, photos, qc
  preg_match("@^$section/[\w-]+/\w{4}/(kinematic|logs|photos|qc(/table)?)$@", $url) ||
  // plots.php
  $url === "$section/plots.php"
) {
  $matches = true;
}

$NAVIGATION =
  navGroup('GPS',
    navItem("$section", 'Data', $matches) .
    navItem("$section/stations", 'Station List') .
    navItem("$section/citation.php", 'Data Citation') .
    navItem("$section/about.php", 'About')
  );

print $NAVIGATION;
