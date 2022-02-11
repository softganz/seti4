<?php
function org_admin_report_orgjoinpeople($self) {
	$stmt='SELECT mo.`orgid`,mo.`psnid`
, p.`sex`
, p.`name`
, p.`lname`
, TIMESTAMPDIFF(YEAR, p.`birth`, CURDATE()) `age`
, p.`phone`
, "" `lineid`
, p.`house`
, p.`village`
, cosub.`subdistname`
, codist.`distname`
, copv.`provname`
FROM `sgz_org_mjoin` mo
	LEFT JOIN `sgz_db_person` p USING(`psnid`)
	LEFT JOIN `sgz_co_district` codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
	LEFT JOIN `sgz_co_subdistrict` cosub ON cosub.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
	LEFT JOIN `sgz_co_province` copv ON p.`changwat`=copv.`provid`
WHERE mo.`orgid`=781 AND p.`changwat`="90"
GROUP BY `psnid`
HAVING `name` IS NOT NULL
ORDER BY
	IF(`provname` IS NULL,1,0) ASC
	, CONVERT(`provname` USING tis620) ASC
	, CONVERT(`distname` USING tis620) ASC
	, CONVERT(`subdistname` USING tis620) ASC
	, CONVERT(`village`+0 USING tis620) ASC
	';
	$dbs=mydb::select($stmt);

	$ret.=mydb()->printtable($dbs);

	return $ret;
}
?>