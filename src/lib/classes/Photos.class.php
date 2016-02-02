<?php

$photos = [];
$dir = sprintf('%s/stations/%s.dir/%s/photos/screen',
  $GLOBALS['DATA_DIR'],
  substr($this->station, 0, 1),
  $this->station
);
$dir_contents = scandir($dir, SCANDIR_SORT_DESCENDING);
$photos['screen'] = array_diff($dir_contents, array('..', '.'));
return $photos;
