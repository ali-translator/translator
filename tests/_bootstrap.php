<?php
/**
 * For run all test, start command "./vendor/bin/phpunit" from root directory
 */

define('TEST_DATA_PATH', __DIR__ . DIRECTORY_SEPARATOR . '_data');
define('SOURCE_CSV_PATH', TEST_DATA_PATH . DIRECTORY_SEPARATOR . 'source' . DIRECTORY_SEPARATOR . 'csv');
define('SOURCE_MYSQL_DNS', 'mysql:dbname=test;host=mysql');
define('SOURCE_MYSQL_USER', 'root');
define('SOURCE_MYSQL_PASSWORD', 'root');

// ensure we get report on all possible php errors
error_reporting(E_ALL);

$composerAutoload = __DIR__ . '/../vendor/autoload.php';
if (!is_file($composerAutoload)) {
    die('You need to set up the project dependencies using Composer');
}
require_once $composerAutoload;
