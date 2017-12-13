<?php
$qr = pdo_select("
  select
    user_id,
    password,
    activation,
    fname,
    lname
  from wrc_users
  where email = ?
", array($post['email']));

if(empty($qr))
  exit_error(1);

if($qr[0]['activation'] != null)
  exit_error(2);

$salt = substr($qr[0]['password'], 7, 21);
$in_hashd_passwd = crypt($post['password'], BLOWFISH_PRE . $salt . BLOWFISH_SUF);

if($qr[0]['password'] != $in_hashd_passwd)
  exit_error(3);

else {
  // Login successful
  $ok_array['token'] = getToken($qr[0]['user_id']);
  
  $ok_array['fname'] = $qr[0]['fname'];
  $ok_array['lname'] = $qr[0]['lname'];
}

?>
