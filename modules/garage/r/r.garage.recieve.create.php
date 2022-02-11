<?php
/**
* Garage Model :: Create Recieve
* Created 2017-07-21
* Modify  2020-02-18
*
* @param Object $shopInfo
* @param Object $data
* @param Object $options
* @return Int $rcvId
*/

$debug = true;

function r_garage_recieve_create($shopInfo, $data, $options = '{}') {
	$shopId = $shopInfo->shopid;
	if (empty($shopId)) return false;

	$defaults = '{debug:false}';
	$options = sg_json_decode($options,$defaults);
	$debug = $options->debug;

	$docName = 'Recieve';
	$docShortName = 'RCV';

	$insurerInfo = R::Model('garage.insurer.get',$shopId,$data->insurerid);

	if (is_array($data)) $data = (Object) $data;

	$docShopId = $shopId;

	do {
		$nextNo = R::Model('garage.nextno', $shopId, $docName, $docShortName);
		$docShopId = $nextNo->shopId;
		$docFormat = $nextNo->format;
		$data->rcvno = $nextNo->nextNo;

		//$data->rcvno = R::Model('garage.nextno',$shopId,$docName,'{debug:false}');

		$isDup = mydb::select(
			'SELECT `rcvid` FROM %garage_rcv% r WHERE r.`shopid` = :shopid AND r.`rcvno` = :rcvno LIMIT 1',
			':shopid', $docShopId, ':rcvno', $data->rcvno
		)->rcvid;

		if ($debug) debugMsg('$isDup='.($isDup?'duplicate to Recieve no '.$isDup:'not duplicate').'<br />'.mydb()->_query);

		if ($isDup) {
			$stmt = 'UPDATE %garage_lastno% SET `lastno` = :lastno WHERE `shopid` = :shopid AND `docname` = :docname LIMIT 1';
			mydb::query($stmt,':shopid',$docShopId, ':lastno',$data->rcvno,':docname',$docName);
		}
	} while ($isDup);

	if ($isDup || empty($data->rcvno)) return false;


	if ($debug) debugMsg('<b>Create new document with no='.$data->rcvno.'</b>');

	$docs = new stdClass();
	$docs->rcvid = NULL;
	$docs->shopid = $shopId;
	$docs->rcvdate = sg_date($data->rcvdate,'Y-m-d');
	$docs->rcvno = $data->rcvno;
	$docs->insurerid = $data->insurerid;
	$docs->uid = i()->uid;
	$docs->rcvcustname = $insurerInfo->insurername;
	$docs->rcvaddr = $insurerInfo->insureraddr;
	$docs->rcvphone = $insurerInfo->insurerphone;
	$docs->rcvtaxid = $insurerInfo->insurertaxid;
	$docs->rcvbranch = $insurerInfo->insurerbranch;
	$docs->vatrate = $shopInfo->vatrate;
	$docs->created = date('U');

	$stmt = 'INSERT INTO %garage_rcv%
		(`rcvid`,`shopid`,`rcvdate`,`rcvno`,`insurerid`,`uid`,`rcvcustname`,`rcvaddr`,`rcvphone`,`rcvtaxid`,`rcvbranch`,`vatrate`,`created`)
		VALUES
		(:rcvid,:shopid,:rcvdate,:rcvno,:insurerid,:uid,:rcvcustname,:rcvaddr,:rcvphone,:rcvtaxid,:rcvbranch,:vatrate,:created)';

	mydb::query($stmt,$docs);

	$querys[] = mydb()->_query;

	if (!mydb()->_error) {
		$rcvId = $docs->rcvid = $data->rcvid = mydb()->insert_id;

		// Update lastno
		$stmt = 'UPDATE %garage_lastno% SET `lastno` = :lastno WHERE `shopid` = :shopid AND `docname` = :docname LIMIT 1';
		mydb::query($stmt,':shopid',$docShopId, ':lastno',$data->rcvno, ':docname',$docName);
		$querys[] = mydb()->_query;

		if ($data->qtid AND is_array($data->qtid)) {
			$stmt = 'UPDATE %garage_qt% SET `rcvid` = :rcvid WHERE `qtid` IN (:qtid)';
			mydb::query($stmt,':rcvid',$rcvId,':qtid','SET:'.implode(',',$data->qtid));
			$querys[] = mydb()->_query;

			R::Model('garage.recieve.vat.update', $rcvId, 'ALL');
		}

	}

	if ($debug) {
		debugMsg($data,'$data');
		debugMsg($docs,'$docs');
		debugMsg($querys,'$querys');
	}

	return $rcvId;
}
?>