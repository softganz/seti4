<?php
/**
* QT :: Installation
* Created 2020-12-13
* Modify  2020-12-13
*
* @usage admin/site/module?module=qt
*/

function module_qt_install() {
	$ret = '<h3>QT installation</h3>';

	$stmt = 'CREATE TABLE IF NOT EXISTS %qtform% (
		`frmid` int(11) NOT NULL,
		`name` varchar(255) DEFAULT NULL,
		`schema` text DEFAULT NULL,
		PRIMARY KEY (`frmid`)
		);';

	mydb::query($stmt);
	$queryResult[] = mydb()->_query;

	$stmt = 'CREATE TABLE IF NOT EXISTS %qtgroup% (
		`qtgrid` int(11) NOT NULL AUTO_INCREMENT,
		`template` varchar(20) DEFAULT NULL,
		`name` varchar(200) DEFAULT NULL,
		PRIMARY KEY (`qtgrid`)
		);';

	mydb::query($stmt);
	$queryResult[] = mydb()->_query;

	$stmt = 'CREATE TABLE IF NOT EXISTS %qtmast% (
		`qtref` int(11) NOT NULL AUTO_INCREMENT,
		`qtgroup` int(10) UNSIGNED DEFAULT NULL,
		`qtform` varchar(20) DEFAULT NULL,
		`tpid` int(10) UNSIGNED DEFAULT NULL,
		`orgid` int(10) UNSIGNED DEFAULT NULL,
		`psnid` int(11) DEFAULT NULL,
		`qtdate` date DEFAULT NULL,
		`qtstatus` tinyint(4) DEFAULT 0,
		`uid` int(10) UNSIGNED DEFAULT NULL,
		`seq` int(10) UNSIGNED DEFAULT NULL,
		`collectname` varchar(50) DEFAULT NULL,
		`value` decimal(12,2) DEFAULT NULL,
		`data` text DEFAULT NULL,
		`created` bigint(20) DEFAULT NULL,
		PRIMARY KEY (`qtref`),
		KEY `psnid` (`psnid`),
		KEY `uid` (`uid`),
		KEY `created` (`created`),
		KEY `qtgroup` (`qtgroup`),
		KEY `qtform` (`qtform`),
		KEY `qtdate` (`qtdate`),
		KEY `seq` (`seq`),
		KEY `tpid` (`tpid`),
		KEY `orgid` (`orgid`),
		KEY `qtstatus` (`qtstatus`)
		);';

	mydb::query($stmt);
	$queryResult[] = mydb()->_query;

	$stmt = 'CREATE TABLE IF NOT EXISTS %qttran% (
		`qtid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT "Questionnaire auto ID",
		`qtref` int(10) UNSIGNED DEFAULT NULL,
		`part` varchar(100) NOT NULL COMMENT "Questionnaire part",
		`rate` int(11) DEFAULT NULL,
		`value` varchar(256) DEFAULT NULL COMMENT "Questionnaire value",
		`ucreated` int(10) UNSIGNED NOT NULL COMMENT "Created by user ID",
		`dcreated` bigint(20) UNSIGNED NOT NULL COMMENT "Date created",
		`umodify` int(10) UNSIGNED DEFAULT NULL COMMENT "Modify by user ID",
		`dmodify` bigint(20) UNSIGNED DEFAULT NULL COMMENT "Date modify",
		PRIMARY KEY (`qtid`),
		KEY `part` (`part`),
		KEY `ucreated` (`ucreated`),
		KEY `umodify` (`umodify`),
		KEY `qtref` (`qtref`)
		);';

	mydb::query($stmt);
	$queryResult[] = mydb()->_query;

	$ret .= '<p><strong>Installation completed.</strong></p>';
	$ret .= '<ul><li>'.implode('</li><li>',$queryResult).'</li></ul>';

	return $ret;
}
?>