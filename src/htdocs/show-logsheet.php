<?php

include '../conf/config.inc.php'; // app config
include '../lib/_functions.inc.php'; // app functions


/**
 * This simple script just displays an image embedded in a barebones web page
 *
 * Necessary b/c Google Earth consistently crashes when linking to an image
 * file directly
 *
 * example image URL:
 *   https://earthquake.usgs.gov/monitoring/gps/data/stations/s.dir/silv/logsheets/silv20141024a1.gif
 */

 //

$img = safeParam('img');

preg_match('/\d{8}/', $img, $matches);

$date = date('Y-m-d', strtotime($matches[0]));
$firstChar = substr($img, 0, 1);
$station = substr($img, 0, 4);
$title = 'GPS Log Sheet: Station ' . strtoupper($station) . " on $date";

$src = sprintf('https://earthquake.usgs.gov%s/data/stations/%s.dir/%s/logsheets/%s',
  $MOUNT_PATH,
  $firstChar,
  $station,
  $img
);

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title><?php print $title; ?></title>
  </head>
  <body>
    <img src="<?php print $src; ?>" alt="logsheet" width="100%" />
  </body>
</html>
