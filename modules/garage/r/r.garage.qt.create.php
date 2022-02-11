<?php
/**
* Create/Update Quotation
* Created 2019-10-01
* Modify  2019-10-14
*
* @param Int $shopId
* @param Object $data
* @param Object $options
* @return Object
*/

$debug = true;

function r_garage_qt_create($shopId, $data, $options = '{}') {
	if (empty($shopId) || empty($data->tpid)) return false;

	$defaults = '{value: "repairname", debug: false}';
	$options = SG\json_decode($options,$defaults);
	$debug = $options->debug;

	$docName = 'qt';
	$docShortName = 'QT';

	if (is_array($data)) $data = (Object) $data;

	$tpid = $data->tpid;

	if ($data->qtid) {
		$qt = $data;
		$stmt = 'UPDATE %garage_qt% SET
			`qtdate` = :qtdate
			, `insurerid` = :insurerid
			, `insuclaimcode` = :insuclaimcode
			, `insuno` = :insuno
			WHERE `qtid` = :qtid
			LIMIT 1';

		mydb::query($stmt, $qt);

		$querys[] = mydb()->_query;

		return $qt;
	}


	// Create new quotation

	$docShopId = $shopId;

	do {
		$nextNo = R::Model('garage.nextno', $shopId, $docName, $docShortName);
		$docShopId = $nextNo->shopId;
		$docFormat = $nextNo->format;
		$data->qtno = $nextNo->nextNo;

		$isDup = mydb::select(
			'SELECT `qtid` FROM %garage_qt% q LEFT JOIN %garage_job% j USING(`tpid`) WHERE j.`shopid` = :shopid AND q.`qtno` = :qtno LIMIT 1',
			':shopid', $docShopId, ':qtno', $data->qtno
		)->qtid;

		if ($debug) debugMsg('$isDup='.($isDup?'duplicate to QT no '.$isDup:'not duplicate').'<br />'.mydb()->_query);

		if ($isDup) {
			$stmt = 'UPDATE %garage_lastno% SET `lastno` = :lastno WHERE `shopid` = :shopid AND `docname` = "qt" LIMIT 1';
			mydb::query($stmt, ':shopid', $docShopId, ':lastno', $data->jobno);
		}
	} while ($isDup);

	if ($debug) debugMsg('$isDup '.($isDup ? 'TRUE' : 'FALSE').' QTNO = '.$data->qtno);
	if ($isDup || empty($data->qtno)) return false;

	if ($debug) debugMsg('<b>Create new QT with qtno='.$data->qtno.'</b>');

	$qt = new stdClass();
	$qt->qtid = NULL;
	$qt->tpid = $data->tpid;
	$qt->qtdate = date('Y-m-d');
	$qt->qtno = $data->qtno;
	$qt->uid = i()->uid;
	$qt->insurerid = $data->insurerid;
	$qt->insuclaimcode = $data->insuclaimcode;
	$qt->insuno = $data->insuno;
	$qt->replyprice = 0;
	$qt->created = date('U');

	$stmt = 'INSERT INTO %garage_qt%
		(`qtid`, `tpid`, `qtdate`, `qtno`, `uid`, `insurerid`,`insuclaimcode`, `insuno`, `created`)
		VALUES
		(:qtid, :tpid, :qtdate, :qtno, :uid, :insurerid, :insuclaimcode, :insuno, :created)';

	mydb::query($stmt,$qt);

	$querys[]=mydb()->_query;

	if (!mydb()->_error) {
		$qtid = $qt->qtid = $data->qtid = mydb()->insert_id;

		$stmt = 'UPDATE %garage_jobtr% SET `qtid` = :qtid WHERE `tpid` = :tpid AND `qtid` IS NULL';

		mydb::query($stmt,':tpid',$tpid, ':qtid',$qtid);

		$querys[]=mydb()->_query;

		// Create QT Transaction
		/*
		$job=new stdClass();
		$job->tpid=$tpid;
		$job->shopid=$shopId;
		$job->jobno=$data->jobno;
		$job->plate=$data->plate;
		$job->rcvdate=$data->rcvdate?sg_date($data->rcvdate,'Y-m-d'):date('Y-m-d');
		$job->templateid=$data->templateid;
		$job->customerid=$data->customerid;
		$job->brandid=$data->brandid;
		$stmt='INSERT INTO %garage_job%
			(`tpid`, `shopid`, `templateid`, `jobno`, `plate`, `rcvdate`, `customerid`, `brandid`)
			VALUES
			(:tpid, :shopid, :templateid, :jobno, :plate, :rcvdate, :customerid, :brandid)';
		mydb::query($stmt,$job);
		$querys[]=mydb()->_query;
		*/

		// Update lastno
		$stmt = 'UPDATE %garage_lastno% SET `lastno` = :lastno WHERE `shopid` = :shopid AND `docname` = "qt" LIMIT 1';
		mydb::query($stmt, ':shopid', $docShopId, ':lastno', $data->qtno);

	}

	if ($debug) {
		debugMsg($data,'$data');
		debugMsg($qt,'$qt');
		debugMsg($querys,'$querys');
	}

	return $qt;
}
?>