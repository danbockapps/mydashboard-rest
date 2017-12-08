<?php
function logtxt($string) {
  file_put_contents(
    LOG_FILE,
    date("Y-m-d G:i:s") . " " . $_SERVER['REMOTE_ADDR'] . " " .
        $_SESSION['userid'] . " " . $string . "\n",
    FILE_APPEND
  );
}

function removePassword($s) {
  $passwordPos = strpos($s, "password");
  if($passwordPos) {
    $openingQuotePos = $passwordPos + 10;
    $closingQuotePos = $openingQuotePos +
        strpos(substr($s, $openingQuotePos + 1), '"');
    return
      substr($s, 0, $openingQuotePos + 1) .
      "xxxxxxxx" .
      substr($s, $closingQuotePos + 1);
  }
  else {
    return $s;
  }
}

function is_email_address($email) {
   return preg_match(
      "/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@" .
      "([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/",
      $email
   );
}

function pdo_connect($db_user) {
   global $ini;
   $password = $ini[$db_user . '_password'];
   try {
      $dbh = new PDO(
         "mysql:host=" . $ini['db_host'] . ";dbname=" . DATABASE_NAME . ";charset=utf8",
         $db_user,
         $password
      );
      $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
   }
   catch(PDOException $e) {
      echo $e->getMessage();
   }
   return $dbh;
}

function pdo_seleqt($query, $qs) {
   if(!is_array($qs)) {
      $qs = array($qs);
   }
   global $ini;
   $dbh = pdo_connect(DB_PREFIX . "_select");
   $sth = $dbh->prepare($query);
   $sth->setFetchMode(PDO::FETCH_ASSOC);
   $sth->execute($qs);
   return $sth->fetchAll();
}

function email_already_in_db($email, $include_noreg=true) {
   if(!$include_noreg) {
      $noreg_clause = " and password != 'TRACKER_NO_REG'";
   }
   else {
      $noreg_clause = "";
   }
   $email_row = pdo_seleqt("
      select count(*) as count
      from wrc_users
      where email = ?
   " . $noreg_clause, array($email));
   return $email_row[0]['count'];
}

?>
