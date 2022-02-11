<?php
/**
 * Upgrade project table staucture
 *
 * @return String
 */
function project_admin_upgrade($self) {
	R::View('project.toolbar',$self,'Project Upgrade','admin');
	$self->theme->sidebar=R::View('project.admin.menu');

	if (!SG\confirm()) {
		return '<div class="-sg-text-center" style="padding: 0 32px;">'
			. '<p><b>อัพเกรดฐานข้อมูลของระบบบริหารโครงการ</b><br /><br />กรุณายืนยันการอัพเกรด?<br /><br />'
			. '<nav class="nav -page"><a class="btn -link -cancel" href="'.url('project/admin').'"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a> <a class="sg-action btn -danger" href="'.url('project/admin/upgrade',array('confirm'=>'Yes')).'" data-rel="#main"><i class="icon -material">done_all</i><span>ยืนยันการอัพเกรด</span></a></nav>'
			. '</p>'
			. '<p><b>คำเตือน</b> ควรสำรองข้อมูลให้เรียบร้อยก่อนดำเนินการอัพเกรดข้อมูล</p>'
			. '</div>';
	}

	$targetDb=cfg('project.backup.db');
	$result=array();

	if (!mydb::columns('project','state')) {
		$stmt='ALTER TABLE %project% ADD `state` tinyint(4) NOT NULL DEFAULT 0 AFTER `project_status`, ADD INDEX (`state`);';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}


	//$stmt='ALTER TABLE %project% CHANGE `objective` `objective` TEXT NULL DEFAULT NULL AFTER `prtrainer`';
	//mydb::query($stmt);
	//$result[]=mydb()->_query;

	//$stmt='ALTER TABLE %project% CHANGE `activity` `activity` TEXT NULL DEFAULT NULL AFTER `objective`';
	//mydb::query($stmt);
	//$result[]=mydb()->_query;

	if (!mydb::columns('project','orgnamedo')) {
		$stmt='ALTER TABLE %project% ADD `orgnamedo` VARCHAR(200) NULL DEFAULT NULL AFTER `activity`';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}

	if (!mydb::columns('project','template')) {
		$stmt='ALTER TABLE %project% ADD `template` VARCHAR(50) NULL DEFAULT NULL AFTER `prtype`';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}

	if (!mydb::columns('project','date_toreport')) {
		$stmt='ALTER TABLE %project%
			ADD `date_toreport` DATE NULL DEFAULT NULL AFTER `date_approve`
			, ADD INDEX (`date_toreport`);';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}

	if (!mydb::columns('project','supporttype')) {
		$stmt='ALTER TABLE %project%
			ADD `supporttype` INT NULL DEFAULT NULL AFTER `template`
			, ADD INDEX (`supporttype`);';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}

	if (!mydb::columns('project','strategic')) {
		$stmt='ALTER TABLE %project%
			ADD `strategic` INT NULL DEFAULT NULL AFTER `supporttype`
			, ADD INDEX (`strategic`);';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}

	if (!mydb::columns('project','ischild')) {
		$stmt='ALTER TABLE %project%
			ADD `ischild` TINYINT NOT NULL DEFAULT 0 AFTER `strategic`
			, ADD INDEX (`ischild`);';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}

	if (!mydb::columns('project','supportorg')) {
		$stmt = 'ALTER TABLE %project%
			ADD `supportorg` INT DEFAULT NULL AFTER `ischild`
			, ADD INDEX (`supportorg`);';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}

	if (mydb::columns('project','targetjoin')) {
		$stmt = 'ALTER TABLE %project%
			CHANGE `targetjoin` `jointarget` INT(11) DEFAULT NULL;';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	} else if (!mydb::columns('project','jointarget')) {
		$stmt='ALTER TABLE %project%
			ADD `jointarget` INT DEFAULT NULL AFTER `totaltarget`;';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}

	if (!mydb::columns('project','performance')) {
		$stmt='ALTER TABLE %project%
			ADD `performance` TINYINT NULL DEFAULT NULL AFTER `jointarget`;';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}

	if (!mydb::columns('project','summary')) {
		$stmt='ALTER TABLE %project%
			ADD `summary` TEXT DEFAULT NULL AFTER `performance`;';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}

	if (!mydb::columns('project', 'ownertype')) {
		$stmt = 'ALTER TABLE %project% ADD `ownertype` VARCHAR(20) NULL DEFAULT NULL AFTER `date_toreport`
			, ADD INDEX (`ownertype`);';
		mydb::query($stmt);
		$result[] = mydb()->_query;
	}

	if (!mydb::columns('project', 'bankaccount')) {
		$stmt = 'ALTER TABLE %project% ADD `bankaccount` VARCHAR(50) NULL DEFAULT NULL AFTER `prtrainer`
			, ADD `bankno` VARCHAR(13) NULL DEFAULT NULL AFTER `bankaccount`
			, ADD `bankname` VARCHAR(50) NULL DEFAULT NULL AFTER `bankno`;';
		mydb::query($stmt);
		$result[] = mydb()->_query;
	}




	if (!mydb::columns('project_tr','refid')) {
		$stmt='ALTER TABLE %project_tr% ADD `refid` INT UNSIGNED NULL DEFAULT NULL AFTER `calid`, ADD INDEX (`refid`);';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}

	if (!mydb::columns('project_tr','refcode')) {
		$stmt='ALTER TABLE %project_tr% ADD `refcode` varchar(50) DEFAULT NULL AFTER `refid`, ADD INDEX (`refcode`);';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}

	if (!mydb::columns('project_tr','tagname')) {
		$stmt='ALTER TABLE %project_tr% ADD `tagname` varchar(50) DEFAULT NULL AFTER `refcode`, ADD INDEX (`tagname`);';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}

	if (!mydb::columns('project_tr','sorder')) {
		$stmt='ALTER TABLE %project_tr% ADD `sorder` bigint(10) unsigned NOT NULL DEFAULT 0 AFTER `tagname`;';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}

	if (!mydb::columns('project_tr','num9')) {
		$stmt='ALTER TABLE %project_tr% ADD `num9` decimal(10,2) DEFAULT NULL AFTER `num8`;';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}

	if (!mydb::columns('project_tr','num10')) {
		$stmt='ALTER TABLE %project_tr% ADD `num10` decimal(10,2) DEFAULT NULL AFTER `num9`;';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}

	if (!mydb::columns('project_tr','num11')) {
		$stmt='ALTER TABLE %project_tr% ADD `num11` decimal(10,2) DEFAULT NULL AFTER `num10`;';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}

	if (!mydb::columns('project_tr','orgid')) {
		$stmt='ALTER TABLE %project_tr% ADD `orgid` INT UNSIGNED NULL DEFAULT NULL AFTER `tpid`, ADD INDEX (`orgid`);';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}

	if (!mydb::columns('project_tr', 'data')) {
		$stmt = 'ALTER TABLE %project_tr% ADD `data` TEXT NULL DEFAULT NULL AFTER `text10`;';
		mydb::query($stmt);
		$result[] = mydb()->_query;
	}



	if (!mydb::columns('project_prov','tagname')) {
		$stmt='ALTER TABLE %project_prov% ADD `tagname` VARCHAR(50) NULL DEFAULT NULL AFTER `tpid`, ADD INDEX (`tagname`)';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}

	if (!mydb::columns('project_prov','areatype')) {
		$stmt='ALTER TABLE %project_prov% ADD `areatype` VARCHAR(50) NULL DEFAULT NULL AFTER `changwat`';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}



	// Table project_dev
	if (!mydb::columns('project_dev','template')) {
		$stmt='ALTER TABLE %project_dev% ADD `template` VARCHAR(50) NULL DEFAULT NULL AFTER `tpid`';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}

	if (!mydb::columns('project_dev','toorg')) {
		$stmt = 'ALTER TABLE %project_dev% ADD `toorg` INT UNSIGNED NULL DEFAULT NULL AFTER `template` , ADD INDEX (`toorg`)';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}

	// Table project_dev
	if (!mydb::columns('project_dev','refNo')) {
		$stmt='ALTER TABLE %project_dev% ADD `refNo` VARCHAR(10) DEFAULT NULL AFTER `pryear`, ADD INDEX (`refNo`)';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}
	if (!mydb::columns('project_dev','commenthsmidate')) {
		$stmt='ALTER TABLE %project_dev% ADD `commenthsmidate` DATETIME NULL DEFAULT NULL AFTER `location`, ADD INDEX (`commenthsmidate`)';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}

	// Table project_dev
	if (!mydb::columns('project_dev','commentsssdate')) {
		$stmt='ALTER TABLE %project_dev% ADD `commentsssdate` DATETIME NULL DEFAULT NULL AFTER `commenthsmidate`, ADD INDEX (`commentsssdate`)';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}


	// TODO: After add commenthsmidate and commentsssdate field, update data from table topic



	if (!mydb::table_exists('project_target')) {
		$stmt = 'CREATE TABLE %project_target% (
			`tpid` INT(11) NOT NULL,
			`trid` INT(11) UNSIGNED NOT NULL,
			`tagname` VARCHAR(30) NOT NULL,
			`tgtid` VARCHAR(50) NULL DEFAULT NULL,
			`amount` INT(11) NOT NULL,
			`joinamt` INT(11) NULL DEFAULT NULL,
			`currentind1` DECIMAL(5,2) DEFAULT NULL,
			`currentind2` DECIMAL(5,2) DEFAULT NULL,
			`currentind3` DECIMAL(5,2) DEFAULT NULL,
			`expectind1` DECIMAL(5,2) DEFAULT NULL,
			`expectind2` DECIMAL(5,2) DEFAULT NULL,
			`expectind3` DECIMAL(5,2) DEFAULT NULL,
			PRIMARY KEY (`tpid`,`trid`,`tagname`,`tgtid`) USING BTREE,
			KEY `targetname` (`targetname`)
		);';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}

	if (!mydb::columns('project_target','joinamt')) {
		$stmt = 'ALTER TABLE %project_target% ADD `joinamt` INT DEFAULT NULL AFTER `amount`;';
		mydb::query($stmt);
		$result[] = mydb()->_query;
	}

	if (!mydb::columns('project_target','trid')) {
		$stmt = 'ALTER TABLE %project_target%
			ADD `trid` INT(11) UNSIGNED NOT NULL AFTER `tpid`,
			ADD `tagname` VARCHAR(30) NOT NULL AFTER `trid`,
			ADD `currentind1` VARCHAR(30) NOT NULL AFTER `joinamt`,
			ADD `currentind2` VARCHAR(30) NOT NULL AFTER `currentind1`,
			ADD `currentind3` VARCHAR(30) NOT NULL AFTER `currentind2`,
			ADD `expectind1` VARCHAR(30) NOT NULL AFTER `currentind3`,
			ADD `expectind2` VARCHAR(30) NOT NULL AFTER `expectind1`,
			ADD `expectind3` VARCHAR(30) NOT NULL AFTER `expectind2`,
			DROP PRIMARY KEY,
			ADD PRIMARY KEY (`tpid`,`trid`,`tagname`,`tgtid`) USING BTREE
			;';
		mydb::query($stmt);
		$result[] = mydb()->_query;
	}


	if (!mydb::columns('org_dos','printweight')) {
		$stmt = 'ALTER TABLE %org_dos% ADD `printweight` INT NOT NULL DEFAULT 0 AFTER `isjoin`;';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}

	/*
	if (!mydb::columns('project_target','targetname')) {
		$stmt = 'ALTER TABLE %project_target% ADD `targetname` VARCHAR(50) NULL DEFAULT NULL AFTER `tgtid`, ADD INDEX (`targetname`);';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}
	*/

	if (!mydb::columns('project_tr','appsrc')) {
		$stmt = 'ALTER TABLE %project_tr%
			ADD `appsrc` ENUM("Web","Web App","Android","iOS") NULL DEFAULT NULL AFTER `text10`,
			ADD `appagent` VARCHAR(40) NULL DEFAULT NULL AFTER `appsrc`,
			ADD INDEX (`appsrc`)';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}



	if ($result) {
		$ret.='<ul><li>'.implode('</li><li>',$result).'</li></ul>';
	} else {
		$ret.='<p>ระบบเป็นรุ่นล่าสุดแล้ว</p>';
	}
	return $ret;
}
?>