<?php
/**
* Get Project Persion Join information
* Created 2019-06-01
* Modify  2019-07-28
*
* @param Intefer $doid
* @return Object $options
* @return Object Record Set
*/

$debug = true;

function r_project_join_get($conditions, $options = '{}') {
	$defaults = '{debug: false, order: "name", limit: 1}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object)$conditions;
	else {
		$conditions = (Object) ['doid' => $conditions];
	}

	if ($conditions->doid)
		mydb::where('d.`doid` = :doid', ':doid', $conditions->doid);

	if ($conditions->calid)
		mydb::where('do.`calid` = :calid', ':calid', $conditions->calid);

	if ($conditions->joingroup)
		mydb::where('d.`joingroup` = :joingroup', ':joingroup', $conditions->joingroup);

	if ($conditions->regtype)
		mydb::where('d.`regtype` = :regtype', ':regtype', $conditions->regtype);

	if ($conditions->psnid)
		mydb::where('d.`psnid` = :psnid', ':psnid', $conditions->psnid);

	if ($conditions->refcode)
		mydb::where('d.`refcode` = :refcode', ':refcode', $conditions->refcode);

	if ($conditions->cid)
		mydb::where('p.`cid` = :cid', ':cid', $conditions->cid);

	if ($conditions->phone)
		mydb::where('p.`phone` = :phone', ':phone', $conditions->phone);

	if (isset($conditions->jointype)) {
		if ($conditions->jointype == 'register') {
			mydb::where('d.`isjoin` >= 0');
		} else if ($conditions->jointype == 'join') {
			mydb::where('d.`isjoin` > 0');
		} else if ($conditions->jointype == 'cancel') {
			mydb::where('d.`isjoin` < 0');
		}
	}

	$orderList = [
		'no' => 'd.`created` ASC',
		'no' => 'IFNULL(`printweight`, 20000000) ASC, CONVERT(`firstname` USING tis620) ASC',
		'name' => 'CONVERT(`firstname` USING tis620) ASC',
		'weight,name' => '`printweight` ASC, CONVERT(`firstname` USING tis620) ASC',
		'prov' => 'CONVERT(`changwatName` USING tis620) ASC, `printweight` ASC, CONVERT(`firstname` USING tis620) ASC'
	];

	mydb::value('$ORDER', 'ORDER BY '.$orderList[$options->order],false);
	mydb::value('$LIMIT', $options->limit != '*' ? 'LIMIT '.$options->limit : '');

	//debugMsg($options,'$options');
	//debugMsg(mydb());
	$stmt='SELECT
				d.*
				, do.`tpid`
				, do.`calid`
				, c.`title` `calendarTitle`
				, p.`prename`
				, p.`name` `firstname`
				, p.`lname` `lastname`
				, p.`cid`
				, p.`sex`
				, p.`religion`
				, p.`birth`
				, p.`phone`
				, p.`house`
				, p.`village`
				, p.`tambon`
				, p.`ampur`
				, p.`changwat`
				, cosub.`subdistname` `tambonName`
				, codist.`distname` `ampurName`
				, cop.`provname` `changwatName`
				, p.`zip`
				, p.`email`
				, po.`trid` `orgtrid`
				, po.`detail1` `orgname`
				, po.`detail2` `orgtype`
				, po.`detail3` `position`
				, po.`detail4` `tripotherby`
				, po.`text1` `carregist`
				, po.`text8` `carregprov`
				, po.`text2` `hotelname`
				, po.`text3` `hotelmate`
				, po.`text4` `hotelwithpsnid`
				, po.`text5` `carwithname`
				, po.`text6` `rentregist`
				, po.`text7` `rentpassenger`
				, po.`num1` `busprice`
				, po.`num2` `airprice`
				, po.`num3` `tripotherprice`
				, po.`num4` `taxiprice`
				, po.`num5` `trainprice`
				, po.`num8` `rentprice`
				, po.`num9` `tripotherprice`
				, po.`num10` `localprice`
				, po.`num6` `hotelprice`
				, ROUND(po.`num7`) `hotelnight`
				, do.`areacode` `doareacode`
				, dt.`fromareacode`, dt.`toareacode`
				, dt.`distance`, dt.`fixprice`
				, po.`text10` `remark`
				, do.`registerrem`
				, do.`paidgroup`
				, dop.`dopid`
				, u.`name` `registerByName`
		FROM %org_dos% d
			LEFT JOIN %org_doings% do USING(`doid`)
			LEFT JOIN %calendar% c ON c.`id` = do.`calid`
			LEFT JOIN %db_person% p USING(`psnid`)
			LEFT JOIN %users% u ON u.`uid` = d.`uid`
			LEFT JOIN %project_tr% po ON po.`tpid` = do.`tpid` AND po.`formid` = "join" AND po.`part` = "register" AND po.`refid` = d.`doid` AND po.`refcode` = d.`psnid`
			LEFT JOIN %distance% dt ON dt.`fromareacode` = CONCAT(p.`changwat`, p.`ampur`) AND dt.`toareacode` = do.`areacode`
			LEFT JOIN %co_district% codist ON codist.`distid`=CONCAT(p.`changwat`,p.`ampur`)
			LEFT JOIN %co_subdistrict% cosub ON cosub.`subdistid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`)
			LEFT JOIN %co_village% covi ON covi.`villid`=CONCAT(p.`changwat`,p.`ampur`,p.`tambon`,IF(LENGTH(p.`village`)=1,CONCAT("0",p.`village`),p.`village`))
			LEFT JOIN %co_province% cop ON p.`changwat`=cop.`provid`
			LEFT JOIN %org_dopaid% dop ON dop.`doid` = d.`doid` AND dop.`psnid` = d.`psnid`

		%WHERE%
		$ORDER
		$LIMIT';

	$dbs = mydb::select($stmt);

	if ($debug) debugMsg(mydb()->_query);

	if ($dbs->_empty) return NULL;


	if ($options->limit == 1) {
		$result = $dbs;
		$result =_r_project_join_get_ext($result);
	} else {
		foreach ($dbs->items as $rs) {
			$result[] = _r_project_join_get_ext($rs);
		}
	}

	/*
	$stmt = 'SELECT * FROM %project_tr% WHERE `tpid` = :tpid AND `refid` = :refid AND `refcode` = :refcode AND `formid` = "join" AND `part` = "register"  ';
	$orgRs = mydb::select($stmt, ':tpid', $rs->tpid, ':refid', $rs->doid, ':refcode', $rs->psnid);

	debugMsg($orgRs,'$orgRs');
	*/

	if ($debug) debugMsg($result,'$result');

	return $result;
}

function _r_project_join_get_ext($rs) {
	$rs->address = SG\implode_address($rs,'short');
	if ($rs->jointype) $rs->jointype = explode(',', $rs->jointype);

	if ($rs->information) $rs = sg_json_decode($rs, $rs->information);

	foreach (explode(',', $rs->tripby) as $item)
		$rs->tripByList[$item] = $item;

	$rs->tripTotalPrice = 	$rs->busprice + $rs->airprice + $rs->tripotherprice + $rs->taxiprice + $rs->trainprice + $rs->rentprice;
	return $rs;
}
?>