<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

if (!isset($TEMPLATE)) {
  $TITLE = 'GPS Station List';
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="' . $MOUNT_PATH . '/css/stationlist.css" />';
  $CONTACT = 'jsvarc';

  include 'template.inc.php';
}

$filter = safeParam('filter');

$db = new Db();

// Db query result: first char for all stations in db
$rsStationChars = $db->queryStationChars();

// Create html for jumplist
$navHtml = '<nav class="jumplist">';
while ($row = $rsStationChars->fetch(PDO::FETCH_ASSOC)) {
  $linkHtml = '<a href="' . $MOUNT_PATH . '/stations/%s">%s</a>';
  // highlight current selection
  if ($row['alphanum'] === $filter) {
    $linkHtml = '<a href="' . $MOUNT_PATH . '/stations/%s"><strong>%s</strong></a>';
  }
  $navHtml .= sprintf($linkHtml,
    $row['alphanum'],
    strtoupper($row['alphanum'])
  );
}
$navHtml .= '</nav>';

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
if ($sel === '') {
  $sel = 'All';
}
$subheaderHtml = sprintf ('<h2>&lsquo;%s&rsquo; Stations (%d)</h2>',
  $sel,
  count($stations)
);

// Create html for station list
$listHtml = '<ul class="stations no-style">';
foreach ($stations as $station => $networks) {
  $networksHtml = '<ul class="no-style">';
  foreach ($networks as $network) {
    $networksHtml .= sprintf ('<li><a href="%s/%s/%s">%s</a></li>',
      $MOUNT_PATH,
      $network,
      $station,
      $network
    );
  }
  $networksHtml .= '</ul>';
  $listHtml .= sprintf('<li><h3>%s</h3>%s</li>',
    strtoupper($station),
    $networksHtml
  );
}
$listHtml .= '</ul>';

// Render html
print $navHtml;
print $subheaderHtml;
print $listHtml;
