<?php
/**
* Format Model :: Format Model
* Created 2021-11-07
* Modify  2021-11-07
*
* @param Int $orgId
* @param Int $docName
* @param String $docShortName
* @return String
*/

class FormatModel {
	public static function get($orgId, $docName) {
		return mydb::clearprop(
			mydb::select(
				'SELECT `orgId`, `docName` `name`, `docFormat` `format`, `lastNo`, `resetOnPeriod` `reset`
				FROM %lastno%
				WHERE `orgId` = :orgId AND `docName` = :docName
				LIMIT 1',
				[':orgId' => $orgId, ':docName' => $docName]
			)
		);
	}

	public static function nextNo($orgId, $docName, $docFormat = NULL) {
		if (empty($orgId) || empty($docName)) return;

		$debug = false;
		//$formatReg = '/([.:\-\/]*[.:\-\/])/'; // use . : - / as separator
		//$formatRegNoSep = '/([yปm]*[yปm])/i'; // use . : - / as separator
		//$formatRegWithSep = '/([.:\-\/ymY]|ปปปป|ปป)/'; // use . : - / as separator
		$formatRegWithSep = '/([.:\-\/]|%%%Y|%Y|%M|%%%B|%B)/'; // use . : - / as separator
		$formatRegNoSep = '/([a-z]+)/i'; // use . : - / as separator
		$splitFlag = PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY;

		$currentValue = [];

		$docInfo = FormatModel::get($orgId, $docName);
		// debugMsg($docInfo, '$docInfo');

		if ($docInfo->format == 'MAIN') {
			$orgId = $docInfo->orgId;
			$docInfo = FormatModel::get($orgId, $docName);
		}

		$resetNoOnNextPeriod = $docInfo->resetOnPeriod;

		$docFormat = SG\getFirst($docInfo->format,strtoupper($docFormat));

		// If no docnum of shopid, create and call nextno again
		if (!$docInfo->format) {
			$docInfo = (Object) [
				'orgId' => $orgId,
				'name' => $docName,
				'format' => $docFormat,
				'lastNo' => '',
				'reset' => 0,
			];
			FormatModel::update($docInfo);
			if ($debug) debugMsg('Create new docformat : '.mydb()->_query);
			return FormatModel::nextNo($orgId, $docName);
		}

		// die('@'.date('H:i:s'));
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

		if ($debug) debugMsg('<b>Get Format Number of '.$docName.' "'.$docFormat.'" running='.$runningField.'  digit='.$digit.'</b>');

		// Prepare data
		$data['%%%Y'] = date('Y');
		$data['%Y'] = substr(date('Y'),2);
		$data['%%%B'] = $data['%%%Y']+543;
		$data['%B'] = substr($data['%%%Y']+543,2);
		$data['%M'] = date('m');
		$data[$formatList[$runningField]] = $formatList[$runningField];

		// Get last no and explode
		$lastNo = $docInfo->lastNo;
		if (empty($lastNo)) $lastNo = $docFormat;

		$nextNoList = FormatModel::explodeNo($lastNo,$formatList);

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
			$docInfo->format = $docFormat;
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

		return (Object) array('orgId' => $orgId, 'format' => $docFormat , 'nextNo' => $nextOrderNo);
	}

	public static function update($value = []) {
		if (is_array($value)) $value = (Object) $value;
		mydb::query(
			'INSERT INTO %lastno%
			(`orgId`, `docName`, `docFormat`, `lastNo`, `resetOnPeriod`)
			VALUES
			(:orgId, :docName, :docFormat, :lastNo, :resetOnPeriod)
			ON DUPLICATE KEY UPDATE
			`lastNo` = :lastNo',
			[
				':orgId' => $value->orgId,
				':docName' => $value->name,
				':docFormat' => $value->format,
				':lastNo' => $value->lastNo,
				':resetOnPeriod' => SG\getFirst($value->reset,0),
			]
		);
	}

	public static function explodeNo($lastNo, $formatList) {
		$result = array();
		//debugMsg($formatList,'$formatList');
		$idx = 0;
		foreach ($formatList as $key => $value) {
			$str = substr($lastNo,$idx,strlen($value));
			//debugMsg($idx.' '.strlen($value).' '.$value);
			$result[$key] = $str;
			$idx += strlen($value);
		}
		return $result;
	}
}
?>