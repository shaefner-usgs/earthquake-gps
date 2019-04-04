<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$networkParam = safeParam('network', 'SFBayArea');
$stationParam = strtolower(safeParam('station', 'p271'));

$stationName = strtoupper($stationParam);

if (!isset($TEMPLATE)) {
  $TITLE = sprintf ('<a href="../../%s">%s Network</a>',
    $networkParam,
    $networkParam
  );
  $SUBTITLE = sprintf ('<a href="../%s" class="button">Station %s</a> <span>Quality Control Data</span>',
    $stationParam,
    $stationName
  );
  $TITLETAG = "$SUBTITLE | $TITLE";
  $NAVIGATION = true;
  $HEAD = '
    <link rel="stylesheet" href="../../lib/c3/c3.css" />
    <link rel="stylesheet" href="../../css/qc.css" />
  ';
  $FOOT = '
    <script>
      var MOUNT_PATH = "' . $MOUNT_PATH . '",
          NETWORK = "' . $networkParam . '",
          STATION = "' . $stationParam . '";
    </script>
    <script src="../../lib/d3/d3.js"></script>
    <script src="../../lib/c3/c3.js"></script>
    <script src="../../js/qc.js"></script>
  ';
  $CONTACT = 'jsvarc';

  include 'template.inc.php';
}

$db = new Db();

// Db query result: station details for selected station
$rsStation = $db->queryStation($stationParam);

$station = $rsStation->fetch();
$color = getColor($station['last_observation']);

$backLink = sprintf('%s/%s/%s',
  $MOUNT_PATH,
  $networkParam,
  $stationParam
);

?>

<h2 class="subtitle">
  <?php print str_replace('button', "$color button", $SUBTITLE); ?>
</h2>

<ul class="pipelist no-style">
  <li><strong>Plots</strong></li>
  <li><a href="./qc/table">Table</a></li>
</ul>

<div class="application">
  <noscript>
    <p class="alert info">Javascript must be enabled to view these plots.</p>
  </noscript>
  <section class="app-download">
    <h3>Download</h3>
    <ul class="downloads no-style">
      <li><a href="qc/data" class="text">Plot Data</a></li>
    </ul>
  </section>
</div>

<p class="back">&laquo;
  <a href="<?php print $backLink; ?>">Back to Station <?php print $stationName; ?></a>
</p>
