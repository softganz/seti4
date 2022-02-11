<?php
function r_garage_billing_create($shopId, $data, $options = '{}') {
	if (empty($shopId)) return false;

	$defaults = '{value:"repairname",debug:false}';
	$options = sg_json_decode($options,$defaults);
	$debug = $options->debug;

	$docName = 'Billing';
	$docShortName = 'BIL';

	if (is_array($data)) $data = (object)$data;

	$docShopId = $shopId;

	do {
		$nextNo = R::Model('garage.nextno', $shopId, $docName, $docShortName);
		$docShopId = $nextNo->shopId;
		$docFormat = $nextNo->format;
		$data->billno = $nextNo->nextNo;

		//$data->billno=R::Model('garage.nextno', $shopId, $docName, '{debug:false}');

		$isDup = mydb::select(
			'SELECT `billid` FROM %garage_billing% b WHERE b.`shopid` = :shopid AND b.`billno` = :billno LIMIT 1',
			':shopid', $docShopId, ':billno', $data->billno
		)->billid;

		if ($debug) debugMsg('$isDup='.($isDup?'duplicate to Billing no '.$isDup:'not duplicate').'<br />'.mydb()->_query);

		if ($isDup) {
			$stmt = 'UPDATE %garage_lastno% SET `lastno` = :lastno WHERE `shopid` = :shopid AND `docname` = :docname LIMIT 1';
			mydb::query($stmt, ':shopid', $docShopId, ':lastno', $data->jobno, ':docname', $docName);
		}
	} while ($isDup);

	if ($isDup || empty($data->billno)) return false;

	if ($debug) debugMsg('<b>Create new QT with qtno='.$data->billno.'</b>');

	$bill = new stdClass();
	$bill->billid = NULL;
	$bill->shopid = $shopId;
	$bill->billdate = sg_date($data->billdate,'Y-m-d');
	$bill->billno = $data->billno;
	$bill->insurerid = $data->insurerid;
	$bill->uid = i()->uid;
	$bill->created = date('U');

	$stmt = 'INSERT INTO %garage_billing%
		(`billid`,`shopid`,`billdate`,`billno`,`insurerid`,`uid`,`created`)
		VALUES
		(:billid,:shopid,:billdate,:billno,:insurerid,:uid,:created)';

	mydb::query($stmt,$bill);

	$querys[] = mydb()->_query;

	if (!mydb()->_error) {
		$billid = $bill->billid = $data->billid = mydb()->insert_id;
		if ($data->qtid AND is_array($data->qtid)) {
			$stmt = 'UPDATE %garage_qt% SET `billid` = :billid WHERE `qtid` IN ( :qtid )';
			mydb::query($stmt, ':billid', $billid, ':qtid', 'SET:'.implode(',',$data->qtid));
			$querys[] = mydb()->_query;
		}

		// Update lastno
		$stmt = 'UPDATE %garage_lastno% SET `lastno` = :lastno WHERE `shopid` = :shopid AND `docname` = :docname LIMIT 1';
		mydb::query($stmt, ':shopid', $docShopId, ':lastno', $data->billno, ':docname', $docName);

	}

	if ($debug) {
		debugMsg($data,'$data');
		debugMsg($bill,'$bill');
		debugMsg($querys,'$querys');
	}
	return $billid;
}
?>