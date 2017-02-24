<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$days = 7;
$network = safeParam('network', 'Pacific');

if (!isset($TEMPLATE)) {
  $TITLE = "$network Network";
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="../css/base.css" />';
  $CONTACT = 'jsvarc';

  include 'template.inc.php';
}

$db = new Db();

// Db query result: all stations that haven't been updated in past x days
$rsLastUpdated = $db->queryLastUpdated($network, $days);

// Create html for table
$table_html = '<table>
  <tr><th>Station</th><th>Last Observation</th></tr>';

while($row = $rsLastUpdated->fetch()) {
  $table_html .= sprintf("<tr><td>%s</td><td>%s</td>\n",
    $row['station'],
    date('M j, Y', strtotime($row['last_observation']))
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
  Stations not updated in the past <?php print $days; ?> days
</h2>

<section>
  <?php print $table_html; ?>
</section>

<p class="back">&laquo;
  <?php print $backlink; ?>
</p>
