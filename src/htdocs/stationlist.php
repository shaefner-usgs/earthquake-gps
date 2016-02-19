<?php

if (!isset($TEMPLATE)) {
  $TITLE = 'GPS Station List';
  $HEAD = '';
  $FOOT = '';

  include '../conf/config.inc.php'; // app config
  include '../lib/functions/functions.inc.php'; // app functions
  include '../lib/classes/Db.class.php'; // db connector, queries
  include 'template.inc.php';
}

$filter = safeParam('filter', 'a');

$db = new Db();

// Db query result: station list
$rsStationList = $db->queryStationList($filter);

// Create a more "friendly" array to loop over for creating html
while ($row = $rsStationList->fetch(PDO::FETCH_ASSOC)) {
  $station = $row['station'];
  $networks[$station][] = $row['network'];
  $stations[$station] = $networks[$station];
}

// Create html for station list
$list_html = '<ul class="no-style">';
foreach ($stations as $station => $networks) {
  $networks_html = '<ul>';
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

// Db query result: first char for all stations in db
$rsStationChars = $db->queryStationChars();

// Create html for navbar
$nav_html = '<nav class="jumplist">';
while ($row = $rsStationChars->fetch(PDO::FETCH_ASSOC)) {
  $link_html = '<a href="%s/stationlist.php?filter=%s">%s</a>';
  if ($row['alphanum'] === $filter) {
    $link_html = '<a href="%s/stationlist/%s/"><strong>%s</strong></a>';
  }
  $nav_html .= sprintf($link_html,
    $MOUNT_PATH,
    $row['alphanum'],
    $row['alphanum']
  );
}
$nav_html .= '</nav>';

// Render html
print $nav_html;
print $list_html;
