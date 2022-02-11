<?php
function module_org_install() {

	$stmt = 'CREATE TABLE IF NOT EXISTS %db_org% (
		`orgid` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
		`uid` INT(10) UNSIGNED DEFAULT NULL,
		`parent` INT(10) UNSIGNED DEFAULT NULL,
		`sector` INT(10) UNSIGNED DEFAULT NULL,
		`template` VARCHAR(40) DEFAULT NULL,
		`name` VARCHAR(255) DEFAULT NULL,
		`shortname` VARCHAR(20) DEFAULT NULL,
		`enshortname` VARCHAR(20) DEFAULT NULL,
		`groupType` VARCHAR(30) DEFAULT NULL,
		`areacode` VARCHAR(8) DEFAULT NULL,
		`house` VARCHAR(255) DEFAULT NULL,
		`zipcode` VARCHAR(5) DEFAULT NULL,
		`phone` VARCHAR(50) DEFAULT NULL,
		`fax` VARCHAR(30) DEFAULT NULL,
		`email` VARCHAR(50) DEFAULT NULL,
		`website` VARCHAR(100) DEFAULT NULL,
		`facebook` VARCHAR(100) DEFAULT NULL,
		`location` VARCHAR(30) DEFAULT NULL,
		`mission` TEXT DEFAULT NULL,
		`managername` VARCHAR(50) DEFAULT NULL,
		`managerphone` VARCHAR(50) DEFAULT NULL,
		`manageremail` VARCHAR(50) DEFAULT NULL,
		`contactname` VARCHAR(255) DEFAULT NULL,
		`contactphone` VARCHAR(50) DEFAULT NULL,
		`contactemail` VARCHAR(50) DEFAULT NULL,
		`contactdbname` VARCHAR(50) DEFAULT NULL,
		`contactdbphone` VARCHAR(50) DEFAULT NULL,
		`contactdbemail` VARCHAR(50) DEFAULT NULL,
		`created` BIGINT(20) DEFAULT NULL,
		PRIMARY KEY (`orgid`),
		KEY `name` (`name`),
		KEY `sector` (`sector`),
		KEY `parent` (`parent`),
		KEY `shortname` (`shortname`),
		KEY `enshortname` (`enshortname`),
		KEY `areacode` (`areacode`),
		KEY `zipcode` (`zipcode`),
		KEY `groupType` (`groupType`)
		) ;';

	mydb::query($stmt);
	$queryResult[] = mydb()->_query;


	$stmt = 'CREATE TABLE IF NOT EXISTS %db_person% (
		`psnid` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
		`uid` INT(10) UNSIGNED DEFAULT NULL,
		`userId` INT(10) UNSIGNED DEFAULT NULL,
		`pcucode` CHAR(5) DEFAULT NULL,
		`cid` VARCHAR(20) DEFAULT NULL,
		`pid` VARCHAR(13) DEFAULT NULL,
		`hid` VARCHAR(14) DEFAULT NULL,
		`prename` VARCHAR(20) NOT NULL,
		`name` VARCHAR(50) NOT NULL,
		`lname` VARCHAR(50) NOT NULL,
		`nickname` VARCHAR(20) NOT NULL,
		`hn` VARCHAR(6) DEFAULT NULL,
		`sex` ENUM("ชาย","หญิง") DEFAULT NULL,
		`birth` DATE DEFAULT NULL,
		`areacode` VARCHAR(8) DEFAULT NULL COMMENT "Home Area Code",
		`hrareacode` VARCHAR(8) DEFAULT NULL COMMENT "House Registration Area Code",
		`house` VARCHAR(255) NOT NULL DEFAULT "",
		`village` CHAR(2) NOT NULL DEFAULT "",
		`commune` VARCHAR(100) NOT NULL DEFAULT "",
		`tambon` CHAR(2) NOT NULL DEFAULT "",
		`t_tambon` VARCHAR(30) NOT NULL DEFAULT "",
		`ampur` CHAR(2) NOT NULL DEFAULT "",
		`t_ampur` VARCHAR(30) NOT NULL DEFAULT "",
		`changwat` CHAR(2) NOT NULL DEFAULT "",
		`t_changwat` VARCHAR(50) NOT NULL DEFAULT "",
		`zip` VARCHAR(5) NOT NULL DEFAULT "",
		`rhouse` VARCHAR(255) NOT NULL DEFAULT "",
		`rvillage` VARCHAR(2) NOT NULL DEFAULT "",
		`rtambon` VARCHAR(2) NOT NULL DEFAULT "",
		`rampur` VARCHAR(2) NOT NULL DEFAULT "",
		`rchangwat` VARCHAR(2) NOT NULL DEFAULT "",
		`rzip` VARCHAR(5) NOT NULL DEFAULT "",
		`mstatus` INT(11) DEFAULT NULL,
		`occupa` VARCHAR(3) DEFAULT NULL,
		`race` VARCHAR(3) DEFAULT NULL,
		`nation` VARCHAR(3) DEFAULT NULL,
		`religion` CHAR(1) DEFAULT NULL,
		`educate` CHAR(1) DEFAULT NULL,
		`fstatus` CHAR(1) DEFAULT NULL,
		`father` VARCHAR(13) DEFAULT NULL,
		`mother` VARCHAR(13) DEFAULT NULL,
		`couple` VARCHAR(13) DEFAULT NULL,
		`movein` VARCHAR(8) DEFAULT NULL,
		`dischar` CHAR(1) DEFAULT NULL,
		`ddisch` VARCHAR(8) DEFAULT NULL,
		`bgroup` CHAR(1) DEFAULT NULL,
		`labor` CHAR(2) DEFAULT NULL,
		`vhid` VARCHAR(8) DEFAULT NULL,
		`typearea` CHAR(1) DEFAULT NULL,
		`phone` VARCHAR(50) DEFAULT NULL,
		`email` VARCHAR(50) DEFAULT NULL,
		`graduated` VARCHAR(100) DEFAULT NULL,
		`orgId` INT(10) DEFAULT NULL,
		`position` VARCHAR(100) DEFAULT NULL,
		`website` VARCHAR(100) DEFAULT NULL,
		`aptitude` VARCHAR(255) DEFAULT NULL,
		`interest` VARCHAR(255) DEFAULT NULL,
		`remark` TEXT,
		`gis` BIGINT(20) DEFAULT NULL,
		`location` point DEFAULT NULL,
		`d_update` VARCHAR(8) DEFAULT NULL,
		`created` BIGINT(20) UNSIGNED DEFAULT NULL,
		`modify` BIGINT(10) UNSIGNED DEFAULT NULL,
		`umodify` INT(10) UNSIGNED DEFAULT NULL,
		`importseries` INT(10) UNSIGNED DEFAULT NULL,
		`importtype` ENUM("update") DEFAULT NULL,
		PRIMARY KEY (`psnid`),
		UNIQUE KEY `userId` (`userId`),
		KEY `uid` (`uid`),
		KEY `cid` (`cid`),
		KEY `name` (`name`),
		KEY `lname` (`lname`),
		KEY `vhid` (`vhid`),
		KEY `typearea` (`typearea`),
		KEY `nation` (`nation`),
		KEY `educate` (`educate`),
		KEY `sex` (`sex`),
		KEY `birth` (`birth`),
		KEY `movein` (`movein`),
		KEY `dischar` (`dischar`),
		KEY `ddisch` (`ddisch`),
		KEY `pcucode` (`pcucode`,`pid`),
		KEY `areacode` (`areacode`),
		KEY `hrareacode` (`hrareacode`),
		KEY `gis` (`gis`),
		KEY `nickname` (`nickname`),
		KEY `changwat` (`changwat`),
		KEY `ampur` (`ampur`),
		KEY `tambon` (`tambon`),
		KEY `village` (`village`),
		KEY `rchangwat` (`rchangwat`),
		KEY `rampur` (`rampur`),
		KEY `rtambon` (`rtambon`),
		KEY `rvillage` (`rvillage`),
		KEY `commune` (`commune`),
		KEY `orgId` (`orgId`),
		KEY `modify` (`modify`),
		KEY `created` (`created`),
		KEY `importseries` (`importseries`)
		)';

	mydb::query($stmt);
	$queryResult[] = mydb()->_query;



	$stmt = 'CREATE TABLE IF NOT EXISTS %org_doings% (
		`doid` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
		`orgid` INT(10) UNSIGNED DEFAULT NULL,
		`tpid` INT(11) DEFAULT NULL,
		`calid` INT(10) UNSIGNED DEFAULT NULL,
		`uid` INT(10) UNSIGNED DEFAULT NULL,
		`isregister` TINYINT NULL DEFAULT NULL,
		`issue` INT(10) UNSIGNED DEFAULT NULL,
		`doings` VARCHAR(200) DEFAULT NULL,
		`place` VARCHAR(200) DEFAULT NULL,
		`atdate` BIGINT(20) DEFAULT NULL,
		`areacode` VARCHAR(6) DEFAULT NULL,
		`fromtime` time DEFAULT NULL,
		`registstart` BIGINT UNSIGNED NULL DEFAULT NULL,
		`registend` BIGINT UNSIGNED NULL DEFAULT NULL,
		`paiddocfrom` VARCHAR(100) DEFAULT NULL,
		`paiddoctagid` VARCHAR(13) DEFAULT NULL,
		`paiddocbyname` VARCHAR(50) DEFAULT NULL,
		`paiddocdate` DATE DEFAULT NULL,
		`registerrem` TEXT DEFAULT NULL,
		`paidgroup` TEXT DEFAULT NULL,
		`options` TEXT DEFAULT NULL,
		PRIMARY KEY (`doid`),
		KEY `atdate` (`atdate`),
		KEY `doings` (`doings`),
		KEY `issue` (`issue`),
		KEY `orgid` (`orgid`),
		KEY `tpid` (`tpid`),
		KEY `calid` (`calid`),
		KEY `registdate` (`registstart`),
		KEY `registend` (`registend`)
		)' ;

	mydb::query($stmt);
	$queryResult[] = mydb()->_query;



	$stmt = 'CREATE TABLE IF NOT EXISTS %org_dos% (
		`psnid` INT(10) UNSIGNED NOT NULL,
		`doid` INT(10) UNSIGNED NOT NULL,
		`uid` INT(10) UNSIGNED NOT NULL,
		`isjoin` TINYINT(1) NOT NULL DEFAULT 0,
		`printweight` INT(10) NOT NULL DEFAULT 0,
		`regtype` ENUM("Invite","Register","Walk In") DEFAULT NULL,
		`jointype` SET("Attendee","Speaker","Workshop","Guest") NOT NULL DEFAULT "Attendee",
		`joingroup` VARCHAR(50) NULL DEFAULT NULL,
		`foodtype` ENUM("ทั่วไป","มุสลิม","มังสวิรัติ") NULL DEFAULT NULL,
		`tripby` VARCHAR(20) NULL DEFAULT NULL,
		`rest` VARCHAR(20) NULL DEFAULT NULL,
		`withdrawrest` TINYINT NULL DEFAULT NULL,
		`refcode` VARCHAR(20) DEFAULT NULL,
		`information` TEXT NULL DEFAULT NULL,
		`created` BIGINT(20) DEFAULT NULL,
		PRIMARY KEY (`psnid`,`doid`),
		KEY `mid` (`psnid`),
		KEY `doid` (`doid`),
		KEY `uid` (`uid`),
		KEY `isjoin` (`isjoin`),
		KEY `regtype` (`regtype`),
		KEY `jointype` (`jointype`),
		KEY `refcode` (`refcode`),
		KEY `foodtype` (`foodtype`),
		KEY `joingroup` (`joingroup`),
		KEY `created` (`created`)
	)' ;

	mydb::query($stmt);
	$queryResult[] = mydb()->_query;

	$stmt ='CREATE TABLE IF NOT EXISTS %org_mjoin% (
		`orgid` INT(10) UNSIGNED NOT NULL,
		`psnid` INT(10) UNSIGNED NOT NULL,
		`uid` INT(10) UNSIGNED DEFAULT NULL,
		`joindate` DATE DEFAULT NULL,
		`created` DATETIME DEFAULT NULL,
		PRIMARY KEY (`orgid`,`psnid`),
		KEY `uid` (`uid`),
		KEY `joindate` (`joindate`),
		KEY `created` (`created`)
		)' ;

	mydb::query($stmt);
	$queryResult[] = mydb()->_query;



	$stmt = 'CREATE TABLE IF NOT EXISTS %org_morg% (
		`psnid` INT(10) UNSIGNED NOT NULL,
		`orgid` INT(10) UNSIGNED NOT NULL DEFAULT "0",
		`uid` INT(10) UNSIGNED DEFAULT NULL,
		`department` VARCHAR(50) DEFAULT NULL,
		`position` VARCHAR(50) DEFAULT NULL,
		PRIMARY KEY (`psnid`,`orgid`),
		KEY `orgid` (`orgid`)
		)' ;

	mydb::query($stmt);
	$queryResult[] = mydb()->_query;



	$stmt = 'CREATE TABLE IF NOT EXISTS %org_officer% (
		`orgid` INT(10) UNSIGNED NOT NULL,
		`uid` INT(10) UNSIGNED NOT NULL,
		`membership` VARCHAR(30) NOT NULL,
		PRIMARY KEY (`orgid`,`uid`)
		)' ;

	mydb::query($stmt);
	$queryResult[] = mydb()->_query;



	$stmt = 'CREATE TABLE IF NOT EXISTS %org_ojoin% (
		`orgid` INT(10) UNSIGNED NOT NULL COMMENT "id ขององค์กรเจ้าของกิจกรรม",
		`jorgid` INT(10) UNSIGNED NOT NULL COMMENT "id ขององค์กรที่เข้าร่วมกิจกรรม",
		`uid` INT(10) UNSIGNED DEFAULT NULL,
		`sorder` INT(10) UNSIGNED DEFAULT NULL,
		`type` INT(10) UNSIGNED DEFAULT NULL,
		`issue` INT(10) UNSIGNED DEFAULT NULL,
		`flags` SET("qt") DEFAULT NULL,
		`joindate` DATE DEFAULT NULL,
		`created` DATETIME DEFAULT NULL,
		PRIMARY KEY (`jorgid`),
		KEY `sorder` (`sorder`),
		KEY `org_type` (`type`),
		KEY `org_issue` (`issue`),
		KEY `org_register_date` (`joindate`),
		KEY `org_created_date` (`created`),
		KEY `orgid` (`orgid`)
		)' ;

	mydb::query($stmt);
	$queryResult[] = mydb()->_query;



	$stmt = 'CREATE TABLE IF NOT EXISTS %org_doc% (
		`docid` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
		`orgid` INT(10) UNSIGNED DEFAULT NULL,
		`uid` INT(10) UNSIGNED DEFAULT NULL,
		`docno` VARCHAR(30) DEFAULT NULL,
		`docdate` DATE DEFAULT NULL,
		`doctype` ENUM("IN","OUT") DEFAULT NULL,
		`attnorg` VARCHAR(200) DEFAULT NULL,
		`attnname` VARCHAR(100) DEFAULT NULL,
		`title` VARCHAR(255) DEFAULT NULL,
		`action` ENUM("GO","NOT GO") DEFAULT NULL,
		`whoaction` VARCHAR(200) DEFAULT NULL,
		`detail` TEXT DEFAULT NULL,
		`created` BIGINT(20) DEFAULT NULL,
		PRIMARY KEY (`docid`),
		KEY `orgid` (`orgid`),
		KEY `uid` (`uid`),
		KEY `docno` (`docno`),
		KEY `docdate` (`docdate`),
		KEY `doctype` (`doctype`),
		KEY `attnorg` (`attnorg`),
		KEY `title` (`title`),
		KEY `created` (`created`)
		);
		';

	mydb::query($stmt);
	$queryResult[] = mydb()->_query;



	$stmt = 'CREATE TABLE IF NOT EXISTS %org_subject% (
		`orgid` int(11) NOT NULL,
		`subject` int(11) NOT NULL,
		PRIMARY KEY (`orgid`,`subject`)
		);';
	mydb::query($stmt);
	$queryResult[] = mydb()->_query;

	$stmt = 'CREATE TABLE IF NOT EXISTS %org_dopaid% (
		`dopid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
		`doid` int(10) UNSIGNED DEFAULT NULL,
		`psnid` int(11) DEFAULT NULL,
		`uid` int(10) UNSIGNED DEFAULT NULL,
		`islock` tinyint(4) DEFAULT 0,
		`formid` varchar(20) DEFAULT NULL,
		`paiddate` date DEFAULT NULL,
		`total` decimal(9,2) NOT NULL DEFAULT 0.00,
		`agrno` varchar(10) DEFAULT NULL,
		`paidname` varchar(50) DEFAULT NULL,
		`address` varchar(255) DEFAULT NULL,
		`created` bigint(20) DEFAULT NULL,
		PRIMARY KEY (`dopid`),
		KEY `doid` (`doid`),
		KEY `psnid` (`psnid`),
		KEY `paiddate` (`paiddate`),
		KEY `uid` (`uid`),
		KEY `created` (`created`),
		KEY `islock` (`islock`)
		)';
	mydb::query($stmt);
	$queryResult[] = mydb()->_query;



	$stmt = 'CREATE TABLE IF NOT EXISTS %org_dopaidtr% (
		`doptrid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
		`dopid` int(11) DEFAULT NULL,
		`catid` int(11) DEFAULT NULL,
		`detail` text COLLATE utf8_unicode_ci,
		`amt` decimal(9,2) NOT NULL DEFAULT 0.00,
		PRIMARY KEY (`doptrid`),
		KEY `dopid` (`dopid`),
		KEY `catid` (`catid`)
		);';
	mydb::query($stmt);
	$queryResult[] = mydb()->_query;



	$stmt = 'CREATE TABLE IF NOT EXISTS %org_board% (
		`brdid` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`orgid` int(11) NOT NULL,
		`boardposition` int(11) DEFAULT NULL,
		`position` int(11) NOT NULL,
		`posno` tinyint(4) DEFAULT NULL,
		`prename` varchar(20) DEFAULT NULL,
		`name` varchar(100) DEFAULT NULL,
		`series` varchar(10) DEFAULT NULL,
		`fromorg` varchar(100) DEFAULT NULL,
		`status` int(11) DEFAULT NULL DEFAULT 1 COMMENT "Join to tag:boardstatus",
		`datein` date DEFAULT NULL,
		`datedue` date DEFAULT NULL,
		`dateout` date DEFAULT NULL,
		`refid` int(10) UNSIGNED DEFAULT NULL,
		`appointed` tinyint(4) DEFAULT NULL,
		PRIMARY KEY (`brdid`),
		KEY `orgid` (`orgid`),
		KEY `status` (`status`),
		KEY `series` (`series`),
		KEY `datedue` (`datedue`),
		KEY `refid` (`refid`)
	)';
	mydb::query($stmt);
	$queryResult[] = mydb()->_query;


	$ret .= implode('<br /><br />'._NL, $queryResult);

	return $ret;
}
?>