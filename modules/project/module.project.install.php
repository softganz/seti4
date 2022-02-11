<?php
function module_project_install() {
	$ret='<h3>Project installation</h3>';

	// create content type
	$content->type='project';
	$content->name='Project';
	$content->module='project';
	$content->has_title=1;
	$content->title_label='Project title';
	$content->has_body=1;
	$content->body_label='Project summary';
	$content->custom=1;
	$content->modify=1;
	$content->locked=1;
	$content->publish='publish';
	$content->comment=2;
	model::create_content_type($content);

	// Modify cid to NULL value for save document file with no comment
	mydb::query('ALTER TABLE %topic_files% CHANGE `cid` `cid` INT( 10 ) UNSIGNED NULL DEFAULT 0');
	$queryResult[]=mydb()->_query;

	$stmt = 'CREATE TABLE IF NOT EXISTS %project% (
		`tpid` int(10) unsigned NOT NULL,
		`projectset` int(10) unsigned DEFAULT NULL,
		`pryear` year(4) DEFAULT NULL,
		`agrno` varchar(10) DEFAULT NULL,
		`prid` varchar(10) DEFAULT NULL,
		`prtype` ENUM( "โครงการ", "แผนงาน", "ชุดโครงการ" ) NOT NULL DEFAULT "โครงการ",
		`template` VARCHAR(50) DEFAULT NULL,
		`supporttype` INT(11) DEFAULT NULL,
		`strategic` INT(11) DEFAULT NULL,
		`ischild` TINYINT(4) NOT NULL DEFAULT 0,
		`supportorg` INT(11) DEFAULT NULL,
		`project_status` enum("กำลังดำเนินโครงการ","ดำเนินการเสร็จสิ้น","ยุติโครงการ","ระงับโครงการ") NOT NULL DEFAULT "กำลังดำเนินโครงการ",
		`state` tinyint(4) NOT NULL DEFAULT 0,
		`risk` tinyint(4) NOT NULL DEFAULT 0,
		`budget` decimal(10,2) DEFAULT NULL,
		`totaltarget` INT UNSIGNED  DEFAULT NULL,
		`jointarget` INT(11) NOT NULL DEFAULT 0,
		`village` char(2) DEFAULT NULL,
		`tambon` char(2) DEFAULT NULL,
		`ampur` char(2) DEFAULT NULL,
		`changwat` char(2) DEFAULT NULL,
		`area` varchar(255) DEFAULT NULL,
		`date_from` date DEFAULT NULL,
		`date_end` date DEFAULT NULL,
		`rdate_from` date DEFAULT NULL,
		`rdate_end` date DEFAULT NULL,
		`date_approve` date DEFAULT NULL,
		`date_toreport` date DEFAULT NULL,
		`ownertype` VARCHAR(20) DEFAULT NULL,
		`prowner` varchar(255) DEFAULT NULL,
		`prphone` varchar(40) DEFAULT NULL,
		`prteam` varchar(255) DEFAULT NULL,
		`prtrainer` VARCHAR(200) DEFAULT NULL,
		`bankaccount` VARCHAR(50) NULL DEFAULT NULL,
		`bankno` VARCHAR(13) NULL DEFAULT NULL,
		`bankname` VARCHAR(50) NULL DEFAULT NULL,
		`target` text,
		`objective` text,
		`activity` text,
		`orgnamedo` VARCHAR(200) DEFAULT NULL,
		`performance` TINYINT(4) DEFAULT NULL,
		`summary` TEXT DEFAULT NULL,
		`location` point DEFAULT NULL,
		PRIMARY KEY (`tpid`),
		KEY `projectset` (`projectset`),
		KEY `pryear` (`pryear`),
		KEY `agrno` (`agrno`),
		KEY `prid` (`prid`),
		KEY `prtype` (`prtype`),
		KEY `project_status` (`project_status`),
		KEY `state` (`state`),
		KEY `changwat` (`changwat`),
		KEY `ampur` (`ampur`),
		KEY `tambon` (`tambon`),
		KEY `village` (`village`),
		KEY `date_approve` (`date_approve`),
		KEY `date_toreport` (`date_toreport`),
		KEY `supporttype` (`supporttype`),
		KEY `strategic` (`strategic`),
		KEY `ischild` (`ischild`),
		KEY `ownertype` (`ownertype`)
		);';
	mydb::query($stmt);
	$queryResult[]=mydb()->_query;

	$stmt='CREATE TABLE IF NOT EXISTS %project_tr% (
		`trid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`tpid` int(10) unsigned DEFAULT NULL,
		`orgid` int(10) unsigned DEFAULT NULL,
		`parent` INT UNSIGNED NULL DEFAULT NULL,
		`calid` int(10) unsigned DEFAULT NULL,
		`refid` int(10) unsigned DEFAULT NULL,
		`refcode` varchar(50) DEFAULT NULL,
		`tagname` varchar(50) DEFAULT NULL,
		`gallery` int(10) unsigned DEFAULT NULL,
		`sorder` bigint(20) unsigned NOT NULL DEFAULT 0,
		`formid` varchar(20) DEFAULT NULL,
		`part` varchar(20) DEFAULT NULL,
		`period` tinyint(3) unsigned DEFAULT NULL,
		`flag` tinyint(4) DEFAULT NULL,
		`uid` int(10) unsigned DEFAULT NULL,
		`rate1` tinyint(4) DEFAULT NULL,
		`rate2` tinyint(4) DEFAULT NULL,
		`date1` date DEFAULT NULL,
		`date2` date DEFAULT NULL,
		`num1` decimal(10,2) DEFAULT NULL,
		`num2` decimal(10,2) DEFAULT NULL,
		`num3` decimal(10,2) DEFAULT NULL,
		`num4` decimal(10,2) DEFAULT NULL,
		`num5` decimal(10,2) DEFAULT NULL,
		`num6` decimal(10,2) DEFAULT NULL,
		`num7` decimal(10,2) DEFAULT NULL,
		`num8` decimal(10,2) DEFAULT NULL,
		`num9` decimal(10,2) DEFAULT NULL,
		`num10` decimal(10,2) DEFAULT NULL,
		`num11` decimal(10,2) DEFAULT NULL,
		`detail1` varchar(255) DEFAULT NULL,
		`detail2` varchar(255) DEFAULT NULL,
		`detail3` varchar(255) DEFAULT NULL,
		`detail4` varchar(255) DEFAULT NULL,
		`text1` text,
		`text2` text,
		`text3` text,
		`text4` text,
		`text5` text,
		`text6` text,
		`text7` text,
		`text8` text,
		`text9` text,
		`text10` text,
		`appsrc` ENUM("Web","Web App","Android","iOS") NULL DEFAULT NULL,
		`appagent` VARCHAR(40) NULL DEFAULT NULL,
		`created` bigint(20) unsigned DEFAULT NULL,
		`modified` bigint(20) unsigned DEFAULT NULL,
		`modifyby` int(10) unsigned DEFAULT NULL,
		PRIMARY KEY (`trid`),
		KEY `tpid` (`tpid`),
		KEY `orgid` (`orgid`),
		KEY `parent` (`parent`),
		KEY `calid` (`calid`),
		KEY `uid` (`uid`),
		KEY `refid` (`refid`),
		KEY `refcode` (`refcode`),
		KEY `formid` (`formid`),
		KEY `part` (`part`),
		KEY `tagname` (`tagname`),
		KEY `date1` (`date1`),
		KEY `date2` (`date2`),
		KEY `gallery` (`gallery`),
		KEY `flag` (`flag`),
		KEY `appsrc` (`appsrc`),
		KEY `created` (`created`),
		KEY `modified` (`modified`)
	);';
	mydb::query($stmt);
	$queryResult[]=mydb()->_query;

	$stmt='CREATE TABLE IF NOT EXISTS %project_tr_bak% LIKE %project_tr%';

	mydb::query($stmt);
	$queryResult[]=mydb()->_query;

	$stmt = 'CREATE TABLE IF NOT EXISTS %project_activity% (
		`calid` int(10) unsigned NOT NULL,
		`calowner` tinyint(4) DEFAULT NULL,
		`mainact` int(10) unsigned DEFAULT NULL,
		`targetpreset` int(11) NOT NULL,
		`target` text NOT NULL,
		`budget` decimal(9,2) NOT NULL,
		PRIMARY KEY (`calid`),
		KEY `calowner` (`calowner`,`mainact`)
	)';
	mydb::query($stmt);
	$queryResult[]=mydb()->_query;

	$stmt = 'CREATE TABLE IF NOT EXISTS %project_dev% (
		`tpid` int(10) unsigned NOT NULL,
		`template` varchar(30) DEFAULT NULL,
		`toorg` int(10) unsigned DEFAULT NULL,
		`pryear` year(4) NOT NULL,
		`refNo` varchar(10) DEFAULT NULL,
		`period` tinyint(3) unsigned NOT NULL DEFAULT 1,
		`status` tinyint(4) NOT NULL DEFAULT 1,
		`category` tinyint(3) unsigned DEFAULT NULL,
		`prid` varchar(10) DEFAULT NULL,
		`prevdevelop` int(10) unsigned DEFAULT NULL,
		`village` char(2) DEFAULT NULL,
		`tambon` char(2) DEFAULT NULL,
		`ampur` char(2) DEFAULT NULL,
		`changwat` char(2) DEFAULT NULL,
		`area` varchar(255) DEFAULT NULL,
		`date_from` DATE NULL DEFAULT NULL,
		`date_end` DATE NULL DEFAULT NULL,
		`date_approve` DATE NULL DEFAULT NULL,
		`budget` decimal(10,2) NOT NULL DEFAULT 0,
		`sector` int(10) unsigned DEFAULT NULL,
		`location` point DEFAULT NULL,
		PRIMARY KEY (`tpid`),
		KEY `pryear` (`pryear`),
		KEY `status` (`status`),
		KEY `changwat` (`changwat`),
		KEY `ampur` (`ampur`),
		KEY `tambon` (`tambon`),
		KEY `category` (`category`),
		KEY `prevdevelop` (`prevdevelop`),
		KEY `prid` (`prid`),
		KEY `toorg` (`toorg`)
	)';

	mydb::query($stmt);
	$queryResult[]=mydb()->_query;

	$stmt = 'CREATE TABLE IF NOT EXISTS %project_orgco% (
		`tpid` int(10) UNSIGNED NOT NULL,
		`orgId` int(10) UNSIGNED NOT NULL,
		`uid` int(10) UNSIGNED DEFAULT NULL,
		`created` bigint(20) UNSIGNED DEFAULT NULL,
		PRIMARY KEY (`tpid`,`orgId`)
	);';

	mydb::query($stmt);
	$queryResult[]=mydb()->_query;

	$stmt = 'CREATE TABLE IF NOT EXISTS %project_prov% (
	  `autoid` INT(10) unsigned NOT NULL AUTO_INCREMENT,
	  `tpid` INT(11) NOT NULL,
		`tagname` VARCHAR(50) DEFAULT NULL,
	  `house` VARCHAR(255) DEFAULT NULL,
	  `village` CHAR(2) DEFAULT NULL,
	  `tambon` CHAR(2) DEFAULT NULL,
	  `ampur` CHAR(2) DEFAULT NULL,
	  `changwat` CHAR(2) DEFAULT NULL,
	  `areatype` VARCHAR(50) DEFAULT NULL,
	  `location` POINT DEFAULT NULL,
		PRIMARY KEY (`autoid`),
		KEY `tpid` (`tpid`),
		KEY `tagname` (`tagname`),
		KEY `village` (`village`),
		KEY `tambon` (`tambon`),
		KEY `ampur` (`ampur`),
		KEY `changwat` (`changwat`)
	);';
	mydb::query($stmt);
	$queryResult[]=mydb()->_query;


	$stmt = 'CREATE TABLE IF NOT EXISTS %project_target% (
		`tpid` int(11) NOT NULL,
		`trid` int(11) UNSIGNED NOT NULL,
		`tagname` varchar(30) NOT NULL,
		`tgtid` varchar(50) NOT NULL DEFAULT "",
		`targetname` varchar(50) NOT NULL DEFAULT "",
		`amount` int(11) NOT NULL,
		`joinamt` int(11) DEFAULT NULL,
		`currentind1` decimal(5,2) DEFAULT NULL,
		`currentind2` decimal(5,2) DEFAULT NULL,
		`currentind3` decimal(5,2) DEFAULT NULL,
		`expectind1` decimal(5,2) DEFAULT NULL,
		`expectind2` decimal(5,2) DEFAULT NULL,
		`expectind3` decimal(5,2) DEFAULT NULL,
		PRIMARY KEY (`tpid`,`trid`,`tagname`,`tgtid`,`targetname`),
		KEY `targetname` (`targetname`)
	);';
	mydb::query($stmt);
	$queryResult[]=mydb()->_query;

	$ret.='<p><strong>Installation completed.</strong></p>';
	$ret.='<ul><li>'.implode('</li><li>',$queryResult).'</li></ul>';

	return $ret;
}
?>