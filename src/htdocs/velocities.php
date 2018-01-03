<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$network = safeParam('network', 'SFBayArea');

if (!isset($TEMPLATE)) {
  $TITLE = "$network Network";
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="../css/velocities.css" />';
  $FOOT = '<script src="../js/sortAndTabifyTable.js"></script>';
  $CONTACT = 'jsvarc';

  include 'template.inc.php';
}

$now = date(DATE_RFC2822);
$secs = 86400; // secs in one day

$db = new Db;

// Db query result: velocities for selected network
$rsVelocities = $db->queryVelocities($network);

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
$tableBody = [];
$tableFooter = '</table>';

while ($row = $rsVelocities->fetch(PDO::FETCH_OBJ)) {
  $days = floor((strtotime($now) - strtotime($row->last_observation)) / $secs);
  $color = getColor($days);

  // sigmas/velocities are comma-separated in this format: $datatype/$component:$value
  $sigmaValues = [];
  $sigmas = explode(',', $row->sigmas);
  foreach ($sigmas as $sigma) {
    // separate out constituent parts
    preg_match('@(\w+)/(E|N|U):([-\d.]+)@', $sigma, $matches);
    $datatype = $matches[1];
    $component = $matches[2];
    $value = $matches[3];

    $sigmaValues[$datatype][$component] = $value;
  }

  $velocityValues = [];
  $velocities = explode(',', $row->velocities);
  foreach ($velocities as $velocity) {
    // separate out constituent parts
    preg_match('@(\w+)/(E|N|U):([-\d.]+)@', $velocity, $matches);
    $datatype = $matches[1];
    $component = $matches[2];
    $value = $matches[3];

    $velocityValues[$datatype][$component] = $value;
  }

  foreach($datatypes as $datatype=>$name) {
    if ($sigmaValues[$datatype] && $velocityValues[$datatype]) { // only create table if there's data
      $tableBody[$datatype] .= sprintf('<tr>
          <td class="%s">%s</td>
          <td>%s</td>
          <td>%s</td>
          <td>%s</td>
          <td>%s</td>
          <td>%s</td>
          <td>%s</td>
          <td>%s</td>
          <td>0.0000</td>
          <td>%s</td>
          <td>%s</td>
        </tr>',
        $color,
        $row->station,
        round($row->lon, 5),
        round($row->lat, 5),
        round($row->elevation, 5),
        $velocityValues[$datatype]['E'],
        $velocityValues[$datatype]['N'],
        $sigmaValues[$datatype]['E'],
        $sigmaValues[$datatype]['N'],
        $velocityValues[$datatype]['U'],
        $sigmaValues[$datatype]['U']
      );
    }
  }
}

foreach ($datatypes as $datatype => $name) {
  if ($tableBody[$datatype]) {
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
  <a href="<?php print $backLink; ?>">Back to <?php print $network; ?> network</a>
</p>
