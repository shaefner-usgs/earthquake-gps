<?php

if (!isset($TEMPLATE)) {
  $TITLE = 'Deleted Points';
  $NAVIGATION = true;
  $CONTACT = 'jsvarc';

  include 'template.inc.php';
}

$params = [
  'network' => $_GET['network'],
  'station' => $_GET['station'],
  'type' => $_GET['type']
];

print '<pre>';
  print_r($params);
print '</pre>';

?>
