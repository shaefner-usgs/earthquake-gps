<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$network = safeParam('network', 'SFBayArea');
$station = safeParam('station', 'p271');

if (!isset($TEMPLATE)) {
  $TITLE = 'GPS Station ' . strtoupper($station) . ' Quality Control Data';
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="../../../css/minimal.css" />';
  $CONTACT = 'jsvarc';

  include_once 'template.inc.php';
}

$db = new Db;

$rsQcData = $db->queryQcData($station, 14);

$backLink = sprintf('%s/%s/%s',
  $MOUNT_PATH,
  $network,
  $station
);
$name = strtoupper($station);

?>

<h2>Past 14 observations</h2>

<p class="back">&laquo;
  <a href="<?php print $backLink; ?>">Back to station <?php print $name; ?></a>
</p>
