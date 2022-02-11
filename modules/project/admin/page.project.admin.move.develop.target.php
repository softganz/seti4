<?php
/**
* Move target of develop from table project_tr to project_target
*
* @param Object $self
* @return String
*/

$debug = true;

function project_admin_move_develop_target($self) {
	$ret = '';

	if (SG\confirm()) {
		if (post('deletetarget')) {
			$stmt = 'DELETE FROM %project_target% WHERE `tagname` = "develop";';
			mydb::query($stmt);
			$ret .= mydb()->_query.'<br />';
		}


		$stmt = 'INSERT IGNORE INTO %project_target%
			(`tpid`,`tagname`,`tgtid`,`amount`)
			SELECT `tpid`,"develop",IFNULL(`refid`,`detail1`),`num1`
			FROM %project_tr%
			WHERE `formid` = "develop" AND `part` = "target" AND `num1`>0
			';	
		mydb::query($stmt);
		$ret .= mydb()->_query.'<br />';

		if (post('deletetr')) {
			$stmt = 'DELETE FROM %project_tr% WHERE `formid` = "develop" AND `part` = "target" ';
			mydb::query($stmt);
			$ret .= mydb()->_query.'<br />';
		}
	} else {
		$ret .= 'PLEASE CONFIRM?';
	}

	return $ret;
}
?>