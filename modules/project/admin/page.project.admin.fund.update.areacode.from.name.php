<?php
/**
* Module Method
* Created 2020-07-21
* Modify  2020-07-21
*
* @param Object $self
* @return String
*/

$debug = true;

function project_admin_fund_update_areacode_from_name($self) {
	$ret = '';

-- SELECT `orgid`,LEFT(`areacode`,6),SUBSTR(`areacode`,7,2)
-- FROM `sgz_db_org`
UPDATE `sgz_db_org` SET `areacode` = LEFT(`areacode`,6)
WHERE SUBSTR(`areacode`,7,2) = "00"
ORDER BY `areacode`  DESC;

-- SELECT `orgid`,LEFT(`areacode`,6),SUBSTR(`areacode`,7,2)
-- FROM `sgz_db_org`
UPDATE `sgz_db_org` SET `areacode` = LEFT(`areacode`,4)
WHERE SUBSTR(`areacode`,5,2) = "00"
ORDER BY `areacode`  DESC

SELECT
f.`orgid`,f.`areaid`,f.`changwat`,f.`ampur`,f.`tambon`
,cop.`provid`,cod.`distid`
,f.`namechangwat`,f.`nameampur`,f.`fundname` FROM `sgz_project_fund` f
LEFT JOIN `sgz_co_province` cop ON cop.`provid` = f.`changwat`
LEFT JOIN `sgz_co_district` cod ON LEFT(cod.`distid`,2)=f.`changwat` AND cod.`distname`=f.`nameampur`
WHERE cod.`distname` IS NOT NULL AND CONCAT(f.`changwat`,f.`ampur`)!=cod.`distid`
ORDER BY f.`changwat`
-- LIMIT 1000


-- SELECT
-- f.`orgid`,f.`areaid`,f.`changwat`,f.`ampur`,f.`tambon`,f.`areacode`
-- ,cop.`provid`,cod.`distid`,SUBSTR(cod.`distid`,3,2)
-- ,f.`namechangwat`,f.`nameampur`,f.`fundname`
-- FROM `sgz_project_fund` f
 UPDATE `sgz_project_fund` f
LEFT JOIN `sgz_db_org` o USING(`orgid`)
LEFT JOIN `sgz_co_district` cod ON LEFT(cod.`distid`,2)=f.`changwat` AND cod.`distname`=f.`nameampur`
 SET
 f.`ampur` = SUBSTR(cod.`distid`,3,2)
 , o.`ampur` = SUBSTR(cod.`distid`,3,2)
 , o.`areacode`=CONCAT(o.`changwat`,IFNULL(SUBSTR(cod.`distid`,3,2),""),IFNULL(o.`tambon`,""))
WHERE cod.`distid` IS NOT NULL
-- AND CONCAT(f.`changwat`,f.`ampur`)!=cod.`distid`
-- ORDER BY f.`changwat`
-- LIMIT 1000


-- UPDATE ORG AMPUR FROM PROJECT_FUND
UPDATE `sgz_project_fund` f
LEFT JOIN `sgz_db_org` o USING(`orgid`)
LEFT JOIN `sgz_co_district` cod ON LEFT(cod.`distid`,2)=f.`changwat` AND cod.`distname`=f.`nameampur`
SET
 f.`ampur` = SUBSTR(cod.`distid`,3,2)
 , o.`ampur` = SUBSTR(cod.`distid`,3,2)
 , o.`areacode`=CONCAT(o.`changwat`,IFNULL(SUBSTR(cod.`distid`,3,2),""),IFNULL(o.`tambon`,""))
WHERE cod.`distid` IS NOT NULL;





-- UPDATE ORG TAMBON FROM PROJECT_FUND

-- SELECT
-- f.`orgid`,f.`areaid`,f.`changwat`,f.`ampur`,f.`tambon`,o.`areacode`
-- ,cos.`subdistid`,SUBSTR(cos.`subdistid`,5,2) `newcode`
-- ,f.`namechangwat`,f.`nameampur`,f.`fundname`
-- FROM `sgz_project_fund` f
UPDATE `sgz_project_fund` f
LEFT JOIN `sgz_db_org` o USING(`orgid`)
LEFT JOIN `sgz_co_subdistrict` cos ON LEFT(cos.`subdistid`,4)=LEFT(o.`areacode`,4) AND cos.`subdistname`=REGEXP_REPLACE(`fundname`,"เทศบาลนคร|เทศบาลเมือง|เทศบาลตำบล|อบต\.","")
SET
f.`tambon` = SUBSTR(cos.`subdistid`,5,2)
, o.`tambon` = SUBSTR(cos.`subdistid`,5,2)
, o.`areacode`=CONCAT(o.`changwat`,o.`ampur`,IFNULL(SUBSTR(cos.`subdistid`,5,2),""))
WHERE o.`tambon` IS NULL AND cos.`subdistid` IS NOT NULL
-- AND CONCAT(f.`changwat`,f.`ampur`)!=cod.`distid`
-- ORDER BY f.`changwat`
-- LIMIT 1000





UPDATE `sgz_project` p
	LEFT JOIN `sgz_topic` t USING(`tpid`)
	LEFT JOIN `sgz_db_org` o USING(`orgid`)
SET
t.`changwat` = o.`changwat`
, p.`changwat` = o.`changwat`, p.`ampur` = o.`ampur`
WHERE o.`changwat` IS NOT NULL;


UPDATE `sgz_project` p
	LEFT JOIN `sgz_topic` t USING(`tpid`)
    LEFT JOIN `sgz_db_org` o USING(`orgid`)
SET p.`changwat` = t.`changwat`
WHERE `orgid` IS NOT NULL AND (p.`changwat` = "" OR p.`changwat` IS NULL);




ALTER TABLE `sgz_project_fund` ADD `areacode` VARCHAR(8) NULL DEFAULT NULL AFTER `tambon`;


SELECT p.`tpid`,t.`changwat`,t.`orgid`, o.`changwat`,o.`ampur`,p.`changwat`,p.`ampur`
FROM `sgz_project` p
	LEFT JOIN `sgz_topic` t USING(`tpid`)
    LEFT JOIN `sgz_db_org` o USING(`orgid`)
WHERE `orgid` IS NOT NULL AND (p.`changwat` = "" OR p.`changwat` IS NULL)
LIMIT 1000
	return $ret;
}
?>