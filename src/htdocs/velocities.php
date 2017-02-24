<?php

include_once '../conf/config.inc.php'; // app config
include_once '../lib/_functions.inc.php'; // app functions
include_once '../lib/classes/Db.class.php'; // db connector, queries

$network = safeParam('network', 'SFBayArea');

if (!isset($TEMPLATE)) {
  $TITLE = "$network Network";
  $NAVIGATION = true;
  $HEAD = '<link rel="stylesheet" href="../css/velocities.css" />';
  $CONTACT = 'jsvarc';

  include 'template.inc.php';
}

$db = new Db;

// Db query result: velocities for selected network
$rsVelocities = $db->queryVelocities($network);

// Create velocities array from db result that is more friendly for parsing
$velocities = $db->createVelocitiesArray($rsVelocities);

$fields = [
  'velocity',
  'sigma',
  'rms',
  'whitenoise',
  'randomwalk',
  'flickernoise'
];

// Create html for table
$table_html = '<table>';

// Table header
$th = '<tr><td class="empty"></td><th>Type</th>';
foreach ($fields as $field) {
  $name = $velocities['lookup'][$field];
  $th .= "<th>$name</th>";
}
$th .= '</tr>';

// Table body
foreach ($velocities['data'] as $station => $types) {
  // add station name and table header
  $table_html .= '<tr class="station"><td colspan="8"><h3>Station ' .
    strtoupper($station) . '</h3></td></tr>';
  $table_html .= $th;

  foreach ($types as $type => $components) {
    if ($type !== 'filtered') { // don't show filtered data
      foreach ($components as $direction => $data) {
        // add component and type
        $tr = "<tr><th>$direction</th>";
        if ($direction === 'north') {
          $tr .= '<td rowspan="3">' . $type . '</td>';
        }
        // add data fields (or '-' for no data)
        foreach ($fields as $field) {
          if (array_key_exists($field, $data)) {
            $tr .= '<td>' . $data[$field] . '</td>';
          } else {
            $tr .=  '<td class="novalue">&ndash;</td>';
          }
        }
        $tr .= "</tr>";
        $table_html .= $tr;
      }
    }
  }
}

$table_html .= '</table>';

$backLink = sprintf('%s/%s',
  $MOUNT_PATH,
  $network
);

?>

<h2>Velocities and Uncertainties</h2>

<?php print $table_html; ?>

<p class="back">&laquo;
  <a href="<?php print $backLink; ?>">Back to <?php print $network; ?>network</a>
</p>
