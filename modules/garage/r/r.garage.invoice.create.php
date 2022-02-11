<?php
/**
* Create Garage Invloce
* Created 2019-10-13
* Modify  2019-10-13
*
* @param Int $shopInfo
* @param Object $data
* @param Object $options
* @return Object
*/

$debug = true;

function r_garage_invoice_create($shopInfo, $data, $options = '{}') {
	$shopId = $shopInfo->shopid;
	if (empty($shopId) || empty($data->insurerid)) return false;

	$defaults = '{value:"repairname",debug:false}';
	$options = sg_json_decode($options,$defaults);
	$debug = $options->debug;

	$docName = 'Invoice';

	if (is_array($data)) $data = (Object) $data;

	$insurerInfo = R::Model('garage.insurer.get', $shopId, $data->insurerid);

	$docShopId = $shopId;
	
	do {
		$nextNo = R::Model('garage.nextno', $shopId, $docName);
		$docShopId = $nextNo->shopId;
		$docFormat = $nextNo->format;
		$data->docno = $nextNo->nextNo;

		$isDup = mydb::select(
			'SELECT `invoiceid` FROM %garage_invoice% b WHERE b.`shopid` = :shopid AND b.`docno` = :docno LIMIT 1',
			':shopid',$docShopId, ':docno',$data->docno
		)->invoiceid;

		if ($debug) debugMsg('$isDup = '.($isDup?'duplicate to Invoice no '.$isDup:'not duplicate').'<br />'.mydb()->_query);

		if ($isDup) {
			$stmt = 'UPDATE %garage_lastno% SET `lastno` = :lastno WHERE `shopid` = :shopid AND `docname` = :docname LIMIT 1';
			mydb::query($stmt,':shopid',$docShopId, ':lastno',$data->jobno, ':docname', $docName);
		}
	} while ($isDup);

	if ($isDup || empty($data->docno)) return false;

	if ($debug) debugMsg('<b>Create new Invoice with docno='.$data->docno.'</b>');

	$docs = new stdClass();
	$docs->invoiceid = NULL;
	$docs->shopid = $shopId;
	$docs->docdate = sg_date($data->date,'Y-m-d');
	$docs->docno = $data->docno;
	$docs->insurerid = $data->insurerid;
	$docs->uid = i()->uid;
	$docs->custname = $insurerInfo->insurername;
	$docs->address = $insurerInfo->insureraddr;
	$docs->phone = $insurerInfo->insurerphone;
	$docs->taxid = $insurerInfo->insurertaxid;
	$docs->branch = $insurerInfo->insurerbranch;
	$docs->vatrate = $shopInfo->vatrate;
	$docs->created = date('U');

	$stmt='INSERT INTO %garage_invoice%
		(`invoiceid`,`shopid`,`docdate`,`docno`,`insurerid`,`uid`,`custname`,`address`,`phone`,`taxid`,`branch`,`vatrate`,`created`)
		VALUES
		(:invoiceid,:shopid,:docdate,:docno,:insurerid,:uid,:custname,:address,:phone,:taxid,:branch,:vatrate,:created)';
		
	mydb::query($stmt,$docs);

	$querys[] = mydb()->_query;

	if (!mydb()->_error) {
		$invoiceid = $docs->invoiceid = $data->invoiceid = mydb()->insert_id;

		// Update lastno
		$stmt = 'UPDATE %garage_lastno% SET `lastno` = :lastno WHERE `shopid` = :shopid AND `docname` = :docname LIMIT 1';
		mydb::query($stmt,':shopid',$docShopId, ':lastno',$data->docno, ':docname', $docName);

		if ($data->qtid AND is_array($data->qtid)) {
			$stmt = 'UPDATE %garage_qt% SET `invoiceid` = :invoiceid WHERE `qtid` IN (:qtid)';

			mydb::query($stmt,':invoiceid',$invoiceid,':qtid','SET:'.implode(',',$data->qtid));

			$querys[] = mydb()->_query;
		}

	}

	if ($debug) {
		debugMsg($data,'$data');
		debugMsg($docs,'$docs');
		debugMsg($querys,'$querys');
	}
	return $invoiceid;
}
?>