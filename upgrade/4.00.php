<?php
/**
* Upgrade :: Upgrade table structor for version 4
* Created :: 2024-01-01
* Modify  :: 2025-06-11
* Version :: 3
*
* @return Array
*
* @usage admin/upgrade
*/

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

if (!mydb::columns('topic_revisions','css')) {
	mydb::query(
		'ALTER TABLE %topic_revisions%
		ADD `css` TEXT NULL DEFAULT NULL AFTER `redirect`,
		ADD `phpBackend` TEXT NULL DEFAULT NULL AFTER `css`,
		ADD `script` TEXT NULL DEFAULT NULL AFTER `phpBackend`,
		ADD `data` JSON NOT NULL DEFAULT "{}" AFTER `script`'
	);
	$result[$version][] = ['Add field css,phpBackend,script,data to topic_revisions.', mydb()->_query, mydb()->_error, mydb()->_error_no];
}

// if (!mydb::columns('watchdog','logDate')) {
// 	mydb::query(
// 		'ALTER TABLE %watchdog% ADD `logDate` DATE NULL DEFAULT NULL AFTER `date`, ADD INDEX (`logDate`)'
// 	);
// 	$result[$version][] = ['Add field logDate to watchdog.', mydb()->_query, mydb()->_error, mydb()->_error_no];
// }
?>