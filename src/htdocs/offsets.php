<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$networkParam = safeParam('network', 'SFBayArea');

if (!isset($TEMPLATE)) {
  $TITLE = "$networkParam Network";
  $SUBTITLE = 'Offsets';
  $TITLETAG = "$SUBTITLE | $TITLE";
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="../css/offsets.css" />';
  $FOOT = '<script src="../js/table.js"></script>';
  $CONTACT = 'jsvarc';

  include 'template.inc.php';
}

$db = new Db;

// Db query results: offsets, velocities for selected network
$rsOffsets = $db->queryOffsets($networkParam);
$rsVelocities = $db->queryVelocities($networkParam);

if ($rsOffsets->rowCount() > 0) {
  $datatypes = [
    'nafixed' => 'NA-fixed',
    'itrf2008' => 'ITRF2008',
    'filtered' => 'Filtered'
  ];

  // Create an array of last update time keyed by station name
  $lastObs = [];
  while ($row = $rsVelocities->fetch(PDO::FETCH_OBJ)) {
    $lastObs[$row->station] = $row->last_observation;
  }

  // Create html for tables
  $html = '<div class="tablist">';
  $tableHeader = '<table class="sortable">
    <tr class="no-sort">
      <th class="sort-default freeze">Station</th>
      <th class="freeze">Date</th>
      <th class="freeze">Decimal Date</th>
      <th class="freeze">N Offset</th>
      <th class="freeze">N Uncertainty</th>
      <th class="freeze">E Offset</th>
      <th class="freeze">E Uncertainty</th>
      <th class="freeze">U Offset</th>
      <th class="freeze">U Uncertainty</th>
      <th class="freeze">Type</th>
      <th class="freeze">Earthquake magnitude</th>
      <th class="freeze">Earthquake information</th>
      <th class="freeze">Distance from epicenter (km)</th>
    </tr>';
  $tableBody = [];
  $tableFooter = '</table>';

  while ($row = $rsOffsets->fetch(PDO::FETCH_OBJ)) {
    // sizes and uncertainties are comma-separated in this format: $datatype/$component:$value
    $sizeValues = [];
    $sizes = explode(',', $row->size);
    foreach ($sizes as $size) {
      // separate out constituent parts
      preg_match('@(\w+)/(E|N|U):([-\d.]+)@', $size, $matches);
      $datatype = $matches[1];
      $component = $matches[2];
      $value = $matches[3];

      $sizeValues[$datatype][$component] = $value;
    }

    $uncertaintyValues = [];
    $uncertainties = explode(',', $row->uncertainty);
    foreach ($uncertainties as $uncertainty) {
      // separate out constituent parts
      preg_match('@(\w+)/(E|N|U):([-\d.]+)@', $uncertainty, $matches);
      $datatype = $matches[1];
      $component = $matches[2];
      $value = $matches[3];

      $uncertaintyValues[$datatype][$component] = $value;
    }

    $eqinfo = '';
    if ($row->eqinfo) {
      $eqinfo = sprintf('<a href="https://earthquake.usgs.gov/earthquakes/eventpage/%s#executive">%s</a>',
        $row->eqinfo,
        $row->eqinfo
      );
    }

    foreach($datatypes as $datatype=>$name) {
      if ($sizeValues[$datatype] && $uncertaintyValues[$datatype]) { // only create table if there's data
        $tableBody[$datatype] .= sprintf('<tr>
            <th class="%s freeze link" title="Last observation: %s">
              <a href="./%s" class="%s button">%s</a>
            </th>
            <td class="nowrap">%s</td>
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
            <td>%s</td>
          </tr>',
          getColor($lastObs[$row->station]),
          date('M j, Y', strtotime($lastObs[$row->station])),
          strtolower($row->station),
          getColor($lastObs[$row->station]),
          strtoupper($row->station),
          $row->date,
          $row->decdate,
          $sizeValues[$datatype]['N'],
          $uncertaintyValues[$datatype]['N'],
          $sizeValues[$datatype]['E'],
          $uncertaintyValues[$datatype]['E'],
          $sizeValues[$datatype]['U'],
          $uncertaintyValues[$datatype]['U'],
          $row->offsettype,
          $row->eqmagnitude,
          $eqinfo,
          $row->distance_from_eq
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
      <a href="../<?php print $networkParam; ?>/velocities">Velocities and Uncertainties</a>
    </li>
    <li>
      <strong>Offsets</strong>
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
