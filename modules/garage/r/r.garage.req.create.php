<?php
function r_garage_req_create($shopInfo, $data, $options = '{}') {
	$shopId = $shopInfo->shopid;
	if (empty($shopId)) return false;

	$defaults = '{debug:false}';
	$options = SG\json_decode($options,$defaults);
	$debug = $options->debug;

	$docName='Requisition';
	$docShortName='REQ';

	if (is_array($data)) $data = (object)$data;

	$docShopId = $shopId;

	do {
		$nextNo = R::Model('garage.nextno', $shopId, $docName);
		$docShopId = $nextNo->shopId;
		$docFormat = $nextNo->format;
		$data->reqno = $nextNo->nextNo;

		$isDup = mydb::select(
			'SELECT `reqid` FROM %garage_reqmast% r WHERE r.`shopid` = :shopid AND r.`reqno` = :reqno LIMIT 1',
			':shopid', $docShopId, ':reqno', $data->reqno
		)->reqid;

		if ($debug) debugMsg('$isDup='.($isDup?'duplicate to Recieve no '.$isDup:'not duplicate').'<br />'.mydb()->_query);

		if ($isDup) {
			$stmt = 'UPDATE %garage_lastno% SET `lastno` = :lastno WHERE `shopid` = :shopid AND `docname` = :docname LIMIT 1';
			mydb::query($stmt, ':shopid', $docShopId, ':lastno', $data->reqno, ':docname', $docName);
		}
	} while ($isDup);

	if ($isDup || empty($data->reqno)) return false;

	if ($debug) debugMsg('<b>Create new document with no='.$data->reqno.'</b>');

	$docs = new stdClass();
	$docs->reqid = NULL;
	$docs->shopid = $shopId;
	$docs->reqno = $data->reqno;
	$docs->reqdate = sg_date($data->docdate,'Y-m-d');
	$docs->tpid = $data->jobid;
	$docs->uid = i()->uid;
	$docs->created = date('U');

	$stmt = 'INSERT INTO %garage_reqmast%
		(`reqid`, `shopid`, `reqno`, `reqdate`, `tpid`, `uid`, `created`)
		VALUES
		(:reqid, :shopid, :reqno, :reqdate, :tpid, :uid, :created)';

	mydb::query($stmt,$docs);

	$querys[] = mydb()->_query;

	if (!mydb()->_error) {
		$reqid = $docs->reqid = $data->reqid = mydb()->insert_id;

		// Update lastno
		$stmt='UPDATE %garage_lastno% SET `lastno` = :lastno WHERE `shopid` = :shopid AND `docname` = :docname LIMIT 1';
		mydb::query($stmt, ':shopid', $docShopId, ':lastno', $data->reqno, ':docname', $docName);
		$querys[] = mydb()->_query;
	}

	if ($debug) {
		debugMsg($data,'$data');
		debugMsg($docs,'$docs');
		debugMsg($querys,'$querys');
	}
	return $reqid;
}
?>