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
function getDirContents ($dir, $order=SCANDIR_SORT_DESCENDING) {
  $r = [];

  if (is_dir($dir)) {
    $contents = scandir($dir, $order);
    $r = array_diff($contents, array('..', '.'));
  }

  return $r;
}

/**
 * Import dynamically generated json file and store it in an array
 *
 * @param $file {String}
 *        full path to json file to import (__DIR__ magic constant is useful)
 *
 * @return {Array} json file contents
 */
function importJsonToArray ($file) {
  if (is_file($file)) {
    // Read file contents into output buffer
    ob_start();
    include $file;
    $contents = ob_get_contents();
    ob_end_clean();

    // Reset to html (gets set to JSON by included $file)
    header('Content-Type: text/html');

    return json_decode($contents, true);
  } else {
    trigger_error("importJsonToArray(): Failed opening $file for import",
      E_USER_WARNING);
  }
}

/**
 * Get a request parameter from $_GET or $_POST
 *
 * @param $name {String}
 *        The parameter name
 * @param $default {?} default is null
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

/**
 * Convert an array to a json feed and print it
 *
 * @param $array {Array}
 *        Data from db
 * @param $callback {String} default is null
 *        optional callback for jsonp requests
 */
function showJson ($array, $callback=null) {
  header('Content-Type: application/json');
  header('Access-Control-Allow-Origin: *');
  header('Access-Control-Allow-Methods: *');
  header('Access-Control-Allow-Headers: accept,origin,authorization,content-type');

  $json = json_encode($array);
  if ($callback) {
    $json = "$callback($json)";
  }
  print $json;
}
