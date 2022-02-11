<?php
function imed_admin_calculate_adl() {
  $stmt='INSERT INTO `sgz_imed_qt` (`pid`,`part`)
    SELECT c.`pid`,"ELDER.GROUP"
      FROM %imed_care% c
        LEFT JOIN %imed_qt% q ON q.`pid`=c.`pid` AND q.`part`="ELDER.GROUP"
      WHERE c.`careid`=2 AND q.`pid` IS NULL;

    INSERT INTO %imed_qt% (`pid`,`part`)
    SELECT c.`pid`,"ADL"
      FROM %imed_care% c
        LEFT JOIN %imed_qt% q ON q.`pid`=c.`pid` AND q.`part`="ADL"
      WHERE c.`careid`=2 AND q.`pid` IS NULL;

    UPDATE %imed_qt% p
    LEFT JOIN ( SELECT q.pid, SUM(q.`value`) AS sum_attr,COUNT(*) amt
             FROM %imed_qt% q
            WHERE q.`part` LIKE "ADL-%"
            GROUP BY q.`pid`
            HAVING amt>0
         ) r
      ON r.pid = p.pid
     SET p.`value` = r.sum_attr
     WHERE p.`part`="ADL";

    UPDATE %imed_qt% p
    LEFT JOIN %imed_qt% q ON q.`pid`=p.`pid` AND q.`part`="ADL"
      SET p.`value`=
        CASE
            WHEN CAST(q.`value` AS UNSIGNED) <=4 THEN 1
            WHEN CAST(q.`value` AS UNSIGNED) <=11 THEN 2
            WHEN CAST(q.`value` AS UNSIGNED) >=12 THEN 3
          END
     WHERE p.`part`="ELDER.GROUP"';
  mydb::query($stmt);
}
?>