<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$network = safeParam('network', 'SFBayArea');

if (!isset($TEMPLATE)) {
  $TITLE = "$network Network";
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="../css/velocities.css" />';
  $FOOT = '<script src="../js/velocities.js"></script>';
  $CONTACT = 'jsvarc';

  include 'template.inc.php';
}

$db = new Db;

// Db query result: velocities for selected network
$rsVelocities = $db->queryVelocities($network);

$datatypes = [
  'nafixed' => 'NA-fixed',
  'itrf2008' => 'ITRF2008',
  'filtered' => 'Filtered'
];

// Create html for tables
$tableBody = [];
while ($row = $rsVelocities->fetch(PDO::FETCH_OBJ)) {
  $positionFields = sprintf('<td>%s</td><td>%s</td><td>%s</td>',
    round($row->lon, 5),
    round($row->lat, 5),
    round($row->elevation, 5)
  );
  // sigmas/velocities stored as comma-sep values ordered by type ASC, component ASC
  $sigmas = explode(',', $row->sigmas);
  $velocities = explode(',', $row->velocities);

  $tableBody['filtered'] .= sprintf('<tr>
      <td>%s</td>
      %s
      <td>%s</td>
      <td>%s</td>
      <td>%s</td>
      <td>%s</td>
      <td>0.0000</td>
      <td>%s</td>
      <td>%s</td>
    </tr>',
    $row->station,
    $positionFields,
    $velocities[0],
    $velocities[1],
    $sigmas[0],
    $sigmas[1],
    $velocities[2],
    $sigmas[2]
  );

  $tableBody['itrf2008'] .= sprintf('<tr>
      <td>%s</td>
      %s
      <td>%s</td>
      <td>%s</td>
      <td>%s</td>
      <td>%s</td>
      <td>0.0000</td>
      <td>%s</td>
      <td>%s</td>
    </tr>',
    $row->station,
    $positionFields,
    $velocities[3],
    $velocities[4],
    $sigmas[3],
    $sigmas[4],
    $velocities[5],
    $sigmas[5]
  );

  $tableBody['nafixed'] .= sprintf('<tr>
      <td>%s</td>
      %s
      <td>%s</td>
      <td>%s</td>
      <td>%s</td>
      <td>%s</td>
      <td>0.0000</td>
      <td>%s</td>
      <td>%s</td>
    </tr>',
    $row->station,
    $positionFields,
    $velocities[6],
    $velocities[7],
    $sigmas[6],
    $sigmas[7],
    $velocities[8],
    $sigmas[8]
  );
}

$html = '';
$tableHeader = '<table class="sortable">
  <tr class="no-sort">
    <th class="sort-default">Station</th>
    <th>Longitude</th>
    <th>Latitude</th>
    <th>Elevation</th>
    <th>Velocity (E)</th>
    <th>Velocity (N)</th>
    <th>Sigma (E)</th>
    <th>Sigma (N)</th>
    <th>Correlation (N-E)</th>
    <th>Velocity (U)</th>
    <th>Sigma (U)</th>
  </tr>';
$tableFooter = '</table>';
foreach ($datatypes as $datatype => $name) {
  $html .= sprintf('<section class="panel" data-title="%s">
      <header>
        <h3>%s</h3>
      </header>
      %s
      %s
      %s
    </section>',
    $name,
    $name,
    $tableHeader,
    $tableBody[$datatype],
    $tableFooter
  );
}

$backLink = sprintf('%s/%s',
  $MOUNT_PATH,
  $network
);

?>

<h2>Velocities and Uncertainties</h2>

<div class="tablist">
  <?php print $html; ?>
</div>

<p class="back">&laquo;
  <a href="<?php print $backLink; ?>">Back to <?php print $network; ?>network</a>
</p>
