<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$db = new Db();
$output = 'benchmark, date, observer, organization, receiver, receiver_sn, ' .
  'receiver_type, antenna, antenna_sn, dome, height (m), slant height (m), ' .
  "interval, first_observation (s)\n";
$stationParam = strtolower(safeParam('station', 'fire'));
$rsMetadata = $db->queryMetadata($stationParam);

while($row = $rsMetadata->fetch()) {
  $output .= sprintf("%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s\n",
    $row['benchmark'],
    $row['date'],
    $row['observer'],
    $row['organization'],
    $row['receiver'],
    $row['receiver_sn'],
    $row['receiver_type'],
    $row['antenna_type'],
    $row['antenna_sn'],
    $row['dome'],
    $row['height'],
    $row['height_slant'],
    $row['interval'],
    $row['first_observation']
  );
}

// Send txt stream to browser
header('Content-type: text/plain');
print $output;
