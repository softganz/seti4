<?php
/**
* Module Method
*
* @param Object $self
* @param Int $orgId
* @param String $action
* @param Int $actionId
* @return String
*/

function test_sql_hack($self) {
	mydb()->_debug = true;
	mydb::where(NULL, ':uid', post('uid'));
	mydb::value('$LIMIT',post('limit'));
	mydb::value('$FLD',post('f'));
	// $ret .= print_o(R(),'R()');
	$ret .= print_o(mydb()->_values, '_value');
	$stmt = 'SELECT $FLD FROM %users% WHERE `uid` = :uid LIMIT $LIMIT';
	$rs = mydb::select($stmt, ':uid', post('uid'));
	$ret .= print_o($rs,'$rs');
	$ret .= print_o(mydb(), 'mydb()');

	$newDb = new MyDb();
	$ret .= print_o($newDb, '$newDb');
	return $ret;
}
?>