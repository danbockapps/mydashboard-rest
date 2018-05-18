<?php

$instructorId = currentInstructor($ok_array['userId']);

//TODO add SMART goals and feedback
$ok_array['messages'] = pdo_select('
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
  $ok_array['userId'],
  $instructorId,
  $ok_array['userId'],
  $instructorId
));

?>