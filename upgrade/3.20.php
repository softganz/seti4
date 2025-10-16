<?php
$version='3.20';

// Change table calendar add field village, tambon, ampur, changwat and add index
if (mydb::table_exists('calendar')) {
	if (!mydb::columns('calendar','options')) {
		mydb::query('ALTER TABLE %calendar% ADD `options` TEXT NULL DEFAULT NULL AFTER `repeat`;');
		$result[$version][] = array('Change table calendar add field options.', mydb()->_query, mydb()->_error, mydb()->_error_no );

		mydb::query('UPDATE %calendar% c
			LEFT JOIN %property% p ON p.`propid` = c.`id` AND p.`module`="calendar" AND p.`name` = "color"
			SET c.`options` = CONCAT(\'{"color": "\',p.`value`,\'"}\');');
		$result[$version][] = array('Upgrade table calendar option from property.', mydb()->_query, mydb()->_error, mydb()->_error_no );

	}

	if (!mydb::columns('calendar','orgid')) {
		mydb::query('ALTER TABLE %calendar% ADD `orgid` INT UNSIGNED NULL DEFAULT NULL AFTER `tpid`, ADD INDEX (`orgid`);');
		$result[$version][] = array('Upgrade table calendar add field orgid.', mydb()->_query, mydb()->_error, mydb()->_error_no );
	}

}

if (!mydb::columns('topic','areacode')) {
	mydb::query('ALTER TABLE %topic% ADD `areacode` VARCHAR(8) NULL DEFAULT NULL AFTER `email`, ADD INDEX (`areacode`);');
	$result[$version][]=array('Add field areacode to topic.', mydb()->_query, mydb()->_error, mydb()->_error_no);
}

if (!mydb::columns('users', 'areacode')) {
	mydb::query('ALTER TABLE %users% ADD `areacode` VARCHAR(8) NULL DEFAULT NULL AFTER `address`, ADD INDEX (`areacode`);');
	$result[$version][]=array('Add field areacode to users.', mydb()->_query, mydb()->_error, mydb()->_error_no);
}

if (!mydb::columns('topic','approve')) {
	mydb::query('ALTER TABLE %topic% ADD `approve` enum("LEARN","USE","MASTER") NOT NULL DEFAULT "LEARN" AFTER `status`, ADD INDEX (`approve`);');
	$result[$version][]=array('Add field approve to topic.', mydb()->_query, mydb()->_error, mydb()->_error_no);
}

if (mydb::table_exists('db_person')) {
	if (!mydb::columns('db_person','userId')) {
		mydb::query('ALTER TABLE %db_person% ADD `userId` INT(10) NULL DEFAULT NULL AFTER `uid`, ADD INDEX (`userId`);');
		$result[$version][]=array('Change table db_person add field userId.', mydb()->_query, mydb()->_error, mydb()->_error_no );
	}

	if (!mydb::columns('db_person','areacode')) {
		mydb::query('ALTER TABLE %db_person% ADD `areacode` VARCHAR(8) NULL DEFAULT NULL COMMENT "Home Area Code" AFTER `birth`, ADD `hrareacode` VARCHAR(8) NULL DEFAULT NULL COMMENT "House Registration Area Code" AFTER `areacode`, ADD INDEX (`areacode`), ADD INDEX (`hrareacode`);');
		$result[$version][]=array('Change table db_person add field areacode,hrareacode.', mydb()->_query, mydb()->_error, mydb()->_error_no );
	}

	if (!mydb::columns('db_person','module')) {
		mydb::query('ALTER TABLE %db_person% ADD `module` VARCHAR(10) NULL DEFAULT NULL AFTER `userid`, ADD INDEX (`module`);');
		$result[$version][]=array('Change table db_person add field module.', mydb()->_query, mydb()->_error, mydb()->_error_no );
	}


}

?>