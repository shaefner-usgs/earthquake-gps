<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$network = safeParam('network', 'SFBayArea');

if (!isset($TEMPLATE)) {
  $TITLE = "$network Network - Velocities and Uncertainties";
  $NAVIGATION = true;
  //$HEAD = '<link rel="stylesheet" href="../css/base.css" />';
  $CONTACT = 'jsvarc';

  include_once 'template.inc.php';
}

$db = new Db;

// Db query result: past 14 records of QC data
$rsVelocities = $db->queryVelocities($network);

// Create html for table body
$table_body_html = '';
while($row = $rsVelocities->fetch(PDO::FETCH_ASSOC)) {
  $table_body_html .= sprintf("<tr>
      <td>%s</td>
      <td>%s</td>
      <td>%s</td>
      <td>%s</td>
      <td>%s</td>
      <td>%s</td>
    </tr>\n",
    $row['velocity'],
    $row['sigma'],
    $row['rms'],
    $row['whitenoise'],
    $row['randomwalk'],
    $row['flickernoise']
  );
}

$backLink = sprintf('%s/%s',
  $MOUNT_PATH,
  $network
);

?>

<h2>Past 14 observations</h2>

<table>
  <tr>
    <th>Velocity (mm/yr)</th>
    <th>Uncertainty (mm/yr)</th>
    <th>RMS (mm)</th>
    <th>White Noise</th>
    <th>Random Walk</th>
    <th>Flicker Noise</th>
  </tr>

<?php print $table_body_html; ?>

</table>

<p class="back">&laquo;
  <a href="<?php print $backLink; ?>">Back to <?php print $network; ?>network</a>
</p>
