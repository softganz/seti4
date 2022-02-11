<?php
/**
* Module Method
* Created 2019-09-01
* Modify  2019-09-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_admin_person_query($self) {
	$ret = '';

	$filename = 'upload/person_query.txt';
	/*
	$fp = fopen('upload/person_query.txt', 'r');
	fread($fp, $debugText);
	fclose($fp);
	*/

	$file = file($filename);

	$tables = new Table();
	$tables->thead = array('no'=>'','time -date'=>'time','uid', 'query -nowrap'=>'query', 'name -nowrap'=>'name', 'lname -nowrap'=>'lname', 'from', 'browser');
	$no = 0;

	foreach ($file as $line) {
		$line = rtrim(trim($line), ',');
		$item = sg_json_decode($line);
		$tables->rows[] = array(
			++$no,
			sg_date($item->time,'Y-m-d').'<br />'.sg_date($item->time,'H:i:s'),
			$item->uid,
			$item->q,
			$item->name,
			$item->lname,
			$item->callfrom,
			$item->browser,
		);
	}

	$ret .= $tables->build();

	return $ret;
}
?>