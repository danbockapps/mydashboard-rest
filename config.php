<?php
/* This file should be require_once'd */

$ini = parse_ini_file('auth.ini');

/*
You can refer to the keys in auth.ini as all-caps constants (e.g. WEBSITE_URL)
or as keys of $ini (e.g. $ini['website_url']). Constants are usually easier.
*/
foreach($ini as $key => $value) {
  define(strtoupper($key), $value);
}

require_once('functions.php');

?>
