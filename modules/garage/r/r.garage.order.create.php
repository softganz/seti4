<?php
function r_garage_order_create($shopInfo, $data, $options = '{}') {
	$shopId = $shopInfo->shopid;
	if (empty($shopId)) return false;

	$defaults = '{debug:false}';
	$options = SG\json_decode($options,$defaults);
	$debug = $options->debug;

	$docName = 'Order';
	$docShortName = 'ORD';
	//$apInfo=R::Model('garage.ap.get',$shopId,$data->apid);

	if (is_array($data)) $data = (Object)$data;


	// Get last doc no or create new doc format on empty
	$docShopId = $shopId;
	do {
		$nextNo = R::Model('garage.nextno', $shopId, $docName, $docShortName);
		$docShopId = $nextNo->shopId;
		$docFormat = $nextNo->format;
		$data->docno = $nextNo->nextNo;

		$isDup = mydb::select(
			'SELECT `ordid` FROM %garage_ordmast% r WHERE r.`shopid` = :shopid AND r.`ordno` = :docno LIMIT 1',
			':shopid', $docShopId, ':docno', $data->docno
		)->ordid;

		if ($debug) debugMsg('$isDup='.($isDup?'duplicate to Recieve no '.$isDup:'not duplicate').'<br />'.mydb()->_query);

		if ($isDup) {
			$stmt = 'UPDATE %garage_lastno% SET `lastno` = :lastno WHERE `shopid` = :shopid AND `docname` = :docname LIMIT 1';
			mydb::query($stmt, ':shopid', $docShopId, ':lastno', $data->docno, ':docname', $docName);
		}
	} while ($isDup);

	if ($isDup || empty($data->docno)) return false;

	if ($debug) debugMsg('<b>Create new document with no='.$data->docno.'</b>');




	// Start create new document
	$docs = new stdClass();
	$docs->docid = NULL;
	$docs->shopid = $shopId;
	$docs->docdate = sg_date($data->docdate,'Y-m-d');
	$docs->docno = $data->docno;
	$docs->apid = $data->apid;
	$docs->uid = i()->uid;
	$docs->vatrate = $shopInfo->vatrate;
	$docs->created = date('U');


	$stmt = 'INSERT INTO %garage_ordmast%
		(`ordid`, `shopid`, `orddate`, `ordno`, `apid`, `uid`, `created`)
		VALUES
		(:docid, :shopid, :docdate, :docno, :apid, :uid, :created)';

	mydb::query($stmt,$docs);

	$querys[] = mydb()->_query;

	if (!mydb()->_error) {
		$docid = $docs->docid = $data->ordid = mydb()->insert_id;

		// Update lastno
		$stmt='UPDATE %garage_lastno% SET `lastno` = :lastno WHERE `shopid` = :shopid AND `docname` = :docname LIMIT 1';
		mydb::query($stmt,':shopid',$docShopId, ':lastno',$data->docno, ':docname',$docName);
		$querys[] = mydb()->_query;
	}

	if ($debug) {
		debugMsg($data,'$data');
		debugMsg($docs,'$docs');
		debugMsg($querys,'$querys');
	}
	return $docid;
}
?>