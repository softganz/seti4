<?php
/**
* Garage Model :: Get Next Document Number
* Created 2017-08-21
* Modify  2020-10-07
*
* @param Int $shopId
* @param Int $docName
* @param String $docShortName
* @return String
*/

$debug = true;

function r_garage_nextno($shopId, $docName, $docShortname = NULL) {
	if (empty($shopId) || empty($docName)) return;

	$debug = false;
	//$formatReg = '/([.:\-\/]*[.:\-\/])/'; // use . : - / as separator
	//$formatRegNoSep = '/([yปm]*[yปm])/i'; // use . : - / as separator
	//$formatRegWithSep = '/([.:\-\/ymY]|ปปปป|ปป)/'; // use . : - / as separator
	$formatRegWithSep = '/([.:\-\/]|%%%Y|%Y|%M|%%%B|%B)/'; // use . : - / as separator
	$formatRegNoSep = '/([a-z]+)/i'; // use . : - / as separator
	$splitFlag = PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY;

	$currentValue = array();

	$stmt = 'SELECT l.*, s.`shopparent` FROM %garage_lastno% l LEFT JOIN %garage_shop% s USING(`shopid`) WHERE `shopid` = :shopid AND `docname` = :docname LIMIT 1';
	$docInfo = mydb::select($stmt,':shopid',$shopId, ':docname',$docName);

	if ($docInfo->docformat == 'MAIN') {
		$shopId = $docInfo->shopparent;
		$stmt = 'SELECT * FROM %garage_lastno% WHERE `shopid` = :shopid AND `docname` = :docname LIMIT 1';
		$docInfo = mydb::select($stmt,':shopid', $shopId, ':docname', $docName);
	}

	$resetNoOnNextPeriod = $docInfo->resetonperiod;

	$docFormat = SG\getFirst($docInfo->docformat,strtoupper($docShortname).'%B/0000');

	// If no docnum of shopid, create and call nextno again
	if ($docInfo->_empty) {
		mydb::query(
			'INSERT INTO %garage_lastno% (`shopid`,`docname`,`docformat`) VALUES (:shopid,:docname,:docformat)',
			':shopid', $shopId, ':docname', $docName, ':docformat', $docFormat
		);
		if ($debug) debugMsg('Create new docformat : '.mydb()->_query);
		return r_garage_nextno($shopId,$docName);
	}

	// Prepare format
	$formatReg = $formatRegWithSep;
	$formatList = preg_split($formatReg,$docFormat,-1,$splitFlag);

	// No separator
	if ($formatList[0] == $docFormat) {
		$formatReg = $formatRegNoSep;
		$formatList = preg_split($formatRegNoSep,$docFormat,-1,$splitFlag);
	}

	// Find last 0 for running no
	$runningField = '';
	foreach ($formatList as $key => $value) {
		if (substr($value,0,1) == '0') $runningField = $key;
	}
	$digit = strlen($formatList[$runningField]);

	if ($debug) debugMsg('<b>Get jobno format "'.$docFormat.'" running='.$runningField.'  digit='.$digit.'</b>');




	// Prepare data
	$data['%%%Y'] = date('Y');
	$data['%Y'] = substr(date('Y'),2);
	$data['%%%B'] = $data['%%%Y']+543;
	$data['%B'] = substr($data['%%%Y']+543,2);
	$data['%M'] = date('m');
	$data[$formatList[$runningField]] = $formatList[$runningField];

	// Get last no and explode
	$stmt = 'SELECT `lastno` FROM %garage_lastno% WHERE `shopid` = :shopid AND `docname` = :docname LIMIT 1';
	$lastNo=mydb::select($stmt, ':shopid', $shopId, ':docname', $docName)->lastno;
	if (empty($lastNo)) $lastNo = $docFormat;

	//$nextNoList=preg_split($formatReg,$lastNo,-1,$splitFlag);
	$nextNoList = __r_garage_nextno_explode($lastNo,$formatList);
	//debugMsg($nextNoList,'$nextNoList');

	foreach ($formatList as $key => $value) {
		$currentValue[$value] = $nextNoList[$key];
	}

	// Create new no on format change
	if (count($nextNoList) != count($formatList)) $nextNoList = array();

	$nextNo = $nextNoList[$runningField]+1;

	// Reset running number on format that contain month or year and start new period
	if ($resetNoOnNextPeriod) {
		if (in_array('%M', $formatList) && $data['%M'] != $currentValue['%M']) $nextNo = 1;
		if (in_array('%%%B', $formatList) && $data['%%%B'] != $currentValue['%%%B']) $nextNo = 1;
		if (in_array('%B', $formatList) && $data['%B'] != $currentValue['%B']) $nextNo = 1;
		if (in_array('%%%Y', $formatList) && $data['%%%Y'] != $currentValue['%%%Y']) $nextNo = 1;
		if (in_array('%Y', $formatList) && $data['%Y'] != $currentValue['%Y']) $nextNo = 1;
	}



	// Increment digit if next order no greater than current digit and save new format
	if ($nextNo >= pow(10,$digit)) {
		$digit++;
		$formatList[$runningField] = str_repeat('0', $digit);
		$docFormat = implode('', $formatList);
		if ($debug) debugMsg('Update new doc format on digit overload to '.$docFormat);
		mydb::query(
			'UPDATE %garage_lastno% SET `docformat` = :docformat WHERE `shopid` = :shopid AND `docname` = :docname LIMIT 1',
			':shopid', $shopId, ':docname', $docName, ':docformat', $docFormat
		);
	}
	$data[$formatList[$runningField]] = sprintf('%0'.$digit.'d',$nextNo);

	// Generate next order no
	foreach ($formatList as $key => $value) {
		if (array_key_exists($value, $data)) $nextNoList[$key] = $data[$value];
		else $nextNoList[$key] = $value;
	}

	$nextOrderNo = implode('', $nextNoList);

	if ($debug) {
		debugMsg('<b>Return doc number :: '.$nextOrderNo.'</b>');
		debugMsg($data,'$data');
		debugMsg($currentValue,'$currentValue');
		debugMsg($nextNoList,'$nextNoList');
		debugMsg($formatList,'$formatList');
		debugMsg($docInfo,'$docInfo');
	}


	/*
	// if no is exist then save and get next
	$isNoExists=mydb::select('SELECT `tpid` FROM %garage_job% WHERE `jobno`=:jobno LIMIT 1',':jobno',$nextOrderNo)->tpid;
	if ($debug) debugMsg(mydb()->_query);
	if ($debug) debugMsg('$isNoExists='.$isNoExists);
	if ($isNoExists) {
		cfg_db($cfgKey,$nextOrderNo);
		return r_garage_nextno($shopId,$refkey);
	}
	*/
	return (Object) array('shopId' => $shopId, 'format' => $docFormat , 'nextNo' => $nextOrderNo);
}

function __r_garage_nextno_explode($lastNo, $formatList) {
	$result = array();
	//debugMsg($formatList,'$formatList');
	$idx=0;
	foreach ($formatList as $key => $value) {
		$str = substr($lastNo,$idx,strlen($value));
		//debugMsg($idx.' '.strlen($value).' '.$value);
		$result[$key] = $str;
		$idx += strlen($value);
	}
	return $result;
}
?>