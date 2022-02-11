-- Query withdraw relation to summary
SELECT t2.`part` t2_part, t2.`tpid` t2_tpid, t2.`num1` t2_num1, t2.`flag` t2_flag, t2.`date1` t2_date1, t2.`date2` t2_date2, t2.`detail4` t2_detail4, t1.*
FROM `sgz_project_tr` t1
LEFT JOIN `sgz_project_tr` t2 ON t2.`formid`="ง.1" AND t2.`part`="withdraw" AND t2.`tpid`=t1.`tpid` AND t2.`period`=t1.`period` 
WHERE t1.`formid`="ง.1" AND t1.`part`="summary"
ORDER BY t1.`tpid`
LIMIT 100;


-- Merge ง.1 part summary with withdraw
UPDATE `sgz_project_tr` t1
LEFT JOIN `sgz_project_tr` t2 ON t2.`formid`="ง.1" AND t2.`part`="withdraw" AND t2.`tpid`=t1.`tpid` AND t2.`period`=t1.`period` 
SET t1.`num10`=t2.`num1`,
t1.`flag`=t2.`flag`,
t1.`detail1`=t2.`date1`,
t1.`detail2`=t2.`date2`,
t1.`detail4`=t2.`detail4`
WHERE t1.`formid`="ง.1" AND t1.`part`="summary";

-- Delete withdraw record
DELETE FROM `sgz_project_tr` WHERE `formid`="ง.1" AND `part`="withdraw";