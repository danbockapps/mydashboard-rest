<?php
$instructorId = currentInstructor($ok_array['userId']);

if($_SERVER['REQUEST_METHOD'] == 'GET') {
  $ok_array['messages'] = getMessages($ok_array['userId'], $instructorId);
}

else if($_SERVER['REQUEST_METHOD'] == 'POST') {
  postMessage($ok_array['userId'], $instructorId);
}

else {
  exit_error(5);
}

function getMessages($userId, $instructorId) {
  //TODO add SMART goals and feedback
  return pdo_select('
    select
      user_id,
      recip_id,
      message,
      create_dttm
    from wrc_messages
    where
      (user_id = ? and recip_id = ?) or
      (recip_id = ? and user_id = ?)
    order by create_dttm desc
    limit 50
  ', array(
    $userId,
    $instructorId,
    $userId,
    $instructorId
  ));
}

function postMessage($userId, $instructorId) {
  global $post;

  if(strlen($post['message']) > 99999) {
    exit_error(6);
  }

  $success = pdo_insert('
    insert into wrc_messages (user_id, recip_id, message, create_dttm)
    values (?, ?, ?, now())
  ', array($userId, $instructorId, $post['message']));

  if($success) {
    sendById($instructorId, 2, $userId);
    updateUserTable($userId, $instructorId);
  }
  else {
    exit_error(7);
  }
}

function updateUserTable($userId) {
  pdo_update('
    update wrc_users
    set last_message_from = now()
    where user_id = ?
  ', $userId);
}

?>
