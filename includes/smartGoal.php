<?php
if($_SERVER['REQUEST_METHOD'] == 'GET') {
  $ok_array['smartGoal'] = getSmartGoal($ok_array['userId']);
}

else if($_SERVER['REQUEST_METHOD'] == 'POST') {
  postSmartGoal($ok_array['userId'], getSmartGoal($ok_array['userId']));
}

else {
  exit_error(5);
}

function getSmartGoal($userId) {
  // Returns null if there's no record
  return pdo_select('
    select e.smart_goal
    from
      enrollment_view e
      inner join current_classes c
        on e.class_id = c.class_id
    where
      e.user_id = ?
      and
        /* most recent class for this participant */
        start_dttm in (
          select max(start_dttm)
          from
            enrollment_view e
            inner join current_classes c
              on e.class_id = c.class_id
          where user_id = ?
        )
  ', array($userId, $userId))[0][smart_goal];
}

function postSmartGoal($userId, $oldSmartGoal) {
  global $post;

  $classId = current_class_by_user($userId)[class_id];

  $dbSuccess = pdo_update('
    update ' . ENROLLMENT_TABLE . '
    set smart_goal = ?
    where tracker_user_id = ?
      and class_id = ?
  ', array($post['goal'], $userId, $classId));

  if($dbSuccess) {
    add_sg_to_messages($oldSmartGoal, $post['goal']);
  }
}

function add_sg_to_messages($old_sg, $new_sg) {
   $msg = "New SMART Goal:\n" . $new_sg;
   if($old_sg) {
      $msg .= "\n\nOld SMART Goal:\n" . $old_sg;
   }
   $dbh = pdo_connect(DB_PREFIX . '_insert');
   $sth = $dbh->prepare("
      insert into wrc_messages (user_id, recip_id, message, create_dttm)
      values (?, ?, ?, now())
   ");
   return $sth->execute(array($_SESSION['user_id'], $_SESSION['user_id'], $msg));
}

?>
