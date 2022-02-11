<?php
/**
* iMed :: Save Person Information In BigData
* Created 2020-12-21
* Modify  2020-12-21
*
* @param Object $data
* @param Object $options
* @return Object Data Set
*
* @usage R::Model("imed.person.save", $psnInfo->person, $options)
*/

$debug = true;

function r_imed_person_save($psnId, $data, $options = '{}') {
	$defaults = '{debug: false}';
	$options = SG\json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	$bigId = 'SELECT `bigid` FROM %bigdata% WHERE `keyname` = "imed" AND `keyid` = :keyid LIMIT 1';
	$dataJSON = SG\json_encode($data);

	$bigData = new stdClass();
	$bigData->keyname = 'imed';
	$bigData->fldname = 'person';
	$bigData->keyid = $psnId;
	$bigData->bigid = mydb::select('SELECT `bigid` FROM %bigdata% WHERE `keyname` = :keyname AND `fldname` = :fldname AND `keyid` = :keyid LIMIT 1', $bigData)->bigid;
	$bigData->fldtype = 'JSON';
	$bigData->flddata = SG\json_encode($data);
	$bigData->created = $bigData->modified = date('U');
	$bigData->ucreated = $bigData->umodified = i()->uid;


	$stmt = 'INSERT INTO %bigdata%
		(`bigid`, `keyname`, `keyid`, `fldname`, `fldtype`, `flddata`, `created`, `ucreated`)
		VALUES
		(:bigid, :keyname, :keyid, :fldname, :fldtype, :flddata, :created, :ucreated)
		ON DUPLICATE KEY UPDATE
		`flddata` = :flddata
		, `modified` = :modified
		, `umodified` = :umodified
		';

	mydb::query($stmt, $bigData);
	//debugMsg(mydb()->_query);

	return $result;
}
?>