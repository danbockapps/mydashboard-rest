<?php
function logtxt($string) {
  file_put_contents(
    LOG_FILE,
    date("Y-m-d G:i:s") . " " . $_SERVER['REMOTE_ADDR'] . " " .
        $_SESSION['userid'] . " " . $string . "\n",
    FILE_APPEND
  );
}

function exit_error($responsecode) {
  global $start_time, $contents;
  logtxt(
    number_format(microtime(true) - $start_time, 4) .
    " " .
    json_encode($_GET) .
    " " .
    removePassword($contents) .
    " ERROR" .
    $responsecode
  );

  $returnable['q'] = $_GET['q'];
  $returnable['responseString'] = "ERROR";
  $returnable['responseCode'] = $responsecode;

  // None of these "explanations" is displayed to the user; they are here to
  // make the code and JSON more human-readable.
  if($responsecode == 1)
    $returnable['explanation'] = "There is no account with that email address.";
  if($responsecode == 2)
    $returnable['explanation'] = "Your account is not activated.";
  if($responsecode == 3)
    $returnable['explanation'] = "Incorrect password.";

  exit(json_encode($returnable));
}

function getToken($userId) {
  return JWT::encode(
    json_encode((object)['userId' => $userId]),      //Data to be encoded in the JWT
    base64_decode(JWT_SECRET), // The signing key
    'HS512'     // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
  );
}

function currentUserId() {
  if(!isset($_SERVER['HTTP_AUTHORIZATION'])) {
    return null;
  }
  else {
    list($token) = sscanf($_SERVER['HTTP_AUTHORIZATION'], 'Bearer %s');
    if($token) {
      return getUserId($token);
    }
    else {
      return null;
    }
  }
}

function getUserId($token) {
  $decoded = JWT::decode($token, base64_decode(JWT_SECRET));
  $obj = json_decode($decoded);
  return $obj->userId;
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

function pdo_select($query, $qs) {
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
   $email_row = pdo_select("
      select count(*) as count
      from wrc_users
      where email = ?
   " . $noreg_clause, array($email));
   return $email_row[0]['count'];
}

?>
