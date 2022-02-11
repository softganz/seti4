<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_pocenter_create($self, $orgId) {
	$ret = '';

	if ($orgId) {
		$data = new stdClass;
		$data->orgid = $orgId;
		$data->servname = 'POCENTER';
		$data->uid = i()->uid;
		$data->active = 1;
		$data->created = date('U');
		$stmt = 'INSERT INTO %org_service% ( `orgid`, `servname`, `uid`, `active`, `created` ) VALUES ( :orgid, :servname, :uid, :active, :created )';
		mydb::query($stmt, $data);
		//$ret .= mydb()->_query;
		$ret .= 'ลงทะเบียนศูนย์กายอุปกรณ์เรียบร้อย';
	}

	return $ret;
}
?>