<?php
$version='3.01';

// Change field value from VARCHAR to TEXT
if (mydb::columns('property','value')) {
	mydb::query('ALTER TABLE %property% CHANGE `value` `value` TEXT NULL DEFAULT NULL ');
	$result[$version][]=array('Change table property value from varchar to text.', mydb()->_query, mydb()->_error, mydb()->_error_no );
}


// Rename field name province to changwat
if (mydb::columns('topic','province')) {
	mydb::query('ALTER TABLE %topic% CHANGE `province` `changwat` CHAR( 2 ) NULL DEFAULT NULL ');
	$result[$version][]=array('Change field province to changwat.', mydb()->_query, mydb()->_error, mydb()->_error_no);
}

if (!mydb::columns('topic','rating')) {
	mydb::query('ALTER TABLE %topic% ADD `rating` DECIMAL(2,1) NULL DEFAULT NULL AFTER `sticky`, ADD `ratetimes` BIGINT NOT NULL DEFAULT 0 AFTER `rating`, ADD `liketimes` INT NOT NULL DEFAULT 0 AFTER `ratetimes`, ADD INDEX (`rating`), ADD INDEX (`ratetimes`), ADD INDEX (`liketimes`)');
	$result[$version][]=array('Add field rating, ratetimes.', mydb()->_query, mydb()->_error, mydb()->_error_no);
}

if (!mydb::columns('topic','thread')) {
	mydb::query('ALTER TABLE %topic% ADD `thread` INT UNSIGNED NULL DEFAULT NULL AFTER `parent`, ADD INDEX (`thread`);');
	$result[$version][]=array('Add field thread to topic.', mydb()->_query, mydb()->_error, mydb()->_error_no);

	if (mydb::table_exists('project')) {
		mydb::query('UPDATE %topic% t LEFT JOIN %project_dev% p USING(`tpid`) SET t.`thread` = p.`prevproject`');
		$result[$version][]=array('Update field topic->thread = project->prevproject.', mydb()->_query, mydb()->_error, mydb()->_error_no);

		mydb::query('ALTER TABLE %project_dev% DROP `prevproject`;');
		$result[$version][]=array('Drop field prevproject of table project_dev.', mydb()->_query, mydb()->_error, mydb()->_error_no);
	}
}

if (!mydb::columns('topic','template')) {
	mydb::query('ALTER TABLE %topic% ADD `template` VARCHAR(50) NULL DEFAULT NULL AFTER `thread`, ADD INDEX (`template`);');
	$result[$version][]=array('Add field template to topic.', mydb()->_query, mydb()->_error, mydb()->_error_no);

	if (mydb::table_exists('project')) {
		mydb::query('UPDATE %topic% t LEFT JOIN %project% p USING(`tpid`) SET t.`template` = p.`template`');
		$result[$version][]=array('Update topic->template = project->template.', mydb()->_query, mydb()->_error, mydb()->_error_no);

		mydb::query('ALTER TABLE %project% DROP `template`;');
		$result[$version][]=array('Drop field template of table project.', mydb()->_query, mydb()->_error, mydb()->_error_no);
	}
}





// Add field cover to topic_files
if (!mydb::columns('topic_files','cover')) {
	mydb::query('ALTER TABLE %topic_files% ADD `cover` ENUM( "Yes" ) NULL DEFAULT NULL AFTER `type` ');
	$result[$version][]=array('Add field cover to topic_files.', mydb()->_query, mydb()->_error, mydb()->_error_no);
}

// Add field tagname to topic_files
if (!mydb::columns('topic_files','tagname')) {
	if (mydb::columns('topic_files','report')) {
		$stmt='ALTER TABLE %topic_files% CHANGE `report` `tagname` VARCHAR(50) NULL DEFAULT NULL';
		mydb::query($stmt);
		$result[$version][]=array('Change field report to tagname in topic_files.', mydb()->_query, mydb()->_error, mydb()->_error_no);
	} else {
		mydb::query('ALTER TABLE %topic_files% ADD `tagname` VARCHAR(50) NULL DEFAULT NULL AFTER `type` , ADD INDEX ( `tagname` ) ');
		$result[$version][]=array('Add field tagname in topic_files.', mydb()->_query, mydb()->_error, mydb()->_error_no);
	}

	$stmt='ALTER TABLE %topic_files% ADD INDEX (`tagname`)';
	mydb::query($stmt);
	$result[$version][]=array('Add index tagname in topic_files.', mydb()->_query, mydb()->_error, mydb()->_error_no);
}

// Add field orgid to topic_files
if (!mydb::columns('topic_files','orgid')) {
 		mydb::query('ALTER TABLE %topic_files% ADD `orgid` INT NULL DEFAULT NULL AFTER `uid` , ADD INDEX ( `orgid` ) ');
	$result[$version][]=array('Add field orgid to topic_files.', mydb()->_query, mydb()->_error, mydb()->_error_no);
}

// Add field refid to topic_files
if (!mydb::columns('topic_files','refid')) {
 		mydb::query('ALTER TABLE %topic_files% ADD `refid` INT NULL DEFAULT NULL AFTER `orgid` , ADD INDEX ( `refid` ) ');
	$result[$version][]=array('Add field refid to topic_files.', mydb()->_query, mydb()->_error, mydb()->_error_no);
}

// Add field rating to topic_comment
if (!mydb::columns('topic_comments','giverating')) {
 		mydb::query('ALTER TABLE %topic_comments% ADD `giverating` TINYINT NULL DEFAULT NULL AFTER `no`');
	$result[$version][]=array('Add field giverating to topic_comments.', mydb()->_query, mydb()->_error, mydb()->_error_no);
}

// Change field vid of table tag from default 0 to null
if (mydb::columns('tag','vid')) {
	mydb::query('ALTER TABLE %tag% CHANGE `vid` `vid` INT( 10 ) UNSIGNED NULL DEFAULT NULL ');
	$result[$version][]=array('Change field vid of table tag from default 0 to null.', mydb()->_query, mydb()->_error, mydb()->_error_no);
}


// Add field taggroup into tag
if (!mydb::columns('tag','taggroup')) {
	mydb::query('ALTER TABLE %tag% ADD `taggroup` VARCHAR( 30 ) NULL DEFAULT NULL AFTER `vid` , ADD INDEX ( `taggroup` ) ');
	$result[$version][]=array('Add field taggroup into tag.', mydb()->_query, mydb()->_error, mydb()->_error_no);
}


if (!mydb::columns('tag','process')) {
	mydb::query('ALTER TABLE %tag% ADD `process` TINYINT NULL DEFAULT NULL AFTER `taggroup` ');
	$result[$version][]=array('Tag process is optional for for tagname.', mydb()->_query, mydb()->_error, mydb()->_error_no);
}


if (mydb::columns('users','username')) {
	mydb::query('ALTER TABLE %users% CHANGE `username` `username` VARCHAR( 30 ) NULL DEFAULT NULL ');
	$result[$version][]=array('Change size of username to 30 charactors.', mydb()->_query, mydb()->_error, mydb()->_error_no);
}


if (mydb::columns('users','name')) {
	mydb::query('ALTER TABLE %users% CHANGE `name` `name` VARCHAR( 255 ) NULL DEFAULT "" ');
	$result[$version][]=array('Change table users field name to 255 char.', mydb()->_query, mydb()->_error, mydb()->_error_no);
}

if (mydb::columns('users','status')) {
	mydb::query('ALTER TABLE %users% CHANGE `status` `status` ENUM("enable","disable","block","waiting","locked") NULL DEFAULT NULL;');
	$result[$version][]=array('Change table users field name to 255 char.', mydb()->_query, mydb()->_error, mydb()->_error_no);
}


// Change table topic add field parent, orgid and add index
if (!mydb::columns('topic','parent')) {
	mydb::query('ALTER TABLE %topic%
								ADD `parent` INT UNSIGNED NULL DEFAULT NULL AFTER `type`,
								ADD INDEX (`parent`);');
	$result[$version][]=array('Change table topic add field parent.', mydb()->_query, mydb()->_error, mydb()->_error_no );
}



if (!mydb::columns('topic','orgid')) {
	mydb::query('ALTER TABLE %topic%
								ADD `orgid` INT UNSIGNED NULL DEFAULT NULL AFTER `access`,
								ADD INDEX (`orgid`); ');
	$result[$version][]=array('Change table topic add field orgid.', mydb()->_query, mydb()->_error, mydb()->_error_no );
}

// TODO : Check if field revid is default NOT NULL
if (mydb::columns('topic','revid')) {
	$stmt = 'ALTER TABLE %topic% CHANGE `revid` `revid` INT(10) UNSIGNED NULL DEFAULT NULL;';
	mydb::query($stmt);
	$result[$version][]=array('Change table topic change revid default to NULL.', mydb()->_query, mydb()->_error, mydb()->_error_no );
}



if (!mydb::columns('watchdog','keyid')) {
	mydb::query('ALTER TABLE %watchdog% ADD `keyid` BIGINT NULL DEFAULT NULL AFTER `keyword`, ADD INDEX (`keyid`); ');
	$result[$version][]=array('Change table watchdog add field keyid.', mydb()->_query, mydb()->_error, mydb()->_error_no );
}


if (!mydb::columns('watchdog','fldname')) {
	mydb::query('ALTER TABLE %watchdog% ADD `fldname` VARCHAR(50) NULL DEFAULT NULL AFTER `keyid`, ADD INDEX (`fldname`); ');
	$result[$version][]=array('Change table watchdog add field fldname.', mydb()->_query, mydb()->_error, mydb()->_error_no );
}


if (!mydb::columns('tag','catid')) {
	mydb::query('ALTER TABLE %tag% ADD `catid` INT UNSIGNED NULL DEFAULT NULL AFTER `taggroup` , ADD INDEX ( `catid` ) ;');
	$result[$version][]=array('Change table tag add field catid.', mydb()->_query, mydb()->_error, mydb()->_error_no );
}


if (!mydb::columns('tag','ownid')) {
	mydb::query('ALTER TABLE %tag% ADD `ownid` INT UNSIGNED NULL DEFAULT NULL AFTER `vid` , ADD INDEX ( `ownid` ) ;');
	$result[$version][]=array('Change table tag add field ownid.', mydb()->_query, mydb()->_error, mydb()->_error_no );
}


if (!mydb::columns('topic','title')) {
	mydb::query('ALTER TABLE %topic% ADD INDEX ( `title` ) ;');
	$result[$version][]=array('Change table topic add field title.', mydb()->_query, mydb()->_error, mydb()->_error_no );
}


if (!mydb::columns('tag','catparent')) {
	mydb::query('ALTER TABLE %tag% ADD `catparent` INT UNSIGNED NOT NULL AFTER `catid`, ADD INDEX (`catparent`); ');
	$result[$version][]=array('Change table tag add field catparent.', mydb()->_query, mydb()->_error, mydb()->_error_no );
}



// 2016-08-27
if (!mydb::columns('tag','listclass')) {
	mydb::query('ALTER TABLE %tag% ADD `listclass` VARCHAR(50) NOT NULL DEFAULT "" AFTER `liststyle`');
	$result[$version][]=array('Change table tag add field listclass.', mydb()->_query, mydb()->_error, mydb()->_error_no );
}




// Change table calendar add field village, tambon, ampur, changwat and add index
if (mydb::table_exists('calendar')) {
	if (!mydb::columns('calendar','village')) {
		mydb::query('ALTER TABLE %calendar%
			ADD `village` CHAR(2) NOT NULL AFTER `location`,
			ADD `tambon` CHAR(2) NOT NULL AFTER `village`,
			ADD `ampur` CHAR(2) NOT NULL AFTER `tambon`,
			ADD `changwat` CHAR(2) NOT NULL AFTER `ampur`,
			ADD `latlng` VARCHAR(30) NULL DEFAULT NULL AFTER `changwat`
			ADD INDEX(`changwat`),
			ADD INDEX(`ampur`),
			ADD INDEX(`tambon`),
			ADD INDEX(`village`)');
		$result[$version][]=array('Change table calendar add field village, tambon, ampur, changwat.', mydb()->_query, mydb()->_error, mydb()->_error_no );
	}
}

if (mydb::table_exists('db_org') && !mydb::columns('db_org','parent')) {
	mydb::query('ALTER TABLE %db_org%
		ADD `parent` INT(10) unsigned DEFAULT NULL AFTER `uid`,
		ADD INDEX(`parent`)');
	$result[$version][]=array('Change table db_org add field parent.', mydb()->_query, mydb()->_error, mydb()->_error_no );
}



if (mydb::table_exists('project') && !mydb::columns('project','prtype')) {
	mydb::query('ALTER TABLE %project%
		ADD `prtype` ENUM( "โครงการ", "แผนงาน", "ชุดโครงการ" ) NOT NULL DEFAULT "โครงการ" AFTER `prid` ,
		ADD INDEX ( `prtype` );');
	$result[$version][]=array('Change table project add field prtype.', mydb()->_query, mydb()->_error, mydb()->_error_no );
}

// Change table project_tr add field refid, refcode, tagname and add index
if (mydb::table_exists('project_tr') && !mydb::columns('project_tr','refid')) {
	mydb::query('ALTER TABLE %project_tr%
		ADD `refid` bigint(10) unsigned DEFAULT NULL AFTER `calid`,
		ADD `refcode` varchar(50) DEFAULT NULL AFTER `refid`,
		ADD `tagname` varchar(50) DEFAULT NULL AFTER `refcode`,
		ADD INDEX ( `refid` ),
		ADD INDEX ( `refcode` ),
		ADD INDEX ( `tagname` );');
	$result[$version][]=array('Change table project_tr add field refid, refcode, tagname and add index.', mydb()->_query, mydb()->_error, mydb()->_error_no );
}



if (mydb::table_exists('db_person') && !mydb::columns('db_person','nickname')) {
	mydb::query('ALTER TABLE %person%
			ADD `nickname` VARCHAR( 50 ) NOT NULL DEFAULT "" AFTER `lname` ,
			ADD INDEX ( `nickname` ) ;');
		$result[$version][]=array('Change table db_person add field nickname.', mydb()->_query, mydb()->_error, mydb()->_error_no );
}

if (mydb::table_exists('topic_user')) {
	mydb::query('ALTER TABLE %topic_user% CHANGE `membership` `membership` ENUM("Manager","Follower","Trainer","Owner","Regular member","Commentator","Viewer") NOT NULL DEFAULT "Regular member"'); 
		$result[$version][]=array('Change table topic_user change field membership.', mydb()->_query, mydb()->_error, mydb()->_error_no );
}

if (mydb::table_exists('bigdata') && !mydb::columns('bigdata','fldref')) {
	mydb::query('ALTER TABLE %bigdata% ADD `fldref` VARCHAR(30) NULL DEFAULT NULL AFTER `fldtype`, ADD INDEX (`fldref`);');
		$result[$version][]=array('Change table bigdata add field fldref.', mydb()->_query, mydb()->_error, mydb()->_error_no );
}


//ALTER TABLE `happynetwork`.`sgz_topic` DROP INDEX `revid`, ADD INDEX `revid` (`revid`) USING BTREE;
?>
