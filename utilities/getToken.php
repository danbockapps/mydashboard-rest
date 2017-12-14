<?php
if(PHP_SAPI != 'cli') {
  exit('This script can be run from the command line only.');
}
if(!isset($argv[1])) {
  exit("Must include user ID as command line argument.\n");
}

require_once('../includes/config.php');

echo getToken($argv[1]);
echo "\n";
?>