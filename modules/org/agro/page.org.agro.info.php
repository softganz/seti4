<?php
/**
* Module Method
* Created 2020-01-01
* Modify  2020-01-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function org_agro_info($self, $id) {
	$isAdmin = user_access('administer imeds');

	if (!$isAdmin) return message('error', 'Access Denied');

	$stmt = 'SELECT * FROM %agro_reg% WHERE `aid` = :aid LIMIT 1';

	$agroInfo = mydb::select($stmt, ':aid', $id);

	$ret = '<header class="header">'._HEADER_BACK.'<h3>'.$agroInfo->prename.$agroInfo->name.' '.$agroInfo->lname.'</h3></header>';

	mydb::clearprop($agroInfo);

	$tables = new Table();
	$tables->thead = array('Key', 'Value');

	foreach ($agroInfo as $key => $value) {
		$tables->rows[] = array($key, $value);
	}

	$ret .= $tables->build();

	//$ret .= print_o($agroInfo, '$agroInfo');
	return $ret;
}
?>