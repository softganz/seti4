<?php
/**
 * Module upgrade
 *
 * User permission to access menu
 *
 * @return String
 */
function imed_admin_upgrade($self) {
	//$self->theme->title='iMed Upgrade';
	//$self->__set_top_menu();

	mydb::query('ALTER TABLE %imed_service% ADD `ox` TEXT NULL DEFAULT NULL AFTER `rx` ');

	mydb::query('ALTER TABLE %imed_people% ADD `phn` VARCHAR( 10 ) NULL DEFAULT NULL AFTER `hn` , ADD INDEX ( phn );');
	if (mydb()->_error) {
		$error[] = mydb()->_error;
	} else {
		mydb::query('UPDATE %imed_people% SET `phn`=`hn` WHERE 1;');
	}

	mydb::query('ALTER TABLE %imed_service% ADD `phn` VARCHAR( 10 ) NULL DEFAULT NULL AFTER `hn` , ADD INDEX ( phn );');
	if (mydb()->_error) {
		$error[]=mydb()->_error;
	} else {
		mydb::query('UPDATE %imed_service% SET `phn`=`hn` WHERE `hn` IS NOT NULL;');
		mydb::query('UPDATE %imed_service% SET `hn`=NULL WHERE 1;');
	}

	mydb::query('ALTER TABLE %imed_service% ADD `vitalsigns` TEXT NULL DEFAULT NULL AFTER `phn` ');
	mydb::query('ALTER TABLE %imed_people% ADD `pcucode` CHAR( 5 ) NULL DEFAULT NULL FIRST , ADD INDEX ( pcucode ) ');
	mydb::query('ALTER TABLE %imed_people% ADD `pid` VARCHAR( 13 ) NULL DEFAULT NULL AFTER `pcucode` ');
	mydb::query('ALTER TABLE %imed_people% ADD `lname` VARCHAR( 50 ) NULL DEFAULT NULL AFTER `name` ');
	mydb::query('ALTER TABLE %imed_service% ADD `maindx` VARCHAR( 255 ) NULL DEFAULT NULL AFTER `vitalsigns`');

	if ($error) $ret.=message('error',$error);
	else $ret.=message('status','Upgrade complete');
	return $ret;
}

function _up200() {
	$stmts[]='ALTER TABLE %imed_service%
						CHANGE `vitalsigns_temp` `temperature` DECIMAL( 4, 2 ) UNSIGNED NULL DEFAULT NULL ,
						CHANGE `vitalsigns_wg` `weight` DECIMAL( 5, 2 ) UNSIGNED NULL DEFAULT NULL ,
						CHANGE `vitalsigns_ht` `pulse` TINYINT( 3 ) UNSIGNED NULL DEFAULT NULL ,
						CHANGE `vitalsigns_bt` `respiratoryrate` INT( 3 ) UNSIGNED NULL DEFAULT NULL ,
						CHANGE `vitalsigns_pr` `bloodpressure` VARCHAR( 7 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
						CHANGE `maindx` `majorsymptom` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ,
						CHANGE `dx` `symptompresent` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ';

	$stmts[]='ALTER TABLE %imed_service%
						ADD `timedata` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `ox` ,
						ADD INDEX ( `timedata` ) ';

	$stmts[]='ALTER TABLE %imed_service%
						ADD `service` ENUM( "Web Distance Treatment", "Treatment", "Home Visit", "Take notes" ) NULL DEFAULT NULL
						COMMENT "รูปแบบของการบันทึกข้อมูล" AFTER `pid` ,
						ADD INDEX ( `service` ) ';

	$stmts[]='UPDATE %imed_service%
						SET `service`="Web Distance Treatment",`timedata`=`created`
						WHERE `service` IS NULL;';

	$stmts[]='ALTER TABLE %imed_service% ADD `height` INT NULL DEFAULT NULL AFTER `weight` ';

	$stmts[]='ALTER TABLE %db_person%
							ADD `commune` VARCHAR( 100 ) NOT NULL AFTER `village` ,
							ADD `importseries` INT UNSIGNED NULL DEFAULT NULL ,
							ADD `importtype` ENUM( "update" ) NULL DEFAULT NULL,
							ADD INDEX ( `importseries` ),
							ADD INDEX ( `commune` )
							';


}
?>