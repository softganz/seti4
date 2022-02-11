<?php
/**
* Update Open Balance
* Created 2017-04-08
* Modify  2019-05-24
*
* @param Object $self
* @param Array $_POST
* @return Array
*/

$debug = true;

function saveup_gl_openbalance_update($self) {
	$mid = post('mid');
	$fld = post('fld');
	$value = sg_strip_money(post('value'));

	$ret['msg'] = '';

	if (mydb::select('SELECT mid FROM %saveup_memcard% WHERE mid=:mid AND `card`=:card AND refno IS NULL AND trno IS NULL LIMIT 1',':mid',$mid,':card',$fld)->_empty) {
		mydb::query('INSERT INTO %saveup_memcard% (mid, `card`, `date`, `refno`, `trno`) VALUES (:mid, :card, NULL, NULL, NULL)',':mid',$mid,':card',$fld);
		//$ret['msg'] .= mydb()->_query.'<br />';
	}

	$stmt = 'UPDATE %saveup_memcard% SET `amt` = :value WHERE mid = :mid AND `card` = :card AND trno IS NULL LIMIT 1';
	mydb::query($stmt,':mid',$mid,':value',$value,':card',$fld);
	//$ret['msg'] .= mydb()->_query.'<br />';

	$stmt = 'SELECT `amt` FROM %saveup_memcard% WHERE mid = :mid AND `card` = :card AND `trno` IS NULL LIMIT 1';
	$ret['value'] = number_format(mydb::select($stmt,':mid',$mid,':card',$fld)->amt,2);

	//$ret['msg'] .= mydb()->_query.'<br />';
	//$ret['msg'] .= print_o(post(),'post()');
	return $ret;
}
?>