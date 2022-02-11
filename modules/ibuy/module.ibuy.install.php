<?php
function module_ibuy_install() {
	$ret='<h3>iBuy installation</h3>';

	// create table
	$stmt = 'CREATE TABLE IF NOT EXISTS %ibuy_cart% (
		`crtid` bigint(20) NOT NULL AUTO_INCREMENT,
		`session` varchar(40) DEFAULT NULL,
		`tpid` bigint(20) DEFAULT NULL,
		`amt` decimal(10,2) NOT NULL DEFAULT 0,
		`uid` bigint(20) unsigned DEFAULT NULL,
		`date_added` datetime NOT NULL,
		PRIMARY KEY (`crtid`),
		KEY `uid` (`uid`),
		KEY `session` (`session`),
		KEY `tpid` (`tpid`),
		KEY `date_added` (`date_added`)
		);';
	mydb::query($stmt);

	mydb::query('CREATE TABLE IF NOT EXISTS %ibuy_customer% (
		`uid` int(10) unsigned NOT NULL DEFAULT 0,
		`wuid` int(10) unsigned DEFAULT NULL,
		`pid` varchar(8) DEFAULT NULL,
		`custtype` char(10) DEFAULT NULL,
		`custname` varchar(100) DEFAULT NULL,
		`custaddress` varchar(200) DEFAULT NULL,
		`custzip` char(5) DEFAULT NULL,
		`custphone` varchar(50) DEFAULT NULL,
		`custlicense` varchar(20) DEFAULT NULL,
		`custattn` varchar(50) DEFAULT NULL,
		`custpaymentmethod` varchar(200) DEFAULT NULL,
		`custpaymentperiod` tinyint(4) DEFAULT NULL,
		`custpaymentstatus` tinyint(4) DEFAULT 0,
		`latlng` varchar(30) DEFAULT NULL,
		`discount` double NOT NULL DEFAULT 0,
		`discount_hold` double NOT NULL DEFAULT -1,
		`shippingby` varchar(200) DEFAULT NULL,
		`score` INT NOT NULL DEFAULT 0,
		PRIMARY KEY (`uid`),
		KEY `custname` (`custname`),
		KEY `custtype` (`custtype`),
		KEY `wuid` (`wuid`),
		KEY `pid` (`pid`)
		);'
	);

	mydb::query('CREATE TABLE IF NOT EXISTS %ibuy_log% (
		`lid` int(11) NOT NULL AUTO_INCREMENT,
		`uid` int(10) unsigned DEFAULT NULL,
		`keyword` varchar(10) DEFAULT NULL,
		`kid` int(10) unsigned DEFAULT NULL,
		`status` tinyint(4) DEFAULT NULL,
		`process` tinyint(4) DEFAULT NULL,
		`amt` DECIMAL(10,2) DEFAULT NULL,
		`detail` text,
		`created` bigint(20) DEFAULT NULL,
		PRIMARY KEY (`lid`),
		KEY `keyword` (`keyword`,`status`,`process`),
		KEY `uid` (`uid`),
		KEY `kid` (`kid`),
		KEY `status` (`status`),
		KEY `process` (`process`),
		KEY `created` (`created`)
		);'
	);

	mydb::query('CREATE TABLE IF NOT EXISTS %ibuy_order% (
		`oid` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`uid` int(11) NOT NULL,
		`classid` VARCHAR(10) NOT NULL DEFAULT "",
		`orderno` VARCHAR(20) NULL DEFAULT NULL,
		`ordertype` tinyint(4) NOT NULL DEFAULT 0,
		`orderdate` bigint(20) DEFAULT NULL,
		`subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0,
		`discount` DECIMAL(10,2) NOT NULL DEFAULT 0,
		`shipping` DECIMAL(10,2) NOT NULL DEFAULT 0,
		`total` DECIMAL(10,2) NOT NULL DEFAULT 0,
		`leveldiscount` DECIMAL(10,2) NOT NULL DEFAULT 0,
		`marketvalue` DECIMAL(10,2) NOT NULL DEFAULT 0,
		`franchisorvalue` DECIMAL(10,2) NOT NULL DEFAULT 0,
		`balance` DECIMAL(10,2) NOT NULL DEFAULT 0,
		`status` tinyint(4) NOT NULL DEFAULT 0,
		`emscode` varchar(13) DEFAULT NULL,
		`emsdate` bigint(20) DEFAULT NULL,
		`shipcode` varchar(2) NOT NULL,
		`shipto` varchar(120) DEFAULT NULL,
		`score` int(11) NOT NULL DEFAULT 0,
		`scoreto` enum("Y","N") NOT NULL DEFAULT "N",
		`couponvalue` decimal(10,2) DEFAULT NULL,
		`couponid` int(11) DEFAULT NULL,
		`remark` text,
		PRIMARY KEY (`oid`),
		KEY `uid` (`uid`),
		KEY `status` (`status`),
		KEY `ordertype` (`ordertype`),
		KEY `orderdate` (`orderdate`),
		KEY `emscode` (`emscode`),
		KEY `emsdate` (`emsdate`),
		KEY `classid` (`classid`),
		UNIQUE `orderno` (`orderno`)
		) ;'
	);

	mydb::query('CREATE TABLE IF NOT EXISTS %ibuy_ordertr% (
		`otrid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		`oid` int(10) unsigned NOT NULL,
		`tpid` int(10) unsigned NOT NULL,
		`description` varchar(200) NOT NULL DEFAULT "",
		`amt` int(11) NOT NULL DEFAULT 0,
		`price` DECIMAL(10,2) NOT NULL DEFAULT 0,
		`subtotal` DECIMAL(10,2) NOT NULL DEFAULT 0,
		`discount` DECIMAL(10,2) NOT NULL DEFAULT 0,
		`total` DECIMAL(10,2) NOT NULL DEFAULT 0,
		`leveldiscount` DECIMAL(10,2) NOT NULL DEFAULT 0,
		`marketvalue` DECIMAL(10,2) NOT NULL DEFAULT 0,
		PRIMARY KEY (`otrid`),
		KEY `oid` (`oid`),
		KEY `tpid` (`tpid`)
		) ;'
	);

	mydb::query('CREATE TABLE IF NOT EXISTS %ibuy_product% (
		`tpid` int(11) NOT NULL,
		`prcode` varchar(10) NOT NULL,
		`forbrand` varchar(255) NOT NULL,
		`showfor` enum("PUBLIC","MEMBER") NOT NULL DEFAULT "PUBLIC",
		`available` tinyint(4) DEFAULT 1,
		`listprice` decimal(10,2) NOT NULL,
		`retailprice` decimal(10,2) NOT NULL,
		`price1` decimal(10,2) NOT NULL,
		`price2` decimal(10,2) NOT NULL,
		`price3` decimal(10,2) NOT NULL,
		`price4` decimal(10,2) NOT NULL,
		`price5` decimal(10,2) NOT NULL,
		`resalerprice` decimal(10,2) NOT NULL,
		`balance` int(11) NOT NULL,
		`isdiscount` tinyint(4) NOT NULL DEFAULT 0,
		`ismarket` tinyint(4) NOT NULL DEFAULT 0,
		`isfranchisor` tinyint(4) NOT NULL DEFAULT 0,
		`isnew` tinyint(4) NOT NULL DEFAULT 1,
		`barcode` varchar(30) NOT NULL,
		`stockid` varchar(30) NOT NULL,
		`cost` decimal(10,2) NOT NULL,
		`minamt` decimal(10,2) NOT NULL,
		`maxamt` decimal(10,2) NOT NULL,
		`vattype` enum("NO","INC","EXC","YES") NOT NULL DEFAULT "INC",
		`unitid` varchar(20) NOT NULL,
		`subunitid` varchar(20) NOT NULL,
		`outofsale` enum("N","Y","O") NOT NULL DEFAULT "N",
		`remember` varchar(160) NOT NULL,
		PRIMARY KEY (`tpid`),
		KEY `available` (`available`),
		KEY `isnew` (`isnew`),
		KEY `forbrand` (`forbrand`),
		KEY `outofsale` (`outofsale`),
		KEY `balance` (`balance`),
		KEY `barcode` (`barcode`),
		KEY `stockid` (`stockid`),
		KEY `minamt` (`minamt`),
		KEY `maxamt` (`maxamt`),
		KEY `prcode` (`prcode`)
		) ;'
	);

	// create ibuy content type
	mydb::query('INSERT INTO %topic_types%
		(`type`,`name`,`module`,`has_title`,`title_label`,`has_body`,`body_label`,`custom`,`modified`,`locked`)
		VALUES
		("ibuy","iBuy Product","ibuy",1,"ชื่อสินค้า",1,"รายละเอียด",1,1,1)'
	);

	if (cfg('topic_options_ibuy')==NULL) {
		$topic_options->publish='publish';
		$topic_options->comment=2;
		cfg_db('topic_options_ibuy',$topic_options);
	}

	// create ibuy franchise content type
	mydb::query('INSERT INTO %topic_types%
		(`type`,`name`,`module`,`has_title`,`title_label`,`has_body`,`body_label`,`custom`,`modified`,`locked`)
		VALUES
		("franchise","Franchise Shop","franchise",1,"ชื่อร้าน",1,"หน้าร้าน",1,1,1)'
	);

	if (cfg('topic_options_franchise')==NULL) {
		$topic_options->publish='publish';
		$topic_options->comment=2;
		cfg_db('topic_options_franchise',$topic_options);
	}

	return $ret;
}
?>