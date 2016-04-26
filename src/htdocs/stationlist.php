<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/functions/functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

if (!isset($TEMPLATE)) {
  $TITLE = 'GPS Station List';
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="/css/stationlist.css" />';
  $FOOT = '';

  include 'template.inc.php';
}

$filter = safeParam('filter');

$db = new Db();

// Db query result: first char for all stations in db
$rsStationChars = $db->queryStationChars();

// Create html for jumplist
$nav_html = '<nav class="jumplist">';
while ($row = $rsStationChars->fetch(PDO::FETCH_ASSOC)) {
  $link_html = '<a href="%s/stations/%s/">%s</a>';
  // highlight current selection
  if ($row['alphanum'] === $filter) {
    $link_html = '<a href="%s/stations/%s/"><strong>%s</strong></a>';
  }
  $nav_html .= sprintf($link_html,
    $MOUNT_PATH,
    $row['alphanum'],
    strtoupper($row['alphanum'])
  );
}
$nav_html .= '</nav>';

// Db query result: station list
$rsStationList = $db->queryStationList($filter);

// Create a more "friendly" array to loop over for creating list html
while ($row = $rsStationList->fetch(PDO::FETCH_ASSOC)) {
  $station = $row['station'];
  $networks[$station][] = $row['network'];
  $stations[$station] = $networks[$station];
}

// Create html for subheader
$sel = strtoupper($filter);
if (!$sel) {
  $sel = 'All';
}
$subheader = sprintf ('<h2>&lsquo;%s&rsquo; Stations (%d)</h2>',
  $sel,
  count($stations)
);

// Create html for station list
$list_html = '<ul class="stations no-style">';
foreach ($stations as $station => $networks) {
  $networks_html = '<ul class="no-style">';
  foreach ($networks as $network) {
    $networks_html .= sprintf ('<li><a href="%s/%s/%s/">%s</a></li>',
      $MOUNT_PATH,
      $network,
      $station,
      $network
    );
  }
  $networks_html .= '</ul>';
  $list_html .= sprintf('<li><h3>%s</h3>%s</li>',
    strtoupper($station),
    $networks_html
  );
}
$list_html .= '</ul>';

// Render html
print $nav_html;
print $subheader;
print $list_html;
