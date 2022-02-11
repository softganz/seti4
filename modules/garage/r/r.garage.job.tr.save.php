<?php
function r_garage_job_tr_save($shopInfo,$jobInfo,$data,$options='{}') {
	$defaults = '{value:"repairname",debug:false}';
	$options = SG\json_decode($options,$defaults);
	$debug = $options->debug;

	$data->jobtrid = SG\getFirst($data->jobtrid, $data->trid, $tr);// = NULL;
	$data->tpid = $jobInfo->tpid;
	$data->uid = i()->uid;
	$data->datecmd = sg_date(SG\getFirst($data->datecmd, date('Y-m-d')),'Y-m-d');
	$data->qty = sg_strip_money($data->qty);
	$data->price = sg_strip_money($data->price);
	$data->totalsale = $data->qty*$data->price;
	$data->discountrate = sg_strip_money($data->discountrate);
	$data->discountamt = sg_strip_money($data->discountamt);
	$data->vatrate = sg_strip_money($data->vatrate);
	$data->vatamt = sg_strip_money($data->vatamt);
	$data->created = date('U');
	$data->sorder = mydb::select('SELECT MAX(`sorder`) `nextorder` FROM %garage_jobtr% WHERE `tpid`=:tpid LIMIT 1',$data)->nextorder+1;

	$repairInfo = R::Model('garage.repaircode.get',$data->repairid);
	
	if ($data->description != '') ;
	else if ($data->repairname == '') $data->description = 'func.NULL';
	else if ($data->repairname != $repairInfo->repairname) $data->description = $data->repairname;

	$data->wait = $repairInfo->repairtype == 2 ? 1 : NULL;

	$data->qtid = NULL;
	if (in_array($repairInfo->repairtype,array(1,2)) && $jobInfo->qt) {
		$data->qtid = end($jobInfo->qt)->qtid;
	}

	$fields = array();
	$insertField = $insertValue = $updateField = '';

	if (property_exists($data,'damagecode')) $fields['damagecode'] = 'damagecode';
	if (property_exists($data,'description')) $fields['description'] = 'description';

	foreach ($fields as $key => $value) {
		$insertField .= ', `'.$key.'`'._NL;
		$insertValue .= ', :'.$value._NL;
		$updateField .= ', `'.$key.'` = :'.$value._NL;
	}

	mydb::value('$INSERTFIELD$', $insertField, false);
	mydb::value('$INSERTVALUE$', $insertValue, false);
	mydb::value('$UPDATEFIELD$', $updateField, false);

	$stmt = 'INSERT INTO %garage_jobtr%
		(
			  `jobtrid`, `tpid`, `uid`, `datecmd`
			, `qtid`
			, `repairid`
			, `qty`, `price`, `discountrate`, `discountamt`
			, `vatrate`, `vatamt`, `totalsale`
			, `sorder`
			, `wait`
			, `created`
			$INSERTFIELD$
		)
		VALUES
		(
			  :jobtrid, :tpid, :uid, :datecmd
			, :qtid
			, :repairid
			, :qty, :price, :discountrate, :discountamt
			, :vatrate, :vatamt, :totalsale
			, :sorder
			, :wait
			, :created
			$INSERTVALUE$
		)
		ON DUPLICATE KEY UPDATE
			  `datecmd` = :datecmd
			, `repairid` = :repairid
			, `qty` = :qty
			, `price` = :price
			, `vatrate` = :vatamt
			, `vatamt` = :vatamt
			, `totalsale` = :totalsale
			$UPDATEFIELD$
		';

	mydb::query($stmt,$data);

	if (empty($data->jobtrid)) $data->jobtrid = mydb()->insert_id;
	if ($debug) debugMsg(mydb()->_query);
	if ($debug) debugMsg($data,'$data');
	if ($debug) debugMsg($repairInfo,'$repairInfo');
	return $data->jobtrid;
}
?>