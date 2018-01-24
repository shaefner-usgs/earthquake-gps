<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$network = safeParam('network', 'SFBayArea');

if (!isset($TEMPLATE)) {
  $TITLE = "$network Network";
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="../css/noise.css" />';
  $FOOT = '<script src="../js/sortAndTabifyTable.js"></script>';
  $CONTACT = 'jsvarc';

  include 'template.inc.php';
}

$db = new Db;

// Db query result: noise for selected network
$rsNoise = $db->queryNoise($network);

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
    <th>N whitenoise</th>
    <th>E whitenoise</th>
    <th>U whitenoise</th>
    <th>N plamp1</th>
    <th>E plamp1</th>
    <th>U plamp1</th>
    <th>N plexp1</th>
    <th>E plexp1</th>
    <th>U plexp1</th>
    <th>N GM</th>
    <th>E GM</th>
    <th>U GM</th>
    <th>N bpfilterelement1</th>
    <th>E bpfilterelement1</th>
    <th>U bpfilterelement1</th>
    <th>N bpfilterelement2</th>
    <th>E bpfilterelement2</th>
    <th>U bpfilterelement2</th>
    <th>N numberofpoles</th>
    <th>E numberofpoles</th>
    <th>U numberofpoles</th>
    <th>N BPamplitude</th>
    <th>E BPamplitude</th>
    <th>U BPamplitude</th>
    <th>N plamp2</th>
    <th>E plamp2</th>
    <th>U plamp2</th>
    <th>N plexp2</th>
    <th>E plexp2</th>
    <th>U plexp2</th>
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
          <td>%s</td>
        </tr>',
        $row->station,
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

<h2>Noise</h2>

<div class="tablist">
  <?php print $html; ?>
</div>

<p class="back">&laquo;
  <a href="<?php print $backLink; ?>">Back to <?php print $network; ?> network</a>
</p>
