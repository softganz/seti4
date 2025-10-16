<?php
$version='2.12';

// Add email to topic
if (!mydb::table_exists('property')) {
	mydb::query('CREATE TABLE IF NOT EXISTS %property% (
							`propid` bigint(20) unsigned NOT NULL DEFAULT 0,
							`module` varchar(30) NOT NULL DEFAULT "",
							`name` varchar(30) NOT NULL DEFAULT "",
							`item` varchar(30) DEFAULT NULL,
							`value` varchar(255) DEFAULT NULL,
						PRIMARY KEY (`propid`,`module`,`name`),
						KEY `name` (`name`)
						)');
	$result[$version][]=array('Create table property.', mydb()->_query, mydb()->_error, mydb()->_error_no);
}
?>