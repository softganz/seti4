<?php
/**
* Get doing information
*
* @param Intefer $doid
* @return Object Record Set
*/

function r_org_dopaid_get($conditions=NULL, $options='{}') {
	$defaults = '{debug:false, data: "info,member,bill", order:"tr.`date1` ASC, tr.`trid` ASC","start":-1, billOrder: "CONVERT(`paidname` USING tis620) ASC, CONVERT(`lname` USING tis620) ASC"}';
	$options = sg_json_decode($options,$defaults);
	$debug = $options->debug;
	$dataFields = explode(',',$options->data);

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object) $conditions;
	else if (is_numeric($conditions)) {
		$conditions = (Object) ['doid' => $conditions];
	}

	$result = NULL;

	//debugMsg($conditions,'$conditions');
	//debugMsg($dataFields,'dataFields');

	mydb::where('d.`doid` = :doid', ':doid', $conditions->doid);
	$stmt='SELECT d.*,
						t.`title` projectTitle,
						tg.`name` issue_name ,
						o.`name` `orgname`,
						(SELECT COUNT(*) FROM %org_dos% WHERE `doid`=d.`doid` AND `isjoin`) joins
					FROM %org_doings% d
						LEFT JOIN %tag% tg ON tg.`tid`=d.`issue`
						LEFT JOIN %db_org% o USING (`orgid`)
						LEFT JOIN %topic% t USING (`tpid`)
					%WHERE%
					LIMIT 1';
	$result = mydb::select($stmt);

	$result->query[] = mydb()->_query;

	if (in_array('member',$dataFields)) {
		mydb::where('d.`doid` = :doid', ':doid', $result->doid);

		if ($conditions->joingroup)
			mydb::where('d.`joingroup` = :joingroup', ':joingroup', $conditions->joingroup);

		if ($conditions->search != '') {
			list($name, $lname) = sg::explode_name(' ', $conditions->search);
			mydb::where('(p.`cid` LIKE :name OR p.`phone` LIKE :name OR (`name` LIKE :name '.($lname?'AND `lname` LIKE :lname':'').'))',':name','%'.$name.'%', ':lname','%'.$lname.'%');
		}


		$stmt='SELECT
								do.`dopid`
								, d.*
								, p.`prename`, p.`name`, p.`lname`, p.`phone`
								, p.`house`, p.`village`, p.`tambon`, p.`ampur`, p.`changwat`
								, IFNULL(cosub.`subdistname`,p.`t_tambon`) subdistname
								, IFNULL(codist.`distname`,p.`t_ampur`) distname
								, IFNULL(cop.`provname`,p.`t_changwat`) provname
								, p.`zip`
								, p.`email`
								, do.`islock`
								, do.`total`
						FROM %org_dos% d
							LEFT JOIN %db_person% p USING(`psnid`)
							LEFT JOIN %org_dopaid% do ON do.`doid`=d.`doid` AND do.`psnid`=d.`psnid`
							LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
							LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
							LEFT JOIN %co_village% covi ON covi.`villid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`,IF(LENGTH(p.`village`)=1,CONCAT("0",p.`village`),p.`village`))
							LEFT JOIN %co_province% cop ON p.`changwat`=cop.`provid`
						%WHERE%
						ORDER BY CONVERT(CONCAT(p.`name`," ",p.`lname`) USING tis620) ASC';
		$dbs = mydb::select($stmt);

		$result->query[] = mydb()->_query;

		$result->members=$dbs->items;
	}

	if (in_array('bill',$dataFields)) {
		mydb::where('dp.`doid` = :doid', ':doid', $result->doid);
		if ($conditions->search != '') {
			list($name, $lname) = sg::explode_name(' ', $conditions->search);
			mydb::where('(p.`cid` LIKE :name OR p.`phone` LIKE :name OR (`name` LIKE :name '.($lname?'AND `lname` LIKE :lname':'').'))',':name','%'.$name.'%', ':lname','%'.$lname.'%');
		}
		mydb::value('$ORDER$', $options->billOrder);

		$stmt = 'SELECT
							dp.*
						, FROM_UNIXTIME(dp.`created`,"%Y-%m-%d %H:%i:%s") `billCreated`
						, CONCAT(p.`prename`,p.`name`," ",p.`lname`) `fullname`
						, p.`cid`
						, p.`house`, p.`village`, p.`tambon`, p.`ampur`, p.`changwat`
						, u.`name` `createBy`
						FROM %org_dopaid% dp
							LEFT JOIN %db_person% p USING(`psnid`)
							LEFT JOIN %users% u ON u.`uid` = dp.`uid`
						%WHERE%
						ORDER BY $ORDER$';

		$result->bills = mydb::select($stmt)->items;

		$result->query[] = mydb()->_query;
	}
	return $result;
}
?>