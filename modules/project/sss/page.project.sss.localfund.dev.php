พัฒนาโครงการ


จำนวนพัฒนาโครงการที่มีวัตถุประสงค์ PA

SELECT
-- t.`title`,t.tpid
"จำนวนพัฒนาโครงการมีวัตถุประสงค์ PA" `header`
,p.`pryear`+543 `ปี`
, o.`refid` `วัตถุประสงค์`
-- ,count(*) `totalPlan`
, COUNT(DISTINCT `tpid`) `TotalProject`
-- ,o.* 
FROM `sgz_project_tr` o
LEFT JOIN sgz_topic t USING(`tpid`)
LEFT JOIN sgz_project_dev p USING(`tpid`)
WHERE o.`formid` LIKE 'develop' AND o.`part` LIKE 'objective' AND o.`tagname`="project:problem:7" AND p.`pryear` BETWEEN 2018 AND 2020
-- AND o.`refid`=8
GROUP BY p.`pryear` ,`refid`
ORDER BY `pryear`,`refid` 
LIMIT 100








SELECT
a.`pryear`
, SUM(`target`) `จำนวนเป้าหมาย`
FROM (
SELECT
p.`pryear`
, SUM(`target`.`amount`) `target`
, p.`budget`
FROM `sgz_project_tr` plan
	LEFT JOIN sgz_project_dev p USING(`tpid`)
    LEFT JOIN sgz_project_target target ON target.`tpid`=plan.`tpid` AND target.`tagname`="develop"
WHERE `formid` LIKE 'develop' AND `part` LIKE 'supportplan' AND `refid`=7  AND p.`pryear` BETWEEN 2018 AND 2020
GROUP BY plan.`tpid`
    ) a
GROUP BY `pryear`
-- ORDER BY `refid`  DESC




SELECT
"กลุ่มเป้าหมายพัฒนาโครงการตามแผน PA" `header`
, a.`pryear`+543 `ปี`
, COUNT(`tpid`) `จำนวนโครงการ`
, SUM(`target`) `จำนวนเป้าหมาย`
, SUM(`budget`) `งบประมาณ`
FROM (
SELECT
p.`pryear`
, plan.`tpid`
, SUM(`target`.`amount`) `target`
, p.`budget`
FROM `sgz_project_tr` plan
	LEFT JOIN sgz_project_dev p USING(`tpid`)
    LEFT JOIN sgz_project_target target ON target.`tpid`=plan.`tpid` AND target.`tagname`="develop"
WHERE `formid` LIKE 'develop' AND `part` LIKE 'supportplan' AND `refid`=7  AND p.`pryear` BETWEEN 2018 AND 2020
GROUP BY plan.`tpid`
    ) a
GROUP BY `pryear`
-- ORDER BY `refid`  DESC


SELECT
"กลุ่มเป้าหมายพัฒนาโครงการตามแผน PA มีงบ" `header`
, a.`pryear`+543 `ปี`
, COUNT(`tpid`) `จำนวนโครงการ`
, SUM(`target`) `จำนวนเป้าหมาย`
, SUM(`budget`) `งบประมาณ`
FROM (
SELECT
p.`pryear`
, plan.`tpid`
, SUM(`target`.`amount`) `target`
, p.`budget`
FROM `sgz_project_tr` plan
	LEFT JOIN sgz_project_dev p USING(`tpid`)
    LEFT JOIN sgz_project_target target ON target.`tpid`=plan.`tpid` AND target.`tagname`="develop"
WHERE `formid` LIKE 'develop' AND `part` LIKE 'supportplan' AND `refid`=7  AND p.`pryear` BETWEEN 2018 AND 2020 AND p.`budget`>0
GROUP BY plan.`tpid`
    ) a
GROUP BY `pryear`
-- ORDER BY `refid`  DESC






