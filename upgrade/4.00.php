<?php
$version = '4.00';

if (!mydb::columns('topic_files','folder')) {
	mydb::query(
		'ALTER TABLE %topic_files% ADD `folder` VARCHAR(50) NULL DEFAULT NULL AFTER `tagname`;'
	);
	$result[$version][]=array('Add field folder to topic_files.', mydb()->_query, mydb()->_error, mydb()->_error_no);
}
?>