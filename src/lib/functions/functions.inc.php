<?php

/**
 * Get directory contents (checks first if it exists and doesn't return .., .)
 *
 * @param $dir {String}
 *        directory to scan
 * @param $order {Integer} default is SCANDIR_SORT_DESCENDING
 *
 * @return $r {Array}
 */
function getContents ($dir, $order=SCANDIR_SORT_DESCENDING) {
  $r = [];

  if (file_exists($dir)) {
    $contents = scandir($dir, $order);
    $r = array_diff($contents, array('..', '.'));
  }

  return $r;
}

/**
 * Get a request parameter from $_GET or $_POST.
 *
 * @param $name {String}
 *        The parameter name.
 * @param $default {?} default is null.
 *        Optional default value if the parameter was not provided.
 * @param $filter {PHP Sanitize filter} default is FILTER_SANITIZE_STRING
 *        Optional sanitizing filter to apply
 * @return $value {String}
 */
function safeParam ($name, $default=null, $filter=FILTER_SANITIZE_STRING) {
  $value = null;

  if (isset($_POST[$name])) {
    $value = filter_input(INPUT_POST, $name, $filter);
  } else if (isset($_GET[$name])) {
    $value = filter_input(INPUT_GET, $name, $filter);
  } else {
    $value = $default;
  }

  return $value;
}
