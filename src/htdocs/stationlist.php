<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$filterParam = safeParam('filter');

if (!isset($TEMPLATE)) {
  $TITLE = 'GPS Station List';
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="' . $MOUNT_PATH . '/css/stationlist.css" />';
  $CONTACT = 'jsvarc';

  include 'template.inc.php';
}

$db = new Db();

// Db queries
$rsStationChars = $db->queryStationChars(); // unique first chars of station names
$rsStationList = $db->queryStationList($filterParam);

// Create html for jumplist
$navHtml = '<nav class="jumplist">';
while ($row = $rsStationChars->fetch(PDO::FETCH_ASSOC)) {
  $linkHtml = '<a href="' . $MOUNT_PATH . '/stations/%s">%s</a>';
  // highlight current selection
  if ($row['alphanum'] === $filterParam) {
    $linkHtml = '<a href="' . $MOUNT_PATH . '/stations/%s"><strong>%s</strong></a>';
  }
  $navHtml .= sprintf($linkHtml,
    $row['alphanum'],
    strtoupper($row['alphanum'])
  );
}
$navHtml .= '</nav>';

// Create a more "friendly" array to loop over for creating list html
while ($row = $rsStationList->fetch(PDO::FETCH_ASSOC)) {
  $station = $row['station'];
  $networks[$station][] = $row['network'];
  $stations[$station] = $networks[$station];
}

// Create html for subheader
$sel = ucfirst($filterParam);
if ($sel === '') {
  $sel = 'All';
}
$subheaderHtml = sprintf ('<h2>&lsquo;%s&rsquo; Stations (%d)</h2>',
  $sel,
  count($stations)
);

// Create html for station list
$listHtml = '<ul class="stations no-style">';

if ($stations) {
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
}

$listHtml .= '</ul>';

// Render html
print $navHtml;
print $subheaderHtml;
print $listHtml;
