<?php
$version='3.01';

mydb::query('ALTER TABLE %tag%
								ADD `ownid` INT(10) unsigned NULL DEFAULT NULL AFTER `vid` ,
								ADD `taggroup` VARCHAR( 30 ) NULL DEFAULT NULL AFTER `ownid` ,
								ADD `catid` INT(10) unsigned NULL DEFAULT NULL AFTER `taggroup` ,
								ADD `catparent` INT(10) unsigned NULL DEFAULT NULL AFTER `catid` ,
								ADD `process` TINYINT NULL DEFAULT NULL AFTER `catparent` ,
								ADD INDEX ( `taggroup` ),
								ADD INDEX ( `catid` ),
								ADD INDEX ( `ownid` ),
								ADD INDEX ( `catparent` )
								;';
$result[$version][]=array(
											'Add field taggroup,process to tag.',
											mydb()->_query,
											mydb()->_error,
											mydb()->_error_no
										);

mydb::query('ALTER TABLE %watchdog%
								ADD `keyid` BIGINT NULL DEFAULT NULL AFTER `keyword` ,
								ADD `fldname` VARCHAR(100) NULL DEFAULT NULL AFTER `keyid` ,
								ADD INDEX ( `keyid` ) ,
								ADD INDEX ( `fldname` )
								 ;';
$result[$version][]=array(
											'Add field keyid,fldname to watchdog.',
											mydb()->_query,
											mydb()->_error,
											mydb()->_error_no
										);

?>