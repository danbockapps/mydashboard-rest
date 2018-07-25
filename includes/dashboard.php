<?php

//TODO Refactor the weight query and the class query to two separate files.
// We do need class data on app startup, but not subsequent times this is
// called (e.g. from postNewData).

$ok_array['data'] = pdo_select('
  select
    /* http://nikolaynaychov.blogspot.com/2012/08/how-to-fix-mysql-error-bigint-unsigned.html */
    cast(r.week_id as signed) - 1 as week, /* week IDs are zero-based from now on */
    r.weight,
    r.aerobic_minutes,
    r.strength_minutes,
    r.avgsteps
  from
    reports_with_fitbit_hybrid r
    inner join current_classes c
      on r.class_id = c.class_id
  where
    r.user_id = ?
    and r.week_id > 0
', $ok_array['userId']);

$ok_array['class'] = current_class_by_user($ok_array['userId']);

?>
