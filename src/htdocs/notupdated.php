<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$days = 7;
$network = safeParam('network', 'Pacific');

if (!isset($TEMPLATE)) {
  $TITLE = "$network Network";
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="../css/notupdated.css" />';
  $FOOT = '<script src="../js/table.js"></script>';
  $CONTACT = 'jsvarc';

  include 'template.inc.php';
}

$db = new Db();

// Db query result: all stations that haven't been updated in past x days
$rsLastUpdated = $db->queryLastUpdated($network, $days);

// Create html for table
$table_html = '<table class="sortable">
  <tr class="no-sort"><th>Station</th><th class="sort-default" data-sort-order="desc">Last Observation</th></tr>';

while($row = $rsLastUpdated->fetch()) {
  $time = strtotime($row['last_observation']);
  $table_html .= sprintf('<tr><td>%s</td><td data-sort="%s">%s</td>',
    $row['station'],
    date('Y-m-d', $time),
    date('M j, Y', $time)
  );
}

$table_html .= '</table>';

$backlink = sprintf('<a href="%s/%s">Back to %s network</a>',
  $MOUNT_PATH,
  $network,
  $network
);

?>

<h2>
  Stations Not Updated in the Past <?php print $days; ?> Days
</h2>

<section>
  <?php print $table_html; ?>
</section>

<p class="back">&laquo;
  <?php print $backlink; ?>
</p>
