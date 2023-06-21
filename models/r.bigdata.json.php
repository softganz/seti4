<?php
/**
* BigData Model :: Save data in JSON
* Created 2020-10-13
* Modify  2020-10-13
*
* @param Object $data
* @param Object $options
* @return Object
*/

$debug = true;

function r_bigdata_json($action, $keyName, $keyId, $fldName, $dataGroup = NULL, $dataKey = NULL, $dataValue = NULL) {
	$result = NULL;

	//$dataValue = str_replace(array("\r\n","\r","\n"), array('<br />','<br />','<br />'), htmlspecialchars($dataValue));
	//$dataValue = nl2br(htmlspecialchars($dataValue));

	$dataValue = preg_replace('/[\r\n]+/', '', nl2br(htmlspecialchars(trim($dataValue))));

	$data = new stdClass();
	$data->bigid = NULL;
	$data->keyname = $keyName;
	$data->keyid = $keyId;
	$data->fldname = $fldName;

	$result->data = $data;


	$currentData = mydb::select('SELECT `bigid`, `flddata` FROM %bigdata% WHERE `keyname` =  :keyname AND `keyid` = :keyid AND `fldname` = :fldname LIMIT 1', $data);
	$result->_query[] = mydb()->_query;

	if ($action == 'get') {
		return \SG\json_decode($currentData->flddata);
	} else if ($action == 'delete' && $currentData->bigid) {
		mydb::query('DELETE FROM %bigdata% WHERE `bigid` = :bigid LIMIT 1', ':bigid', $currentData->bigid);
		return true;
	}

	$data->bigid = $currentData->bigid;
	$data->flddata = \SG\json_decode($currentData->flddata);
	$data->created = $data->modified = date('U');
	$data->ucreated = $data->umodified = i()->uid;

	if ($dataGroup) {
		if ($action == 'remove') {
			unset($data->flddata->{$dataGroup}->{$dataKey});
		} else {
			$data->flddata->{$dataGroup}->{$dataKey} = $dataValue;
		}
	} else {
		if ($action == 'remove') {
			unset($data->flddata->{$dataKey});
		} else {
			$data->flddata->{$dataKey} = $dataValue;
		}
	}
	$data->flddata = \SG\json_encode($data->flddata);

	$stmt = 'INSERT INTO %bigdata%
		(`bigid`, `keyname`, `keyid`, `fldname`, `fldtype`, `flddata`, `created`, `ucreated`)
		VALUES
		(:bigid, :keyname, :keyid, :fldname, "JSON", :flddata, :created, :ucreated)
		ON DUPLICATE KEY UPDATE `flddata` = :flddata, `modified` = :modified, `umodified` = :umodified';

	mydb::query($stmt, $data);

	$result->_query[] = mydb()->_query;

	return $result;
}
?>