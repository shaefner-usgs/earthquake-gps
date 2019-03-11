<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$direction = safeParam('direction');
$stationParam = strtolower(safeParam('station', '157p'));
$now = date(DATE_RFC2822);

$db = new Db;

// Db query result: time series data for given station
$rsTimeSeries = $db->queryTimeSeries($stationParam);

// Set header
if (preg_match('/^north|east|up$/', $direction)) {
  $header = sprintf("Datetime-UTC, %s\n", ucfirst($direction));
} else {
  $header = "Datetime-UTC, Datetime-J2000, North, East, Up\n";
}
$output = $header;

// Add timeseries data
while ($row = $rsTimeSeries->fetch(PDO::FETCH_ASSOC)) {
  // J2000 time (epoch field) starts at 12 noon on 1 January 2000 UTC
  $secs_1970_2000 = mktime(12, 0, 0, 01, 01, 2000);
  $timestamp = $row['epoch'] + $secs_1970_2000;
  $date = date('Y/m/d H:i:s', $timestamp);

  // show only chosen component - for timeseries plot
  if ($direction) {
    $column = $direction;
    if ($direction === 'up') {
      $column = 'vertical'; // db field is 'vertical' for up component
    }
    $values = floatval($row[$column]);
  }
  // or, show all components (plus J2000 time) - download option
  else {
    $values = sprintf('%s, %f, %f, %f',
      $row['epoch'],
      floatval($row['north']),
      floatval($row['east']),
      floatval($row['vertical'])
    );
  }

  $output .= "$date, $values\n";
}

// Send txt stream to browser
header('Content-type: text/plain');
print $output;
