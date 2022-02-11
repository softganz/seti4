<?php
function r_garage_aprcv_create($shopInfo,$data,$options='{}') {
	$shopId = $shopInfo->shopid;
	if (empty($shopId)) return false;

	$defaults = '{debug:false}';
	$options = SG\json_decode($options,$defaults);
	$debug = $options->debug;

	$docName = 'ApMast';
	$docShortName = 'AP';
	$apInfo = R::Model('garage.ap.get',$shopId,$data->apid);

	if (is_array($data)) $data = (object)$data;

	$docShopId = $shopId;

	do {
		$nextNo = R::Model('garage.nextno', $shopId, $docName, $docShortName);
		$docShopId = $nextNo->shopId;
		$docFormat = $nextNo->format;
		$data->rcvno = $nextNo->nextNo;

		$isDup = mydb::select(
			'SELECT `rcvid` FROM %garage_apmast% r WHERE r.`shopid` = :shopid AND r.`rcvno` = :rcvno LIMIT 1',
			':shopid', $docShopId, ':rcvno', $data->rcvno
		)->rcvid;

		if ($debug) debugMsg('$isDup='.($isDup?'duplicate to Recieve no '.$isDup:'not duplicate').'<br />'.mydb()->_query);

		if ($isDup) {
			$stmt='UPDATE %garage_lastno% SET `lastno` = :lastno WHERE `shopid` = :shopid AND `docname` = :docname LIMIT 1';
			mydb::query($stmt, ':shopid', $docShopId, ':lastno', $data->rcvno, ':docname', $docName);
		}
	} while ($isDup);

	if ($isDup || empty($data->rcvno)) return false;

	if ($debug) debugMsg('<b>Create new document with no='.$data->rcvno.'</b>');

	$docs = new stdClass();
	$docs->rcvid = NULL;
	$docs->shopid = $shopId;
	$docs->rcvdate = sg_date($data->rcvdate,'Y-m-d');
	$docs->rcvno = $data->rcvno;
	$docs->apid = $data->apid;
	$docs->refno = $data->refno;
	$docs->uid = i()->uid;
	$docs->vatrate = sg_strip_money($data->vatrate);
	$docs->ispaid = $data->paidcash?-1:0;
	$docs->created = date('U');

	$stmt = 'INSERT INTO %garage_apmast%
		(`rcvid`,`shopid`,`rcvdate`,`rcvno`,`apid`,`refno`,`uid`,`vatrate`,`ispaid`,`created`)
		VALUES
		(:rcvid,:shopid,:rcvdate,:rcvno,:apid,:refno,:uid,:vatrate,:ispaid,:created)';

	mydb::query($stmt,$docs);

	$querys[] = mydb()->_query;

	if (!mydb()->_error) {
		$rcvid = $docs->rcvid = $data->rcvid = mydb()->insert_id;

		// Update lastno
		$stmt = 'UPDATE %garage_lastno% SET `lastno` = :lastno WHERE `shopid` = :shopid AND `docname` = :docname LIMIT 1';
		mydb::query($stmt, ':shopid', $docShopId, ':lastno', $data->rcvno, ':docname', $docName);
		$querys[] = mydb()->_query;
	}

	if ($debug) {
		debugMsg($data,'$data');
		debugMsg($docs,'$docs');
		debugMsg($querys,'$querys');
	}
	return $rcvid;
}
?>