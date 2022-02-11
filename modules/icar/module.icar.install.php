<?php
/**
* Module Install
*
* @param Object $self
* @return String
*/

$debug = true;

function module_icar_install($self) {
	// create content type
	$content->type='icar';
	$content->name='iCar - Car Cost Control';
	$content->module='icar';
	$content->has_title=1;
	$content->title_label='Car information';
	$content->has_body=1;
	$content->body_label='Car detail';
	$content->custom=1;
	$content->modify=1;
	$content->locked=1;
	$content->publish='publish';
	$content->comment=_COMMENT_READWRITE;
	model::create_content_type($content);

	$stmts[]='CREATE TABLE %icar% (
		`tpid` int(10) unsigned NOT NULL,
		`shopid` int(10) unsigned DEFAULT NULL,
		`buydate` date DEFAULT NULL,
		`saledate` date DEFAULT NULL,
		`plate` varchar(30) DEFAULT NULL,
		`brand` int(10) unsigned DEFAULT NULL,
		`model` varchar(50) DEFAULT NULL,
		`year` year(4) DEFAULT NULL,
		`color` varchar(20) DEFAULT NULL,
		`enginno` varchar(20) DEFAULT NULL,
		`bodyno` varchar(20) DEFAULT NULL,
		`stklocname` VARCHAR(50) NULL DEFAULT NULL,
		`costprice` decimal(10,2) NOT NULL DEFAULT 0.00,
		`downpayment` decimal(10,2) NOT NULL DEFAULT 0.00,
		`pricetosale` decimal(10,2) NOT NULL DEFAULT 0.00,
		`saleprice` decimal(10,2) NOT NULL DEFAULT 0.00,
		`financeid` int(10) unsigned DEFAULT NULL,
		`financeprice` decimal(10,2) NOT NULL DEFAULT 0.00,
		`comfinance` decimal(10,2) NOT NULL DEFAULT 0.00,
		`rcvtransfer` decimal(10,2) NOT NULL DEFAULT 0.00,
		`paytransfer` decimal(10,2) NOT NULL DEFAULT 0.00,
		PRIMARY KEY (`tpid`),
		KEY `datebuy` (`buydate`),
		KEY `datesale` (`saledate`),
		KEY `plate` (`plate`),
		KEY `brand` (`brand`),
		KEY `model` (`model`),
		KEY `year` (`year`),
		KEY `financeid` (`financeid`),
		KEY `color` (`color`),
		KEY `shopid` (`shopid`),
		KEY `stklocname` (`stklocname`)
		)';

	$stmts[]='CREATE TABLE %icarcost% (
		`costid` int(11) NOT NULL AUTO_INCREMENT,
		`tpid` int(10) unsigned DEFAULT NULL,
		`uid` int(10) unsigned DEFAULT NULL,
		`itemdate` date NOT NULL,
		`costcode` int(10) unsigned DEFAULT NULL,
		`detail` varchar(200) DEFAULT NULL,
		`interest` decimal(4,2) NOT NULL DEFAULT 0.00,
		`amt` decimal(10,2) NOT NULL DEFAULT 0.00,
		`created` bigint(20) unsigned DEFAULT NULL,
		PRIMARY KEY (`costid`),
		KEY `uid` (`uid`),
		KEY `tpid` (`tpid`),
		KEY `itemdate` (`itemdate`),
		KEY `costcode` (`costcode`),
		KEY `created` (`created`)
		)';

	$stmts[]='CREATE TABLE %icarshop% (
		`shopid` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`shopname` varchar(200) DEFAULT NULL,
		`motto` varchar(255) DEFAULT NULL,
		PRIMARY KEY (`shopid`)
		)';

	$stmts[]='CREATE TABLE %icarusr% (
		`shopid` int(11) unsigned NOT NULL,
		`uid` int(11) unsigned NOT NULL,
		`membership` varchar(20) DEFAULT NULL,
		PRIMARY KEY (`shopid`,`uid`)
		)';
		
	$stmts[]='ALTER TABLE `sgz_tag` ADD `shopid` INT UNSIGNED NULL DEFAULT NULL AFTER `vid` , ADD INDEX ( `shopid` )';

	foreach ($stmts as $stmt) mydb::query($stmt);
}
?>