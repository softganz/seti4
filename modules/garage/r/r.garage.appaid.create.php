<?php
function r_garage_appaid_create($shopInfo,$data,$options='{}') {
	$shopId = $shopInfo->shopid;
	if (empty($shopId)) return false;

	$defaults = '{debug:false}';
	$options = sg_json_decode($options,$defaults);
	$debug = $options->debug;

	$docName = 'ApPaid';
	$docShortName = 'PS';
	$apInfo = R::Model('garage.ap.get',$shopId,$data->apid);

	if (is_array($data)) $data = (Object) $data;

	$docShopId = $shopId;
	do {
		$nextNo = R::Model('garage.nextno', $shopId, $docName, $docShortName);
		$docShopId = $nextNo->shopId;
		$docFormat = $nextNo->format;
		$data->qtno = $nextNo->nextNo;


		$isDup = mydb::select(
			'SELECT `paidid` FROM %garage_appaid% r WHERE r.`shopid` = :shopid AND r.`paidno` = :paidno LIMIT 1',
			':shopid', $docShopId, ':paidno', $data->paidno
		)->paidid;

		if ($debug) debugMsg('$isDup='.($isDup?'duplicate to Recieve no '.$isDup:'not duplicate').'<br />'.mydb()->_query);

		if ($isDup) {
			$stmt='UPDATE %garage_lastno% SET `lastno`=:lastno WHERE `shopid`=:shopid AND `docname`=:docname LIMIT 1';
			mydb::query($stmt,':shopid',$docShopId, ':lastno',$data->paidno,':docname',$docName);
		}
	} while ($isDup);

	if ($isDup || empty($data->paidno)) return false;

	if ($debug) debugMsg('<b>Create new document with no='.$data->paidno.'</b>');

	$docs=new stdClass();
	$docs->paidid=NULL;
	$docs->shopid=$shopId;
	$docs->paiddate=sg_date($data->paiddate,'Y-m-d');
	$docs->paidno=$data->paidno;
	$docs->apid=$data->apid;
	$docs->uid=i()->uid;
	$docs->created=date('U');

	$stmt='INSERT INTO %garage_appaid%
				(`paidid`,`shopid`,`paiddate`,`paidno`,`apid`,`uid`,`created`)
				VALUES
				(:paidid,:shopid,:paiddate,:paidno,:apid,:uid,:created)';
	mydb::query($stmt,$docs);
	$querys[]=mydb()->_query;

	if (!mydb()->_error) {
		$paidid=$docs->paidid=$data->paidid=mydb()->insert_id;

		if ($data->rcvid AND is_array($data->rcvid)) {
			$stmt='UPDATE %garage_apmast% SET `paidid`=:paidid, `ispaid`=1 WHERE `rcvid` IN (:rcvid)';
			mydb::query($stmt,':paidid',$paidid,':rcvid','SET:'.implode(',',$data->rcvid));
			$querys[]=mydb()->_query;
		}

		$total=mydb::select('SELECT SUM(`grandtotal`) `total` FROM %garage_apmast% WHERE `paidid`=:paidid LIMIT 1',':paidid',$paidid)->total;

		$stmt='UPDATE %garage_appaid% SET `grandtotal`=:total WHERE `paidid`=:paidid LIMIT 1';
		mydb::query($stmt,':paidid',$paidid,':total',$total);
		$querys[]=mydb()->_query;

		// Update lastno
		$stmt='UPDATE %garage_lastno% SET `lastno`=:lastno WHERE `shopid`=:shopid AND `docname`=:docname LIMIT 1';
		mydb::query($stmt,':shopid',$docShopId, ':lastno',$data->paidno, ':docname',$docName);
		$querys[]=mydb()->_query;
	}

	if ($debug) {
		debugMsg($data,'$data');
		debugMsg($docs,'$docs');
		debugMsg($querys,'$querys');
	}
	return $paidid;
}
?>