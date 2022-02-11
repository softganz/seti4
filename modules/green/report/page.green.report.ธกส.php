<?php
/**
* Module :: Description
* Created 2021-01-01
* Modify  2021-01-01
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage module/{id}/method
*/

$debug = true;

function green_report_ธกส($self) {
	$ret = '';


	//-- จำนวนต้นไม้ทั้งหมด
	$dbs = mydb::select(
		'SELECT
		o.`name` `orgName`
		,l.`landname`
		,p.`productname`
		,p.`startdate`, FROM_UNIXTIME(p.`created`,"%Y-%m-%d") `createdDate`
		FROM `sgz_ibuy_farmplant` p
		LEFT JOIN `sgz_ibuy_farmland` l ON l.`landid`=p.`landid`
		LEFT JOIN `sgz_db_org` o ON o.`orgid` = l.`orgid`
		WHERE p.`tagname`="GREEN,TREE" AND p.`landid` != 2 AND l.`landid` IS NOT NULL
		ORDER BY CONVERT(`orgName` USING tis620) ASC, CONVERT(`landname` USING tis620) ASC, CONVERT(`productname` USING tis620) ASC;
		'
	);

	$ret .= mydb::printtable($dbs);

	/*


-- จำนวนต้นไม้ที่บันทึกช่วง ธันวาคม 2563 - กุมภาพันธ์ 2564
SELECT
o.`name` `orgName`
,l.`landname`
,p.`productname`
,p.`startdate`, FROM_UNIXTIME(p.`created`,"%Y-%m-%d") `createdDate`
FROM `sgz_ibuy_farmplant` p
LEFT JOIN `sgz_ibuy_farmland` l ON l.`landid`=p.`landid`
LEFT JOIN `sgz_db_org` o ON o.`orgid` = l.`orgid`
WHERE p.`tagname`="GREEN,TREE" AND p.`landid` != 2 AND l.`landid` IS NOT NULL AND FROM_UNIXTIME(p.`created`,"%Y-%m-%d") BETWEEN "2020-12-01" AND "2021-02-28"
ORDER BY CONVERT(`orgName` USING tis620) ASC, CONVERT(`landname` USING tis620) ASC, CONVERT(`productname` USING tis620) ASC
LIMIT 100


-- จำนวนราย
SELECT
o.`name` `orgName`
,l.`landname`
,p.`productname`
, COUNT(*) `totalLand`
,p.`startdate`, FROM_UNIXTIME(p.`created`,"%Y-%m-%d") `createdDate`
FROM `sgz_ibuy_farmplant` p
LEFT JOIN `sgz_ibuy_farmland` l ON l.`landid`=p.`landid`
LEFT JOIN `sgz_db_org` o ON o.`orgid` = l.`orgid`
WHERE p.`tagname`="GREEN,TREE" AND p.`landid` != 2 AND l.`landid` IS NOT NULL AND FROM_UNIXTIME(p.`created`,"%Y-%m-%d") BETWEEN "2020-12-01" AND "2021-02-28"
GROUP BY p.`landid`
ORDER BY CONVERT(`orgName` USING tis620) ASC, CONVERT(`landname` USING tis620) ASC, CONVERT(`productname` USING tis620) ASC
LIMIT 100
*/

	return $ret;
}
?>