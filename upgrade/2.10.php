<?php
$version='2.10';

// Add timestamp key to topic comment
	mydb::query('ALTER TABLE %topic_comments% ADD INDEX ( `timestamp` )  ');

// Add tag property to tag
mydb::query('ALTER TABLE %tag% ADD `liststyle` VARCHAR( 10 ) NULL DEFAULT NULL AFTER `weight`  ');
$result[$version][]=array('Add tag property to tag.', mydb()->_query, mydb()->_error, mydb()->_error_no);

// Add field fkey into topic_file
mydb::query('ALTER TABLE %topic_files% ADD `fkey` VARCHAR( 10 ) NULL DEFAULT NULL AFTER `fid` , ADD UNIQUE (`fkey`);');
$result[$version][]=array('Add field fkey to topic_file.', mydb()->_query, mydb()->_error, mydb()->_error_no);

// Adding wating flag to users status
mydb::query('ALTER TABLE %users% CHANGE `status` `status` ENUM( "enable", "disable", "block", "waiting" ) NULL DEFAULT NULL ');
$result[$version][]=array('Add wating flag to users status.', mydb()->_query, mydb()->_error, mydb()->_error_no);

// Adding field admin_remark to users
mydb::query('ALTER TABLE %users% ADD `admin_remark` TEXT NULL DEFAULT NULL ');
$result[$version][]=array('Adding field admin_remark to users.', mydb()->_query, mydb()->_error, mydb()->_error_no);

// Add field tpid to calendar
if (!mydb::columns('calendar','tpid')) {
	mydb::query('ALTER TABLE  %calendar%
									ADD  `tpid` INT UNSIGNED NULL DEFAULT NULL AFTER  `id` ,
									ADD INDEX (  `tpid` )');
	$result[$version][]=array('Add field tpid to calendar', mydb()->_query, mydb()->_error, mydb()->_error_no);
}
?>
