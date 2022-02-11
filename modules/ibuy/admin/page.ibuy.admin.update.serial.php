<?php
/**
* Module Method
* Created 2019-11-01
* Modify  2019-11-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function ibuy_admin_update_serial($self) {
	$ret = '';

	$stmt = 'UPDATE %ibuy_serial% s
		LEFT JOIN %ibuy_customer% c ON c.`custcode` = s.`cuscod`
		SET s.`custid` = c.`custid`
	';

	mydb::query($stmt);

	$ret .= mydb()->_query.'<br />';

	// Update STKDES to serial.stkdesc
	$stmt = 'UPDATE %ibuy_serial% s
		LEFT JOIN `STMAS` m ON m.`STKCOD`=s.`STKCOD`
		SET s.`stkdesc` = m.`STKDES`
		WHERE m.`STKDES` IS NOT NULL';
	mydb::query($stmt);


	$ret .= mydb()->_query.'<br />';

	return $ret;
}
?>