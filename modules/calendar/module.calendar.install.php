<?php
function module_calendar_install() {
	$ret = '<h3>Project installation</h3>';

	if (!cfg('calendar.title')) cfg_db('calendar.title','Calendar');

	$stmt = 'CREATE TABLE IF NOT EXISTS %calendar% (
		`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`tpid` int(10) unsigned DEFAULT NULL,
		`orgid` INT UNSIGNED DEFAULT NULL,
		`owner` int(10) unsigned DEFAULT NULL,
		`privacy` enum("private","public","group") NOT NULL DEFAULT "private",
		`category` varchar(10) DEFAULT NULL,
		`title` varchar(255) DEFAULT NULL,
		`location` varchar(100) DEFAULT NULL,
		`village` varchar(2) DEFAULT NULL,
		`tambon` varchar(2) DEFAULT NULL,
		`ampur` varchar(2) DEFAULT NULL,
		`changwat` varchar(2) DEFAULT NULL,
		`latlng` varchar(30) DEFAULT NULL,
		`from_date` date DEFAULT NULL,
		`from_time` time DEFAULT NULL,
		`to_date` date DEFAULT NULL,
		`to_time` time DEFAULT NULL,
		`detail` text DEFAULT NULL,
		`reminder` enum("yes","no") NOT NULL DEFAULT "no",
		`repeat` enum("yes","no") NOT NULL DEFAULT "no",
		`options` TEXT NULL DEFAULT NULL,
		`ip` varchar(15) DEFAULT NULL,
		`created_date` datetime DEFAULT NULL,
		PRIMARY KEY (`id`),
		KEY `owner` (`owner`),
		KEY `from_date` (`from_date`),
		KEY `to_date` (`to_date`),
		KEY `tpid` (`tpid`),
		KEY `changwat` (`changwat`,`ampur`,`tambon`),
		KEY `orgid` (`orgid`)
	)';

	mydb::query($stmt);

	$queryResult[] = mydb()->_query;

	$stmt = 'CREATE TABLE IF NOT EXISTS %calendar_room% (
		`resvid` int(10) unsigned NOT NULL AUTO_INCREMENT,
		`calid` int(10) unsigned DEFAULT NULL,
		`roomid` int(10) unsigned NOT NULL,
		`uid` int(10) unsigned DEFAULT NULL,
		`approve` enum("รออนุมัติ","ยกเลิก","ไม่อนุมัติ","อนุมัติ") NOT NULL DEFAULT "รออนุมัติ",
		`title` varchar(255) DEFAULT NULL,
		`body` text,
		`resv_by` varchar(50) NOT NULL,
		`org_name` varchar(200) NOT NULL,
		`phone` varchar(30) DEFAULT NULL,
		`checkin` date NOT NULL,
		`from_time` time NOT NULL,
		`to_time` time NOT NULL,
		`peoples` smallint(5) unsigned NOT NULL DEFAULT 0,
		`equipment` set("Projector","คอมพิวเตอร์","เครื่องฉายข้ามศรีษะ","เครื่องเสียง","สไลด์") DEFAULT NULL,
		`created` bigint(20) unsigned DEFAULT NULL,
		PRIMARY KEY (`resvid`),
		KEY `checkin` (`checkin`),
		KEY `uid` (`uid`),
		KEY `calid` (`calid`),
		KEY `roomid` (`roomid`),
		KEY `created` (`created`),
		KEY `approve` (`approve`)
		)';

	mydb::query($stmt);

	$queryResult[] = mydb()->_query;



	$ret .= '<p><strong>Installation completed.</strong></p>';
	$ret .= '<ul><li>'.implode('</li><li>',$queryResult).'</li></ul>';

	return $ret;
}
?>