<?php

//TODO Refactor the weight query and the class query to two separate files.
// We do need class data on app startup, but not subsequent times this is
// called (e.g. from postNewData).

$ok_array['data'] = pdo_select('
  select
    r.week_id - 1 as week, /* week IDs are zero-based from now on */
    r.weight,
    r.aerobic_minutes,
    r.strength_minutes,
    r.avgsteps
  from
    reports_with_fitbit_hybrid r
    inner join current_classes c
      on r.class_id = c.class_id
  where r.user_id = ?
', $ok_array['userId']);

$ok_array['class'] = current_class_by_user($ok_array['userId']);

?>
