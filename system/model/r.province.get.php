<?php
function r_province_get($provid) {
	$result=new stdClass();
	$rs=mydb::select('SELECT * FROM %co_province% WHERE `provid`=:provid LIMIT 1');
	if ($rs->_num_rows) {
		$result->provid=$rs->provid;
		$result->name=$rs->provname;
	}
	return $result;
}
?>