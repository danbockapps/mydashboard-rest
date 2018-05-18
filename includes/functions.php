<?php
require_once(dirname(__FILE__) . '/../library/jwt_helper.php');

function logtxt($string) {
  global $ok_array;
  file_put_contents(
    LOG_FILE,
    (isset($ok_array['userId']) ? $ok_array['userId'] . ' ' : '') .
    date("Y-m-d H:i:s") . " " .
    $_SERVER['REMOTE_ADDR'] . " " .
    $string . "\n",
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
  if($responsecode == 1) {
    http_response_code(401);
    $returnable['explanation'] = "There is no account with that email address.";
  }
  if($responsecode == 2) {
    http_response_code(401);
    $returnable['explanation'] = "Your account is not activated.";
  }
  if($responsecode == 3) {
    http_response_code(401);
    $returnable['explanation'] = "Incorrect password.";
  }
  if($responsecode == 4) {
    http_response_code(400);
    $returnable['explanation'] = "Invalid token.";
  }

  exit(json_encode($returnable));
}

function getToken($userId) {
  return JWT::encode(
    json_encode((object)['userId' => $userId, 'iat' => time()]),
    base64_decode(JWT_SECRET),
    'HS512'
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
  try {
    $decoded = JWT::decode($token, base64_decode(JWT_SECRET));
  }
  catch (Exception $e) {
    logtxt('JWT exception: ' . $e);
    exit_error(4);
  }
  $obj = json_decode($decoded);
  // $obj has the issued-at date ($obj['iat']) in case we ever want to do
  // anything with that.
  return $obj->userId;
}

function removePassword($s) {
  $obj = json_decode($s);
  if(isset($obj->password)) {
    $obj->password = 'zzzzzzzz';
    return json_encode($obj);
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

function currentInstructor($participantId) {
  $qr = pdo_select('
    select c.instructor_id
    from
      enrollment_view e
      natural join current_classes c
    where
      e.user_id = ? and
      start_dttm in (
        select max(start_dttm)
        from
           enrollment_view e
           natural join current_classes
        where user_id = ?
     )
  ', array($participantId, $participantId));

  return $qr[0]['instructor_id'];
}

?>
