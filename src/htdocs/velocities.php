<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$networkParam = safeParam('network', 'SFBayArea');

if (!isset($TEMPLATE)) {
  $TITLE = "$networkParam Network";
  $SUBTITLE = 'Velocities and Uncertainties';
  $TITLETAG = "$SUBTITLE | $TITLE";
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="../css/velocities.css" />';
  $FOOT = '<script src="../js/table.js"></script>';
  $CONTACT = 'jsvarc';

  include 'template.inc.php';
}

$db = new Db;

// Db query: velocities for selected network
$rsVelocities = $db->queryVelocities($networkParam);

if ($rsVelocities->rowCount() > 0) {
  $datatypes = [
    'nafixed' => 'NA-fixed',
    'itrf2008' => 'ITRF2008',
    'filtered' => 'Filtered'
  ];

  // Create html for tables
  $html = '<div class="tablist">';
  $tableHeader = '<table class="sortable">
    <tr class="no-sort">
      <th class="sort-default freeze">Station</th>
      <th class="freeze">Longitude</th>
      <th class="freeze">Latitude</th>
      <th class="freeze">Elevation</th>
      <th class="freeze">Velocity (E)</th>
      <th class="freeze">Velocity (N)</th>
      <th class="freeze">Sigma (E)</th>
      <th class="freeze">Sigma (N)</th>
      <th class="freeze">Correlation (N-E)</th>
      <th class="freeze">Velocity (U)</th>
      <th class="freeze">Sigma (U)</th>
    </tr>';
  $tableBody = [];
  $tableFooter = '</table>';

  while ($row = $rsVelocities->fetch(PDO::FETCH_OBJ)) {
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
            <th class="%s freeze link" title="Last observation: %s">
              <a href="./%s" class="%s button">%s</a>
            </th>
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
          getColor($row->last_observation),
          date('M j, Y', strtotime($row->last_observation)),
          strtolower($row->station),
          getColor($row->last_observation),
          strtoupper($row->station),
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
          <div class="scroll-wrapper">
          %s
          %s
          %s
          </div>
        </section>',
        $name,
        $name,
        $tableHeader,
        $tableBody[$datatype],
        $tableFooter
      );
    }
  }

  $html .= '</div>';
} else {
  $html = '<p class="alert info">No Data</p>';
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
      <strong>Velocities and Uncertainties</strong>
    </li>
    <li>
      <a href="../<?php print $networkParam; ?>/offsets">Offsets</a>
    </li>
    <li>
      <a href="../<?php print $networkParam; ?>/notupdated">Stations Not Updated in the Past 7 Days</a>
    </li>
  </ul>
</nav>

<?php print $html; ?>

<p class="back">&laquo;
  <a href="<?php print $backLink; ?>">Back to Station Map</a>
</p>
