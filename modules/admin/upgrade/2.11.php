<?php
$version='2.11';

// Add email to topic
mydb::query('ALTER TABLE %topic% ADD `email` VARCHAR( 50 ) NULL DEFAULT NULL AFTER `poster` ');
$result[$version][]=array(
											'Add email to topic.',
											mydb()->_query,
											mydb()->_error,
											mydb()->_error_no
										);

mydb::query('ALTER TABLE %topic_files% ADD `download` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `last_reply` ,
							ADD `last_download` INT NULL DEFAULT NULL AFTER `download` ');
$result[$version][]=array(
											'Add download and last_download to topic_files.',
											mydb()->_query,
											mydb()->_error,
											mydb()->_error_no
										);

mydb::query('ALTER TABLE %tag% ADD `isdefault` ENUM( "Yes" ) NULL DEFAULT NULL AFTER `vid` ';
$result[$version][]=array(
											'Add isdefault to tag.',
											mydb()->_query,
											mydb()->_error,
											mydb()->_error_no
										);
?>