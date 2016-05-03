<?php

date_default_timezone_set('UTC');

$CONFIG = parse_ini_file('config.ini');

$APP_DIR = $CONFIG['APP_DIR'];
$DATA_DIR = $CONFIG['DATA_DIR'];
$DB_DSN = $CONFIG['DB_DSN'];
$DB_USER = $CONFIG['DB_USER'];
$DB_PASS = $CONFIG['DB_PASS'];
$MOUNT_PATH = $CONFIG['MOUNT_PATH'];
