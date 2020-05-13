<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$networkParam = safeParam('network', 'SFBayArea');
$stationParam = strtolower(safeParam('station', 'p271'));

$now = date(DATE_RFC2822);

$db = new Db;

// Db query: all qc data for a given station / network
$rsQcData = $db->queryQcData($networkParam, $stationParam);

$output = "Date, Filename, Completeness, Obs_per_slip, MP1, MP2, SN1, SN2\n";

while($row = $rsQcData->fetch(PDO::FETCH_ASSOC)) {
  $output .= sprintf("%s, %s, %s, %s, %s, %s, %s, %s\n",
    $row['date'],
    $row['filename'],
    $row['percentage'],
    $row['obs_per_slip'],
    $row['mp1'],
    $row['mp2'],
    $row['sn1'],
    $row['sn2']
  );
}

// Send txt stream to browser
header('Content-type: text/plain');
print $output;
