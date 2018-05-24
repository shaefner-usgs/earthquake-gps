<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$network = safeParam('network', 'SFBayArea');
$station = safeParam('station', '208p');
$datatype = safeParam('datatype', 'filtered');

$db = new Db();

$rsDeleted = $db->queryDeletedPts($network, $station, $datatype);

$output = "Date, Component, Network, Datatype, Method\n";

while($row = $rsDeleted->fetch()) {
  $output .= sprintf("%s, %s, %s, %s, %s\n",
    $row['date'],
    $row['component'],
    $row['network'],
    $row['datatype'],
    $row['method']
  );
}

// Send txt stream to browser
header('Content-type: text/plain');
print $output;

?>
