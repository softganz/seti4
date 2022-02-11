<?php
function module_saveup_install() {

	$stmt = 'CREATE TABLE IF NOT EXISTS %saveup_treat% (
			`tid` int(10) unsigned NOT NULL auto_increment,
			`uid` int(10) unsigned NOT NULL,
			`mid` int(10) unsigned NOT NULL,
			`ref` varchar(20) NOT NULL,
			`date` date NOT NULL,
			`amount` double(10,2) NOT NULL,
			`payfor` varchar(50) NOT NULL,
			`disease` varchar(50) NOT NULL,
			`clinic` text NOT NULL,
			`amphure` varchar(50) NOT NULL,
			`province` varchar(50) NOT NULL,
			`bills` int(11) NOT NULL,
			`billdate` date default NULL,
			`remark` text NOT NULL,
			`created` datetime NOT NULL,
			PRIMARY KEY  (`tid`),
			KEY `mid` (`mid`)
		) ENGINE=MyISAM ;';
	mydb::query($stmt);

	$queryResult[] = mydb()->_query;

	$ret .= implode('<br /><br />'._NL, $queryResult);

	return $ret;
}
?>