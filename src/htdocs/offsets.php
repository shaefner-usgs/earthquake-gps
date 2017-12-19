<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$network = safeParam('network', 'SFBayArea');

if (!isset($TEMPLATE)) {
  $TITLE = "$network Network";
  $NAVIGATION = true;
  $HEAD = '';
  $FOOT = '';
  $CONTACT = 'jsvarc';

  include 'template.inc.php';
}

$db = new Db;

// Db query result: velocities for selected network
$rsOffsets = $db->queryVelocities($network);

$datatypes = [
  'nafixed' => 'NA-fixed',
  'itrf2008' => 'ITRF2008',
  'filtered' => 'Filtered'
];

// Create html for tables
$html = '';
$tableHeader = '<table class="sortable">
  <tr class="no-sort">
    <th class="sort-default">Station</th>
    <th>Date</th>
    <th>Decimal Date</th>
    <th>Offset (E)</th>
    <th>Offset (N)</th>
    <th>Offset (U)</th>
    <th>Sigma (E)</th>
    <th>Sigma (N)</th>
    <th>Sigma (U)</th>
    <th>Type</th>
  </tr>';
$tableBody = [];
$tableFooter = '</table>';

while ($row = $rsOffsets->fetch(PDO::FETCH_OBJ)) {

}

$backLink = sprintf('%s/%s',
  $MOUNT_PATH,
  $network
);

?>

<h2>Offsets</h2>

<div class="tablist">
  <?php print $html; ?>
</div>

<p class="back">&laquo;
  <a href="<?php print $backLink; ?>">Back to <?php print $network; ?>network</a>
</p>
