<?php
function org_admin_install() {
	$ret='<h3>Organization installation</h3>';

	$stmts[]='CREATE TABLE %db_org% (
		`orgid` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`uid` int(10) unsigned DEFAULT NULL,
		`parent` int(10) unsigned DEFAULT NULL,
		`sector` int(10) unsigned DEFAULT NULL,
		`name` varchar(255) DEFAULT "",
		`shortname` varchar(20) DEFAULT "",
		`enshortname` varchar(20) DEFAULT "",
		`areacode` varchar(8) DEFAULT NULL,
		`house` varchar(255) NOT NULL,
		`zipcode` varchar(5) NOT NULL,
		`phone` varchar(50) DEFAULT "",
		`fax` varchar(30) DEFAULT "",
		`email` varchar(50) DEFAULT "",
		`website` varchar(100) DEFAULT "",
		`facebook` varchar(100) DEFAULT "",
		`address` varchar(255) DEFAULT "",
		`managername` varchar(50) DEFAULT "",
		`managerphone` varchar(50) DEFAULT "",
		`manageremail` varchar(50) DEFAULT "",
		`contactname` varchar(255) DEFAULT "",
		`contactphone` varchar(50) DEFAULT "",
		`contactemail` varchar(50) DEFAULT "",
		`contactdbname` varchar(50) DEFAULT "",
		`contactdbphone` varchar(50) DEFAULT "",
		`contactdbemail` varchar(50) DEFAULT "",
		`mission` text ,
		`location` varchar(30) DEFAULT "",
		`created` bigint(20) DEFAULT NULL,
		PRIMARY KEY (`orgid`),
		KEY `parent` (`parent`),
		KEY `uid` (`uid`),
		KEY `name` (`name`),
		KEY `shortname` (`shortname`),
		KEY `sector` (`sector`),
		KEY `tambon` (`tambon`),
		KEY `ampur` (`ampur`),
		KEY `changwat` (`changwat`),
		KEY `zipcode` (`zipcode`),
		KEY `created` (`created`)
		);';

	$stmts[]='CREATE TABLE %db_person% (
		`psnid` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`uid` int(10) unsigned DEFAULT NULL,
		`pcucode` char(5) DEFAULT NULL,
		`cid` varchar(20) DEFAULT NULL,
		`pid` varchar(13) DEFAULT NULL,
		`hid` varchar(14) DEFAULT NULL,
		`prename` varchar(20) DEFAULT NULL,
		`name` varchar(50) DEFAULT NULL,
		`lname` varchar(50) DEFAULT NULL,
		`nickname` varchar(20) DEFAULT NULL,
		`hn` varchar(6) DEFAULT NULL,
		`sex` enum("ชาย","หญิง") DEFAULT NULL,
		`birth` date DEFAULT NULL,
		`areacode` VARCHAR(8) DEFAULT NULL COMMENT "Home Area Code",
		`hrareacode` VARCHAR(8) DEFAULT NULL COMMENT "House Registration Area Code",
		`house` varchar(255) DEFAULT NULL,
		`village` char(2) DEFAULT NULL,
		`commune` varchar(100) DEFAULT NULL,
		`tambon` char(2) DEFAULT NULL,
		`t_tambon` varchar(30) DEFAULT NULL,
		`ampur` char(2) DEFAULT NULL,
		`t_ampur` varchar(30) DEFAULT NULL,
		`changwat` char(2) DEFAULT NULL,
		`t_changwat` varchar(50) DEFAULT NULL,
		`zip` varchar(5) DEFAULT NULL,
		`rhouse` varchar(255) DEFAULT NULL,
		`rvillage` varchar(2) DEFAULT NULL,
		`rtambon` varchar(2) DEFAULT NULL,
		`rampur` varchar(2) DEFAULT NULL,
		`rchangwat` varchar(2) DEFAULT NULL,
		`rzip` varchar(5) DEFAULT NULL,
		`mstatus` int(11) DEFAULT NULL,
		`occupa` varchar(3) DEFAULT NULL,
		`race` varchar(3) DEFAULT NULL,
		`nation` varchar(3) DEFAULT NULL,
		`religion` char(1) DEFAULT NULL,
		`educate` char(1) DEFAULT NULL,
		`fstatus` char(1) DEFAULT NULL,
		`father` varchar(13) DEFAULT NULL,
		`mother` varchar(13) DEFAULT NULL,
		`couple` varchar(13) DEFAULT NULL,
		`movein` varchar(8) DEFAULT NULL,
		`dischar` char(1) DEFAULT NULL,
		`ddisch` varchar(8) DEFAULT NULL,
		`bgroup` char(1) DEFAULT NULL,
		`labor` char(2) DEFAULT NULL,
		`vhid` varchar(8) DEFAULT NULL,
		`typearea` char(1) DEFAULT NULL,
		`phone` varchar(50) DEFAULT NULL,
		`email` varchar(50) DEFAULT NULL,
		`website` varchar(100) DEFAULT NULL,
		`aptitude` varchar(255) DEFAULT NULL,
		`interest` varchar(255) DEFAULT NULL,
		`remark` text,
		`gis` bigint(20) DEFAULT NULL,
		`location` point DEFAULT NULL,
		`d_update` varchar(8) DEFAULT NULL,
		`created` bigint(20) unsigned DEFAULT NULL,
		`modify` bigint(10) unsigned DEFAULT NULL,
		`umodify` int(10) unsigned DEFAULT NULL,
		`importseries` int(10) unsigned DEFAULT NULL,
		`importtype` enum("update") DEFAULT NULL,
		PRIMARY KEY (`psnid`),
		KEY `pcucode_pid` (`pcucode`,`pid`),
		KEY `uid` (`uid`),
		KEY `modify` (`modify`),
		KEY `name` (`name`),
		KEY `lname` (`lname`),
		KEY `created` (`created`),
		KEY `gis` (`gis`),
		KEY `nickname` (`nickname`),
		KEY `areacode` (`areacode`),
		KEY `hrareacode` (`hrareacode`),
		KEY `changwat` (`changwat`),
		KEY `ampur` (`ampur`),
		KEY `tambon` (`tambon`),
		KEY `village` (`village`),
		KEY `rchangwat` (`rchangwat`),
		KEY `rampur` (`rampur`),
		KEY `rtambon` (`rtambon`),
		KEY `rvillage` (`rvillage`),
		KEY `importseries` (`importseries`),
		KEY `commune` (`commune`),
		KEY `cid` (`cid`),
		KEY `vhid` (`vhid`),
		KEY `typearea` (`typearea`),
		KEY `nation` (`nation`),
		KEY `educate` (`educate`),
		KEY `sex` (`sex`),
		KEY `birth` (`birth`),
		KEY `movein` (`movein`),
		KEY `dischar` (`dischar`),
		KEY `ddisch` (`ddisch`)
		);';

	$stmts[]='CREATE TABLE %org_doings% (
		`doid` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`orgid` int(10) unsigned DEFAULT NULL,
		`tpid` int(11) DEFAULT NULL,
		`calid` int(10) unsigned DEFAULT NULL,
		`uid` int(10) unsigned DEFAULT NULL,
		`issue` int(10) unsigned DEFAULT NULL,
		`doings` varchar(200) DEFAULT NULL,
		`place` varchar(200) DEFAULT NULL,
		`atdate` bigint(20) DEFAULT NULL,
		`fromtime` time NOT NULL,
		PRIMARY KEY (`doid`),
		KEY `atdate` (`atdate`),
		KEY `doings` (`doings`),
		KEY `issue` (`issue`),
		KEY `orgid` (`orgid`),
		KEY `tpid` (`tpid`),
		KEY `calid` (`calid`)
		);';

	$stmts[]='CREATE TABLE %org_dos% (
		`psnid` int(10) unsigned NOT NULL,
		`doid` int(10) unsigned NOT NULL,
		`isjoin` tinyint(1) NOT NULL DEFAULT 0,
		`regtype` enum("Invite","Register","Walk In") DEFAULT NULL,
		`jointype` enum("Attendee","Speaker","Workshop","Guest") NOT NULL DEFAULT "Attendee",
		`refcode` varchar(20) DEFAULT NULL,
		PRIMARY KEY (`psnid`,`doid`),
		KEY `mid` (`psnid`),
		KEY `doid` (`doid`),
		KEY `isjoin` (`isjoin`),
		KEY `regtype` (`regtype`),
		KEY `jointype` (`jointype`),
		KEY `refcode` (`refcode`)
		);';

	$stmts[]='CREATE TABLE %org_mjoin% (
		`orgid` int(10) unsigned NOT NULL,
		`psnid` int(10) unsigned NOT NULL,
		`uid` int(10) unsigned DEFAULT NULL,
		`joindate` date DEFAULT NULL,
		`created` datetime DEFAULT NULL,
		PRIMARY KEY (`orgid`,`psnid`),
		KEY `uid` (`uid`),
		KEY `joindate` (`joindate`),
		KEY `created` (`created`)
		);';

	$stmts[]='CREATE TABLE %org_morg% (
		`psnid` int(10) unsigned NOT NULL,
		`orgid` int(10) unsigned NOT NULL DEFAULT 0,
		`uid` int(10) unsigned DEFAULT NULL,
		`department` varchar(50) DEFAULT NULL,
		`position` varchar(50) DEFAULT NULL,
		PRIMARY KEY (`psnid`,`orgid`),
		KEY `orgid` (`orgid`)
		);';

	$stmts[]='CREATE TABLE %org_officer% (
		`orgid` int(10) unsigned NOT NULL,
		`uid` int(10) unsigned NOT NULL,
		`membership` varchar(30) NOT NULL,
		PRIMARY KEY (`orgid`,`uid`)
		);';

	$stmts[]='CREATE TABLE %org_ojoin% (
		`orgid` int(10) unsigned NOT NULL COMMENT "id ขององค์กรเจ้าของกิจกรรม",
		`jorgid` int(10) unsigned NOT NULL COMMENT "id ขององค์กรที่เข้าร่วมกิจกรรม",
		`uid` int(10) unsigned DEFAULT NULL,
		`sorder` int(10) unsigned DEFAULT NULL,
		`type` int(10) unsigned DEFAULT NULL,
		`issue` int(10) unsigned DEFAULT NULL,
		`flags` set("qt") DEFAULT NULL,
		`joindate` date DEFAULT NULL,
		`created` datetime DEFAULT NULL,
		PRIMARY KEY (`jorgid`),
		KEY `sorder` (`sorder`),
		KEY `org_type` (`type`),
		KEY `org_issue` (`issue`),
		KEY `org_register_date` (`joindate`),
		KEY `org_created_date` (`created`),
		KEY `orgid` (`orgid`)
		);';


	$ret.='<ul>';
	foreach ($stmts as $stmt) {
		mydb::query($stmt);
		$ret.='<li><pre>'.preg_replace('/[\t]+/',"\t",mydb()->_query).'</pre></li>';
	}
	$ret.='</ul>';

	$ret.='<p>Installation completed.</p>';
	return $ret;
}
?>