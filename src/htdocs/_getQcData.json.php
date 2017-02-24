<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$callback = safeParam('callback');
$station = safeParam('station', 'coco');
$now = date(DATE_RFC2822);

$db = new Db;

// Db query result: all qc data for a given station
$rsQcData = $db->queryQcData($station);

// Initialize array template for json feed
$output = [
  'count' => $rsQcData->rowCount(),
  'generated' => $now
];

// Create a nested array for each type of data to store
$fields = [
  'comp_obs',
  'date',
  'mp1',
  'mp2',
  'pos_obs',
  'slips',
  'sn1',
  'sn2'
];
foreach ($fields as $field) {
  $output[$field] = [];
}

// Store results from db into output array
while($row = $rsQcData->fetch(PDO::FETCH_ASSOC)) {
  foreach($fields as $field) {
    $value = $row[$field];
    if ($field === 'date') {
      $value = date('Y-m-d', strtotime($value));
    } else if ($field === 'slips') {
      // convert slips to log base 10
      if ($value <= 0) {
        $value = 0;
      } else {
        $value = round(log10($value), 2);
      }
    } else {
      if ($value !== null) {
        $value = floatval($value);
      }
    }
    array_push($output[$field], $value);
  }
}

// send json stream
showJson($output);
