<?php
if(PHP_SAPI != 'cli') {
  exit('This script can be run from the command line only.');
}

// When creating a new environment, run this script to generate the JWT secret.
echo base64_encode(openssl_random_pseudo_bytes(64));
echo "\n";
?>