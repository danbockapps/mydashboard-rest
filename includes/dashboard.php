<?php

$ok_array['weight'] = pdo_select('
  select
    r.week_id - 1 as week, /* week IDs are zero-based from now on */
    r.weight
  from
    reports_with_fitbit_hybrid r
    inner join current_classes c
      on r.class_id = c.class_id
  where r.user_id = ?
', $ok_array['userId']);

$ok_array['class'] = current_class_by_user($ok_array['userId']);

?>
