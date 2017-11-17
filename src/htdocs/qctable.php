<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$network = safeParam('network', 'SFBayArea');
$station = safeParam('station', 'p271');

if (!isset($TEMPLATE)) {
  $TITLE = 'GPS Station ' . strtoupper($station) . ' Quality Control Data';
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="../../../css/base.css" />';
  $CONTACT = 'jsvarc';

  include 'template.inc.php';
}

$db = new Db;

// Db query result: past 14 records of QC data
$rsQcData = $db->queryQcData($station, 14);

// Create html for table body
$table_body_html = '';
while($row = $rsQcData->fetch(PDO::FETCH_ASSOC)) {
  $table_body_html .= sprintf("<tr>
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
  $network,
  $station
);
$name = strtoupper($station);

?>

<h2>Past 14 observations</h2>

<table>
  <tr>
    <th>Year</th>
    <th><abbr title="Day of the year">DoY</abbr></th>
    <th>Date</th>
    <th>Filename</th>
    <th>Completeness</th>
    <th>Slips</th>
    <th>MP1</th>
    <th>MP2</th>
    <th>SN1</th>
    <th>SN2</th>
  </tr>

<?php print $table_body_html; ?>

</table>

<p class="back">&laquo;
  <a href="<?php print $backLink; ?>">Back to station <?php print $name; ?></a>
</p>
