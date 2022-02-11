<?php
/**
* Get information by id
*
* @param Object $conditions
* @param Object $options
* @return Object
*/

$debug = true;

function r_icar_get($conditions, $options = '{}') {
	$defaults = '{debug: false, updatecost: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	if (is_object($conditions)) ;
	else if (is_array($conditions)) $conditions = (object) $conditions;
	else {
		$conditions = (Object) ['carId' => $conditions];
	}

	$carId = $conditions->carId;

	$stmt='SELECT
		  NULL `RIGHT`, NULL `RIGHTBIN`
		, t.`title` `carname`
		, t.`uid`
		, t.`title`
		, i.*
		, ct.`name` `cartypeName`
		, s.`shopname`
		, b.`name` `brandname`
		, p.`pshopid`
		, IFNULL(p.`name`,"ไม่มีผู้ร่วมทุน") `partnername`
		, p.`share` `pshare`
		, (SELECT UPPER(`membership`) FROM %icarusr% WHERE `shopid` = i.`shopid` AND `uid` = :uid) `iam`
		, s.`shopstatus`
		FROM %icar% i
			LEFT JOIN %topic% t USING(tpid)
			LEFT JOIN %tag% b ON b.`tid` = i.`brand`
			LEFT JOIN %icarshop% s ON i.`shopid` = s.`shopid`
			LEFT JOIN %icarpartner% p USING(partner)
			LEFT JOIN %tag% ct ON ct.`taggroup` = "icar:cartype" AND i.`cartype` = ct.`catid`
		WHERE i.`tpid` = :tpid LIMIT 1';

	$result = mydb::select($stmt,':tpid',$carId, ':uid', i()->uid);

	if ($result->_empty) return NULL;

	if (!$debug) $result = mydb::clearprop($result);

	// Check Right
	$result->RIGHT = NULL;

	// Membership type : 'ADMIN','OWNER','OFFICER','MANAGER','STOCK','VIEWER'
	$isAdmin = user_access('administer icars');
	$isOfficer = $result->iam;
	$isEditable = $isAdmin || in_array($isOfficer, array('ADMIN','OWNER','OFFICER','MANAGER'));
	$isDeletable = $isAdmin || in_array($isOfficer, array('ADMIN','OWNER','MANAGER'));

	if ($isAdmin) $result->RIGHT = $result->RIGHT | _IS_ADMIN;
	if ($isOfficer) $result->RIGHT = $result->RIGHT | _IS_OFFICER;
	if ($isEditable) $result->RIGHT = $result->RIGHT | _IS_EDITABLE;
	if ($isDeletable) $result->RIGHT = $result->RIGHT | _IS_DELETABLE;

	$result->RIGHTBIN = decbin($result->RIGHT);

	$result->costcalculate=0;
	$result->interest=0;
	$result->notcost=0;

	$result->photo = mydb::select('SELECT * FROM %topic_files% WHERE `tpid`='.$result->tpid.' AND `cid`=0 AND `type`="photo" ORDER BY fid')->items;

	if (!$debug) $result->photo = mydb::clearprop($result->photo);

	foreach ($result->photo as $key=>$photo) {
		$result->photo[$key] = object_merge(
			$result->photo[$key],
			model::get_photo_property($photo->file)
		);
	}


	// Get cost transaction
	$stmt = 'SELECT c.*, cid.`name` `costname`, cid.`taggroup`, cid.`process`
		FROM %icarcost% c
			LEFT JOIN %tag% cid ON cid.`tid` = c.`costcode`
		WHERE `tpid` = :tpid
		ORDER BY `itemdate` ASC, `costid` ASC';

	$result->tr = mydb::select($stmt,':tpid',$result->tpid)->items;


	$notcost = 0;
	$saledate = $result->saledate ? $result->saledate : date('Y-m-d');
	foreach ($result->tr as $irs) {
		$irs->interestday = 0;
		$irs->interestamt = 0;
		if ($irs->interest > 0) {
			$irs->interestday = (sg_date($saledate,'U') - sg_date($irs->itemdate,'U')) / (24*60*60)+1;
			$irs->interestamt = round(($irs->interestday * $irs->amt * $irs->interest) / (30*100));
		}
		$interesttotal += $irs->interestamt;
		if ($irs->process == 2) $notcost += $irs->amt;
		if ($irs->taggroup == 'icar:tr:cost') $costtotal += $irs->amt;
		if ($irs->taggroup == 'icar:tr:down') $saledownpaid += $irs->amt;
		if ($irs->taggroup == 'icar:tr:finance') $financeprice += $irs->amt;
		if ($irs->taggroup == 'icar:tr:rcv') $rcv += $irs->amt;
		if ($irs->taggroup == 'icar:tr:exp') $exp += $irs->amt;
	}
	$result->costcalculate = $costtotal;
	$result->interest = $interesttotal;
	$result->notcost = $notcost;



	// UPDATE COST to database
	if ($options->updatecost) {
		$update->costprice = $result->costprice = $result->costcalculate;
		$update->saledownpaid = $result->saledownpaid = $saledownpaid;
		$update->financeprice = $result->financeprice = $financeprice;
		$update->rcvtransfer = $result->rcvtransfer = $rcv;
		$update->paytransfer = $result->paytransfer = $exp;

		$stmt='UPDATE %icar% SET
			`costprice` = :costprice, `saledownpaid` = :saledownpaid
			, `financeprice` = :financeprice, `rcvtransfer` = :rcvtransfer,
			`paytransfer` = :paytransfer
			WHERE `tpid` = :tpid LIMIT 1';

		mydb::query($stmt,':tpid',$carId,$update);
	}


	return $result;
}
?>