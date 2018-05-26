<?php

/* Parameters accepted:
    classId
    weekId
    weight
    aerobicMinutes
    strengthMinutes
*/

$userId = currentUserId();
if($userId == null) {
  exit_error(4);
}

$qr = select_one_record('
  select count(*) as count
  from wrc_reports
  where
    user_id = ?
    and class_id = ?
    and week_id = ?
', array($userId, $post['classId'], $post['weekId'] + 1));

if($qr['count'] == 0) {
  pdo_insert('
    insert into wrc_reports (user_id, class_id, class_source, week_id)
    values (?, ?, ?, ?)
  ', array($userId, $post['classId'], 'w', $post['weekId'] + 1));
}

pdo_update('
  update wrc_reports set
    weight = ?, 
    aerobic_minutes = ?,
    strength_minutes = ?,
    create_dttm = now()
  where
    user_id = ? and
    class_id = ? and
    week_id = ?
', array(
  $post['weight'],
  $post['aerobicMinutes'],
  $post['strengthMinutes'],
  $userId,
  $post['classId'],
  $post['weekId'] + 1
));

?>
