<?php

$section = '/monitoring/gps';

$matches = false;
if (preg_match("@^/monitoring/gps/?(index.php)?$@", $_SERVER['REQUEST_URI']) ||
  $_SERVER['REQUEST_URI'] === "$section/kinematic.php" ||
  $_SERVER['REQUEST_URI'] === "$section/network.php" ||
  $_SERVER['REQUEST_URI'] === "$section/qc.php" ||
  $_SERVER['REQUEST_URI'] === "$section/station.php" ||
  $_SERVER['REQUEST_URI'] === "$section/photos.php" ||
  $_SERVER['REQUEST_URI'] === "$section/logsheets.php") {
    $matches = true;
}

$NAVIGATION =
  navGroup('GPS',
    navItem("$section/", 'Data', $matches) .
    navItem("$section/stations/", 'Station List') .
    navItem("$sectionabout.php", 'About')
  );

print $NAVIGATION;
