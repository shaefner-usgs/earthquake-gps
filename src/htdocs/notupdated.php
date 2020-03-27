<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$networkParam = safeParam('network', 'Pacific');

if (!isset($TEMPLATE)) {
  $TITLE = "$networkParam Network";
  $SUBTITLE = 'Stations Not Updated in the Past 7 Days';
  $TITLETAG = "$SUBTITLE | $TITLE";
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="../css/notupdated.css" />';
  $FOOT = '<script src="../js/table.js"></script>';
  $CONTACT = 'jsvarc';

  include 'template.inc.php';
}

$db = new Db();

// Db query: all stations that haven't been updated in past 7 days
$rsLastUpdated = $db->queryLastUpdated($networkParam, 7);

if ($rsLastUpdated->rowCount() > 0) {
  // Create html for table
  $html = '<table class="sortable">
    <tr class="no-sort">
      <th>Station</th>
      <th class="sort-default" data-sort-order="desc">Last Observation</th>
    </tr>';

  while($row = $rsLastUpdated->fetch(PDO::FETCH_OBJ)) {
    $time = strtotime($row->last_observation);

    $html .= sprintf('<tr>
        <th class="%s link">
          <a href="./%s" class="%s button">%s</a>
        </th>
        <td data-sort="%s">%s</td>
      </tr>',
      getColor($row->last_observation),
      strtolower($row->station),
      getColor($row->last_observation),
      strtoupper($row->station),
      date('Y-m-d', $time),
      date('M j, Y', $time)
    );
  }

  $html .= '</table>';
} else {
  $html = '<p class="alert info">None</p>';
}

$backLink = sprintf('%s/%s',
  $MOUNT_PATH,
  $networkParam
);

?>

<h2 class="subtitle">
  <?php print $SUBTITLE; ?>
</h2>

<nav>
  <ul class="pipelist no-style">
    <li>
      <a href="../<?php print $networkParam; ?>">Station Map</a>
    </li>
    <li>
      <a href="../<?php print $networkParam; ?>/velocities">Velocities and Uncertainties</a>
    </li>
    <li>
      <a href="../<?php print $networkParam; ?>/offsets">Offsets</a>
    </li>
    <li>
      <strong>Stations Not Updated in the Past 7 Days</strong>
    </li>
  </ul>
</nav>

<?php print $html; ?>

<p class="back">&laquo;
  <a href="<?php print $backLink; ?>">Back to Station Map</a>
</p>
