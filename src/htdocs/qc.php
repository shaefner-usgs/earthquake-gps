<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions

$network = safeParam('network', 'SFBayArea');
$station = safeParam('station', 'p271');

$name = strtoupper($station);

if (!isset($TEMPLATE)) {
  $TITLE = "$network Network";
  $SUBTITLE = "Station $name Quality Control Data";
  $TITLETAG = "$TITLE | $SUBTITLE";
  $NAVIGATION = true;
  $HEAD = '
    <link rel="stylesheet" href="../../lib/c3/c3.css" />
    <link rel="stylesheet" href="../../css/qc.css" />
  ';
  $FOOT = '
    <script>
      var MOUNT_PATH = "' . $MOUNT_PATH . '",
          NETWORK = "' . $network . '",
          STATION = "' . $station . '";
    </script>
    <script src="../../lib/d3/d3.js"></script>
    <script src="../../lib/c3/c3.js"></script>
    <script src="../../js/qc.js"></script>
  ';
  $CONTACT = 'jsvarc';

  include 'template.inc.php';
}

$backLink = sprintf('%s/%s/%s',
  $MOUNT_PATH,
  $network,
  $station
);

print '<h2 class="subtitle">' . $SUBTITLE . '</h2>';

?>

<ul class="pipelist no-style">
  <li><strong>Plots</strong></li>
  <li><a href="./qc/table">Table</a></li>
</ul>

<div class="qcdata">
  <div id="application">
    <noscript>
      <p class="alert info">Javascript must be enabled to view these plots.</p>
    </noscript>
  </div>
</div>

<p class="back">&laquo;
  <a href="<?php print $backLink; ?>">Back to Station <?php print $name; ?></a>
</p>
