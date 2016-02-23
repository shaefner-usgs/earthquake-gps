<?php

include '../lib/classes/Db.class.php'; // db connector, queries
include '../lib/functions/functions.inc.php'; // app functions

date_default_timezone_set('UTC');

$network = safeParam('network', 'Pacific');
$threshold = date('Y-m-d', strtotime('-8 days'));

$db = new Db();

// Db query result: all stations that haven't been updated since $threshold
$rsLastUpdated = $db->queryLastUpdated($network, $threshold);

// Send csv stream to browser
header('Content-Type: text/plain');

print "station, last observation\n";
while($row = $rsLastUpdated->fetch()) {
	printf("%s, %s\n",
    $row['station'],
    $row['last_observation']
  );
}
