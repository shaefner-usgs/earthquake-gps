<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$networkParam = safeParam('network', 'SFBayArea');

if (!isset($TEMPLATE)) {
  $TITLE = "$networkParam Network";
  $SUBTITLE = 'Noise';
  $TITLETAG = "$SUBTITLE | $TITLE";
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="../css/noise.css" />';
  $FOOT = '<script src="../js/table.js"></script>';
  $CONTACT = 'jsvarc';

  include 'template.inc.php';
}

$db = new Db;

// Db queries: noise, velocities for selected network
$rsNoise = $db->queryNoise($networkParam);
$rsVelocities = $db->queryVelocities($networkParam);

if ($rsNoise->rowCount() > 0) {
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
      <th class="freeze">N whitenoise</th>
      <th class="freeze">E whitenoise</th>
      <th class="freeze">U whitenoise</th>
      <th class="freeze">N plamp1</th>
      <th class="freeze">E plamp1</th>
      <th class="freeze">U plamp1</th>
      <th class="freeze">N plexp1</th>
      <th class="freeze">E plexp1</th>
      <th class="freeze">U plexp1</th>
      <th class="freeze">N GM</th>
      <th class="freeze">E GM</th>
      <th class="freeze">U GM</th>
      <th class="freeze">N bpfilterelement1</th>
      <th class="freeze">E bpfilterelement1</th>
      <th class="freeze">U bpfilterelement1</th>
      <th class="freeze">N bpfilterelement2</th>
      <th class="freeze">E bpfilterelement2</th>
      <th class="freeze">U bpfilterelement2</th>
      <th class="freeze">N numberofpoles</th>
      <th class="freeze">E numberofpoles</th>
      <th class="freeze">U numberofpoles</th>
      <th class="freeze">N BPamplitude</th>
      <th class="freeze">E BPamplitude</th>
      <th class="freeze">U BPamplitude</th>
      <th class="freeze">N plamp2</th>
      <th class="freeze">E plamp2</th>
      <th class="freeze">U plamp2</th>
      <th class="freeze">N plexp2</th>
      <th class="freeze">E plexp2</th>
      <th class="freeze">U plexp2</th>
    </tr>';
  $tableBody = [];
  $tableFooter = '</table>';

  $fieldsToExpand = ['whitenoise', 'plamp1', 'plexp1', 'GM', 'bpfilterelement1',
    'bpfilterelement2', 'numberofpoles', 'BPamplitude', 'plamp2', 'plexp2'
  ];

  while ($row = $rsNoise->fetch(PDO::FETCH_OBJ)) {
    // Data is comma-separated in this format: $datatype/$component:$value, ...
    foreach($fieldsToExpand as $field) {
      $values[$field] = [];
      $items = explode(',', $row->$field);
      foreach ($items as $item) {
        // separate out constituent parts
        preg_match('@(\w+)/(E|N|U):([-\d.]+)@', $item, $matches);
        $datatype = $matches[1];
        $component = $matches[2];
        $value = $matches[3];

        $values[$field][$datatype][$component] = $value;
      }
    }

    foreach($datatypes as $datatype=>$name) {
      if ($values['whitenoise'][$datatype]) { // only create table if there's data
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
            <td>%s</td>
          </tr>',
          getColor($lastObs[$row->station]),
          date('M j, Y', strtotime($lastObs[$row->station])),
          strtolower($row->station),
          getColor($lastObs[$row->station]),
          strtoupper($row->station),
          $values['whitenoise'][$datatype]['N'],
          $values['whitenoise'][$datatype]['E'],
          $values['whitenoise'][$datatype]['U'],
          $values['plamp1'][$datatype]['N'],
          $values['plamp1'][$datatype]['E'],
          $values['plamp1'][$datatype]['U'],
          $values['plexp1'][$datatype]['N'],
          $values['plexp1'][$datatype]['E'],
          $values['plexp1'][$datatype]['U'],
          $values['GM'][$datatype]['N'],
          $values['GM'][$datatype]['E'],
          $values['GM'][$datatype]['U'],
          $values['bpfilterelement1'][$datatype]['N'],
          $values['bpfilterelement1'][$datatype]['E'],
          $values['bpfilterelement1'][$datatype]['U'],
          $values['bpfilterelement2'][$datatype]['N'],
          $values['bpfilterelement2'][$datatype]['E'],
          $values['bpfilterelement2'][$datatype]['U'],
          $values['numberofpoles'][$datatype]['N'],
          $values['numberofpoles'][$datatype]['E'],
          $values['numberofpoles'][$datatype]['U'],
          $values['BPamplitude'][$datatype]['N'],
          $values['BPamplitude'][$datatype]['E'],
          $values['BPamplitude'][$datatype]['U'],
          $values['plamp2'][$datatype]['N'],
          $values['plamp2'][$datatype]['E'],
          $values['plamp2'][$datatype]['U'],
          $values['plexp2'][$datatype]['N'],
          $values['plexp2'][$datatype]['E'],
          $values['plexp2'][$datatype]['U']
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
      <a href="../<?php print $networkParam; ?>/offsets">Offsets</a>
    </li>
    <li>
      <strong>Noise</strong>
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
