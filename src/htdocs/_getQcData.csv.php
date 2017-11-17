<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$station = safeParam('station', 'coco');
$now = date(DATE_RFC2822);

$db = new Db;

// Db query result: all qc data for a given station
$rsQcData = $db->queryQcData($station);

$output = "Date, Filename, Completeness, Slips_per_obs, MP1, MP2, SN1, SN2\n";

while($row = $rsQcData->fetch(PDO::FETCH_ASSOC)) {
  $output .= sprintf("%s, %s, %s, %s, %s, %s, %s, %s\n",
    $row['date'],
    $row['filename'],
    $row['percentage'],
    $row['slips_per_obs'],
    $row['mp1'],
    $row['mp2'],
    $row['sn1'],
    $row['sn2']
  );
}

// Send txt stream to browser
header('Content-type: text/plain');
print $output;
