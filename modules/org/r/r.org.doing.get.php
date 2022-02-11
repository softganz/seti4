<?php
/**
* Get doing information
*
* @param Intefer $doid
* @return Object Record Set
*/

function r_org_doing_get($conditions, $options = '{}') {
	$defaults = '{debug: false, data: "*"}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object)$conditions;
	else {
		$conditions = (Object) ['doid' => $conditions];
	}

	if ($conditions->doid) mydb::where('d.`doid` = :doid', ':doid', $conditions->doid);
	if ($conditions->calid) mydb::where('d.`calid` = :calid', ':calid', $conditions->calid);

	$stmt = 'SELECT d.*,
			t.`title` projectTitle,
			tg.`name` issue_name ,
			o.`name` `orgname`,
			(SELECT COUNT(*) FROM %org_dos% WHERE `doid` = d.`doid` AND `isjoin`) joins
		, cos.`subdistname` `doTambon`
		, cod.`distname` `doAmpur`
		, cop.`provname` `doChangwat`
		FROM %org_doings% d
			LEFT JOIN %tag% tg ON tg.`tid` = d.`issue`
			LEFT JOIN %db_org% o USING (`orgid`)
			LEFT JOIN %topic% t USING (`tpid`)
			LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(d.`areacode`, 2)
			LEFT JOIN %co_district% cod ON cod.`distid` = LEFT(d.`areacode`, 4)
			LEFT JOIN %co_subdistrict% cos ON cos.`subdistid` = LEFT(d.`areacode`, 6)
		%WHERE%
		LIMIT 1';
	$rs = mydb::select($stmt);

	if (!$debug) mydb::clearprop($rs);

	$rs->options = sg_json_decode($rs->options);

	if ($options->data == '*') {
		$stmt = 'SELECT d.*,
			p.`prename`, p.`name`, p.`lname`, p.`cid`, p.`phone`
			, p.`house`, p.`village`, p.`tambon`, p.`ampur`, p.`changwat`
			, IFNULL(cosub.`subdistname`,p.`t_tambon`) subdistname
			, IFNULL(codist.`distname`,p.`t_ampur`) distname
			, IFNULL(cop.`provname`,p.`t_changwat`) provname
			, p.`zip`
			, p.`email`
			, u.`username` `registerByUsername`
			, (SELECT COUNT(*) FROM %org_dos% jd LEFT JOIN %org_doings% jdo USING(`doid`)
				WHERE jd.`psnid`=d.`psnid` AND `isjoin`=1 AND jdo.`orgid`=:orgid) `joins`
			, GROUP_CONCAT(o.`name`) `orgName`
			FROM %org_dos% d
				LEFT JOIN %org_doings% do USING(`doid`)
				LEFT JOIN %db_person% p USING(`psnid`)
				LEFT JOIN %users% u ON u.`uid` = d.`uid`
				LEFT JOIN %org_morg% mo USING(`psnid`)
				LEFT JOIN %db_org% o ON o.`orgid`=mo.`orgid`
				LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
				LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
				LEFT JOIN %co_village% covi ON covi.`villid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`,IF(LENGTH(p.`village`)=1,CONCAT("0",p.`village`),p.`village`))
				LEFT JOIN %co_province% cop ON p.`changwat`=cop.`provid`
			WHERE  d.`doid`=:doid
			GROUP BY `psnid`
			ORDER BY CONVERT(CONCAT(p.`name`," ",p.`lname`) USING tis620) ASC;
			-- {key: "psnid"}';
		$dbs = mydb::select($stmt,':doid',$rs->doid, ':orgid',$rs->orgid);


		$rs->_member_query=$dbs->_query;
		$rs->_member_error=$dbs->_error;
		$rs->_member_error_msg=$dbs->_error_msg;

		$rs->members = $dbs->items;
	}

	return $rs;
}
?>