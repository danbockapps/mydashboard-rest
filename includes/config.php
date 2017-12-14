<?php
/* This file should be require_once'd */

CRYPT_BLOWFISH or die ('No Blowfish found.');
define("BLOWFISH_PRE", "$2y$05$");
define("BLOWFISH_SUF", "$");
date_default_timezone_set('America/New_York');

$ini = parse_ini_file(dirname(__FILE__) . '/../auth.ini');

/*
You can refer to the keys in auth.ini as all-caps constants (e.g. WEBSITE_URL)
or as keys of $ini (e.g. $ini['website_url']). Constants are usually easier.
*/
foreach($ini as $key => $value) {
  define(strtoupper($key), $value);
}

require_once(dirname(__FILE__) . '/functions.php');

?>
