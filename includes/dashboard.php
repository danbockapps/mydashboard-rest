<?php

$ok_array['weight'] = pdo_select('
  select
    week_id,
    weight
  from
    reports_with_fitbit_hybrid r
    inner join current_classes c
      on r.class_id = c.class_id
  where r.user_id = ?
', $ok_array['userId']);

?>
