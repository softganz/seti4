<?php
function module_ad_install() {

	$stmt='
			CREATE TABLE IF NOT EXISTS %ad% (
			  `aid` int(10) unsigned NOT NULL AUTO_INCREMENT,
			  `uid` int(10) unsigned NOT NULL,
			  `oid` int(10) unsigned DEFAULT NULL,
			  `default` enum("yes","no") DEFAULT NULL,
			  `active` enum("yes","no") DEFAULT NULL,
			  `location` varchar(20) DEFAULT NULL,
			  `file` varchar(200) DEFAULT NULL,
			  `title` varchar(255) DEFAULT NULL,
			  `body` text,
			  `url` varchar(255) DEFAULT NULL,
			  `width` smallint(5) unsigned NOT NULL DEFAULT 0,
			  `height` smallint(5) unsigned NOT NULL DEFAULT 0,
			  `weight` tinyint(3) unsigned NOT NULL DEFAULT 0,
			  `start` datetime DEFAULT NULL,
			  `stop` datetime DEFAULT NULL,
			  `clicks` int(10) unsigned NOT NULL DEFAULT 0,
			  `views` int(10) unsigned NOT NULL DEFAULT 0,
			  `created` datetime DEFAULT NULL,
			  PRIMARY KEY (`aid`),
			  KEY `ad_location` (`location`)
			) ENGINE=MyISAM ;';
	mydb::query($stmt);

	$ret .= mydb()->_query;

	$stmt='CREATE TABLE IF NOT EXISTS %ad_location% (
				  `lid` varchar(20) NOT NULL,
				  `description` varchar(255) DEFAULT NULL,
				  `width` smallint(6) DEFAULT NULL,
				  `height` smallint(6) DEFAULT NULL,
				  PRIMARY KEY (`lid`)
				) ENGINE=MyISAM;';
	mydb::query($stmt);

	$ret .= mydb()->_query;


	return $ret;
}
?>