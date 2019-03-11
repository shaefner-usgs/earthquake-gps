<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$networkParam = safeParam('network', 'SFBayArea');
$stationParam = strtolower(safeParam('station', 'p271'));

$stationName = strtoupper($stationParam);

if (!isset($TEMPLATE)) {
  $TITLE = "$networkParam Network";
  $SUBTITLE = sprintf ('<a href="../../%s">Station %s</a> <span>Quality Control Data</span>',
    $stationParam,
    $stationName
  );
  $TITLETAG = "$SUBTITLE | $TITLE";
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="../../../css/qctable.css" />';
  $FOOT = '<script src="../../../js/table.js"></script>';
  $CONTACT = 'jsvarc';

  include 'template.inc.php';
}

$db = new Db;

// Db query results: station details for selected station and past 14 records of QC data
$rsStation = $db->queryStation($stationParam);
$rsQcData = $db->queryQcData($networkParam, $stationParam, 14);

$station = $rsStation->fetch();
$color = getColor($station['last_observation']);

// Create html for table body
$tableBodyHtml = '';
while($row = $rsQcData->fetch(PDO::FETCH_ASSOC)) {
  $tableBodyHtml .= sprintf("<tr>
      <td>%s</td>
      <td>%s</td>
      <td>%s</td>
      <td>%s</td>
      <td>%s</td>
      <td>%s</td>
      <td>%s</td>
      <td>%s</td>
      <td>%s</td>
      <td>%s</td>
    </tr>\n",
    substr($row['date'], 0, 4),
    date('z', strtotime($row['date'])) + 1,
    $row['date'],
    $row['filename'],
    $row['percentage'],
    $row['slips_per_obs'],
    $row['mp1'],
    $row['mp2'],
    $row['sn1'],
    $row['sn2']
  );
}

$backLink = sprintf('%s/%s/%s',
  $MOUNT_PATH,
  $networkParam,
  $stationParam
);

?>

<h2 class="subtitle <?php print $color; ?>">
  <?php print $SUBTITLE; ?>
</h2>

<ul class="pipelist no-style">
  <li><a href="../qc">Plots</a></li>
  <li><strong>Table</strong></li>
</ul>

<table class="sortable">
  <tr class="no-sort">
    <th>Year</th>
    <th><abbr title="Day of the year">DoY</abbr></th>
    <th class="sort-default" data-sort-order="desc">Date</th>
    <th>Filename</th>
    <th>Completeness</th>
    <th>Slips</th>
    <th>MP1</th>
    <th>MP2</th>
    <th>SN1</th>
    <th>SN2</th>
  </tr>

<?php print $tableBodyHtml; ?>

</table>

<p>Past 14 observations.</p>

<p class="back">&laquo;
  <a href="<?php print $backLink; ?>">Back to Station <?php print $stationName; ?></a>
</p>
