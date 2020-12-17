<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$stationParam = strtolower(safeParam('station', 'fire'));

$db = new Db();

$rsMetadata = $db->queryMetadata($stationParam);

$output = "benchmark, date, observer, organization, receiver, receiver_sn, receiver_type, antenna_sn, antenna_type, dome, height, interval, first_observation\n";
while($row = $rsMetadata->fetch()) {
  $output .= sprintf("%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s\n",
    $row['benchmark'],
    $row['date'],
    $row['observer'],
    $row['organization'],
    $row['receiver'],
    $row['receiver_sn'],
    $row['receiver_type'],
    $row['antenna_sn'],
    $row['antenna_type'],
    $row['dome'],
    $row['height'],
    $row['interval'],
    $row['first_observation']
  );
}

// Send txt stream to browser
header('Content-type: text/plain');
print $output;