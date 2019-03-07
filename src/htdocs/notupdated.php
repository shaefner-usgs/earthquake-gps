<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$network = safeParam('network', 'Pacific');

if (!isset($TEMPLATE)) {
  $TITLE = "$network Network";
  $SUBTITLE = 'Stations Not Updated in the Past 7 Days';
  $TITLETAG = "$SUBTITLE | $TITLE";
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="../css/notupdated.css" />';
  $FOOT = '<script src="../js/table.js"></script>';
  $CONTACT = 'jsvarc';

  include 'template.inc.php';
}

$now = date(DATE_RFC2822);
$secs = 86400; // secs in one day

$db = new Db();

// Db query result: all stations that haven't been updated in past 7 days
$rsLastUpdated = $db->queryLastUpdated($network, 7);

// Create html for table
$html = '<table class="sortable">
  <tr class="no-sort"><th>Station</th><th class="sort-default" data-sort-order="desc">Last Observation</th></tr>';

while($row = $rsLastUpdated->fetch(PDO::FETCH_OBJ)) {
  $time = strtotime($row->last_observation);
  $days = floor((strtotime($now) - $time) / $secs);
  $color = getColor($days);

  $html .= sprintf('<tr>
      <td class="%s">%s</td><td data-sort="%s">%s</td>
    </tr>',
    $color,
    $row->station,
    date('Y-m-d', $time),
    date('M j, Y', $time)
  );
}

$html .= '</table>';

$backLink = sprintf('%s/%s',
  $MOUNT_PATH,
  $network
);

?>

<h2 class="subtitle">
  <?php print $SUBTITLE; ?>
</h2>

<section>
  <?php print $html; ?>
</section>

<p class="back">&laquo;
  <a href="<?php print $backLink; ?>">Back to <?php print $network; ?> Network</a>
</p>
