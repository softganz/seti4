<?php
$version='3.01';

mydb::query('ALTER TABLE %tag%
								ADD `taggroup` VARCHAR( 30 ) NULL DEFAULT NULL AFTER `vid` ,
								ADD `process` TINYINT NULL DEFAULT NULL AFTER `taggroup` ,
								ADD INDEX ( `taggroup` ) ;';
$result[$version][]=array(
											'Add field taggroup,process to tag.',
											mydb()->_query,
											mydb()->_error,
											mydb()->_error_no
										);
?>