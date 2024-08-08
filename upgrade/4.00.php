<?php
$version = '4.00';

if (!mydb::columns('topic_files','folder')) {
	mydb::query(
		'ALTER TABLE %topic_files% ADD `folder` VARCHAR(50) NULL DEFAULT NULL AFTER `tagname`'
	);
	$result[$version][] = ['Add field folder to topic_files.', mydb()->_query, mydb()->_error, mydb()->_error_no];
}

if (!mydb::columns('bigdata','data')) {
	mydb::query(
		'ALTER TABLE %bigdata% ADD `data` JSON NOT NULL DEFAULT "{}" AFTER `fldref`'
	);
	$result[$version][] = ['Add field data to bigdata.', mydb()->_query, mydb()->_error, mydb()->_error_no];
}
?>