<?php
/**
* BMC :: Create New
* Created 2020-12-07
* Modify  2020-12-07
*
* @param Object $self
* @return String
*
* @usage bmc/new
*/

$debug = true;

function bmc_new($self) {
	$ret = '';

	$data = new stdClass();
	$data->uid = i()->uid;
	$data->title = 'New Title';
	$data->created = date('U');

	$stmt = 'INSERT INTO %bmc% (`uid`, `title`, `created`) VALUES (:uid, :title, :created)';

	mydb::query($stmt, $data);

	$bmcId = mydb()->insert_id;

	location('bmc/'.$bmcId);

	return $ret;
}
?>