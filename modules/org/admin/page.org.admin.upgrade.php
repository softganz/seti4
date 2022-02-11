<?php
/**
* Org Upgrade Method
*
* @param Object $self
* @return String
*/
class OrgAdminUpgrade extends Page {
	function build() {
		if (!SG\confirm()) {
			return new Scaffold([
				'appBar' => new AppBar([
					'title' => 'อัพเกรดฐานข้อมูลระบบบริหารองค์กร',
				]),
				'body' => '<div class="-sg-text-center" style="padding: 0 32px;">'
					. '<p><b>อัพเกรดฐานข้อมูลระบบบริหารองค์กร</b><br /><br />กรุณายืนยันการอัพเกรด?<br /><br />'
					. '<nav class="nav -page"><a class="btn -link -cancel" href="'.url('org/admin').'"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a> <a class="sg-action btn -danger" href="'.url('org/admin/upgrade',array('confirm'=>'Yes')).'" data-rel="#main"><i class="icon -material">done_all</i><span>ยืนยันการอัพเกรด</span></a></nav>'
					. '</p>'
					. '<p><b>คำเตือน</b> ควรสำรองข้อมูลให้เรียบร้อยก่อนดำเนินการอัพเกรดข้อมูล</p>'
					. '</div>',
			]);
		}

		$result=array();


		// Upgrade db_org
		if (!mydb::columns('db_org','enshortname')) {
			$stmt='ALTER TABLE %db_org% ADD `enshortname` varchar(20) DEFAULT NULL AFTER `shortname`, ADD INDEX (`enshortname`);';
			mydb::query($stmt);
			$result[] = mydb()->_query;
		}

		if (!mydb::columns('db_org','house')) {
			$stmt = 'ALTER TABLE %db_org%
				ADD `house` VARCHAR(255) NULL DEFAULT NULL AFTER `enshortname`;';
			mydb::query($stmt);
			$result[] = mydb()->_query;
		}

		if (!mydb::columns('db_org','facebook')) {
			$stmt = 'ALTER TABLE %db_org%
				ADD `facebook` VARCHAR(100) NULL DEFAULT NULL AFTER `website`;';
			mydb::query($stmt);
			$result[] = mydb()->_query;
		}

		if (!mydb::columns('db_org','zipcode')) {
			$stmt = 'ALTER TABLE %db_org%
				ADD `zipcode` VARCHAR(5) NULL DEFAULT NULL AFTER `house`
				, ADD INDEX (`zipcode`);';
			mydb::query($stmt);
			$result[] = mydb()->_query;
		}

		// Upgrade changwat,ampur,tambon,village to areacode
		// ***** Manual delete field changwat,ampur,tambon,village after complate *****
		if (!mydb::columns('db_org','areacode')) {
			$stmt='ALTER TABLE %db_org%
				  ADD `areacode` VARCHAR(8) NULL DEFAULT NULL AFTER `enshortname`
				, ADD INDEX (`areacode`);';
			mydb::query($stmt);
			$result[] = mydb()->_query;

			if (mydb::columns('db_org','changwat')) {
				$stmt = 'UPDATE %db_org% SET `areacode` = IF(`changwat` IS NULL OR `changwat` = "", NULL, CONCAT(`changwat`,IFNULL(`ampur`,"00"),IFNULL(`tambon`,"00"),LPAD(TRIM(IFNULL(`village`,"")),2,"0")))';
				mydb::query($stmt);
				$result[] = mydb()->_query;
			}
		}

		if (!mydb::columns('db_org','template')) {
			$stmt = 'ALTER TABLE %db_org%
				ADD `template` VARCHAR(40) NULL DEFAULT NULL AFTER `sector`;';
			mydb::query($stmt);
			$result[] = mydb()->_query;
		}

		if (!mydb::columns('db_org','groupType')) {
			mydb::query(
				'ALTER TABLE %db_org%
				ADD `groupType` VARCHAR(30) NULL DEFAULT NULL AFTER `enshortname`
				, ADD INDEX (`groupType`);'
			);
			$result[] = mydb()->_query;
		}


		// Upgrade org_dos
		if (!mydb::columns('org_dos','joingroup')) {
			$stmt = 'ALTER TABLE %org_dos%
				  ADD `uid` INT UNSIGNED NULL DEFAULT NULL AFTER `doid`
				, ADD `joingroup` VARCHAR(50) NULL DEFAULT NULL AFTER `jointype`
				, ADD `foodtype` ENUM("ทั่วไป","มุสลิม","มังสวิรัติ") NULL DEFAULT NULL AFTER `joingroup`
				, ADD `tripby` VARCHAR(20) NULL DEFAULT NULL AFTER `foodtype`
				, ADD `rest` VARCHAR(20) NULL DEFAULT NULL AFTER `tripby`
				, ADD INDEX (`uid`)
				, ADD INDEX (`foodtype`)
				, ADD INDEX (`joingroup`)
				';
			mydb::query($stmt);
			$result[] = mydb()->_query;
		}

		if (!mydb::columns('org_dos', 'information')) {
			$stmt = 'ALTER TABLE %org_dos% ADD `information` TEXT NULL DEFAULT NULL AFTER `refcode`;';
			mydb::query($stmt);
			$result[] = mydb()->_query;
		}

		if (!mydb::columns('org_dos', 'created')) {
			$stmt = 'ALTER TABLE %org_dos%
				ADD `created` BIGINT(20) DEFAULT NULL AFTER `information`
				, ADD INDEX (`created`)
				';
			mydb::query($stmt);
			$result[] = mydb()->_query;
		}

		if (!mydb::columns('org_dos', 'withdrawrest')) {
			$stmt = 'ALTER TABLE %org_dos%
				ADD `withdrawrest` TINYINT NULL DEFAULT NULL AFTER `rest`
				';
			mydb::query($stmt);
			$result[] = mydb()->_query;
		}


		// Upgrade org_doings
		if (!mydb::columns('org_doings', 'isregister')) {
			$stmt = 'ALTER TABLE %org_doings%
					ADD `isregister` TINYINT NULL DEFAULT NULL AFTER `uid`
				, ADD `registstart` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `fromtime`
				, ADD `registend` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `registstart`
				, ADD INDEX (`registstart`)
				, ADD INDEX (`registend`)
				';
			mydb::query($stmt);
			$result[] = mydb()->_query;
		}

		if (!mydb::columns('org_doings', 'areacode')) {
			$stmt = 'ALTER TABLE %org_doings%
				ADD `areacode` VARCHAR(6) DEFAULT NULL AFTER `atdate`,
				ADD `paiddocfrom` VARCHAR(100) DEFAULT NULL AFTER `registend`,
				ADD `paiddoctagid` VARCHAR(13) DEFAULT NULL AFTER `paiddocfrom`,
				ADD `paiddocbyname` VARCHAR(50) DEFAULT NULL AFTER `paiddoctagid`,
				ADD `paiddocdate` DATE DEFAULT NULL AFTER `paiddocbyname`,
				ADD `registerrem` TEXT DEFAULT NULL AFTER `paiddocdate`
				';
			mydb::query($stmt);
			$result[] = mydb()->_query;
		}

		if (!mydb::columns('org_doings', 'options')) {
			$stmt = 'ALTER TABLE %org_doings%
				ADD `paidgroup` TEXT DEFAULT NULL AFTER `registerrem`,
				ADD `options` TEXT DEFAULT NULL AFTER `paidgroup`
				';
			mydb::query($stmt);
			$result[] = mydb()->_query;
		}


		// Upgrade db_person
		if (!mydb::columns('db_person', 'areacode')) {
			$stmt = 'ALTER TABLE %db_person%
				ADD `areacode` VARCHAR(8) DEFAULT NULL COMMENT "Home Area Code" AFTER `birth`
				, ADD `hrareacode` VARCHAR(8) DEFAULT NULL COMMENT "House Registration Area Code" AFTER `areacode`
				, ADD INDEX (`areacode`)
				, ADD INDEX (`hrareacode`)
				';
			mydb::query($stmt);
			$result[] = mydb()->_query;
		}

		if (!mydb::columns('db_person', 'userId')) {
			$stmt = 'ALTER TABLE %db_person%
				ADD `userId` INT UNSIGNED NULL DEFAULT NULL AFTER `uid`
				, ADD UNIQUE (`userId`)
				';
			mydb::query($stmt);
			$result[] = mydb()->_query;
		}

		if (!mydb::columns('db_person', 'graduated')) {
			$stmt = 'ALTER TABLE %db_person% ADD `graduated` VARCHAR(100) NULL DEFAULT NULL AFTER `email`
				, ADD `faculty` VARCHAR(100) NULL DEFAULT NULL AFTER `graduated`;
				';
			mydb::query($stmt);
			$result[] = mydb()->_query;
		}

		if (!mydb::columns('db_person', 'orgId')) {
			mydb::query('ALTER TABLE %db_person%
				ADD `orgId` INT(10) DEFAULT NULL DEFAULT NULL AFTER `graduated`
				, ADD `position` VARCHAR(100) NULL DEFAULT NULL AFTER `orgId`
				, ADD INDEX (`orgId`)
				;'
			);
			$result[] = mydb()->_query;
		}





		if ($result) {
			$ret.='<p class="notify">ปรับปรุงระบบเป็นรุ่นล่าสุด</p>';
			$ret.='<ul><li>'.implode('</li><li>',$result).'</li></ul>';
		} else {
			$ret.='<p class="notify">ระบบเป็นรุ่นล่าสุดแล้ว</p>';
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'อัพเกรดฐานข้อมูลระบบบริหารองค์กร',
			]),
			'body' => new Container([
				'class' => '-sg-text-center',
				'children' => [
					$ret,
					'<a class="btn -primary" href="'.url('org/admin').'"><span>เรียบร้อย</span></a>',
				],
			]),
		]);


	/*
	ALTER TABLE `sgz_db_person` CHANGE `aid` `psnid` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ;
	ALTER TABLE `sgz_db_org` CHANGE `oid` `orgid` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT ;

	ALTER TABLE `sgz_org_doings`
		ADD `orgid` INT UNSIGNED NOT NULL AFTER `doid` ,
		ADD INDEX ( `orgid` ),
		ADD `tpid` INT NULL DEFAULT NULL AFTER `orgid` ,
		ADD INDEX ( `tpid` ),
		ADD `calid` INT UNSIGNED NULL DEFAULT NULL AFTER `tpid` ,
		ADD INDEX ( `calid` ),
		ADD `fromtime` TIME NOT NULL;

	ALTER TABLE `sgz_org_dos`
		CHANGE `mid` `psnid` INT( 10 ) UNSIGNED NOT NULL,
		ADD `isjoin` BOOLEAN NOT NULL DEFAULT FALSE AFTER `doid` ,
		ADD INDEX ( `isjoin` ),
		ADD `regtype` ENUM("Invite", "Register", "Walk In") NULL DEFAULT NULL ,
		ADD INDEX ( `regtype` ),
		ADD `jointype` ENUM( "Attendee", "Speaker", "Workshop", "Guest" ) NOT NULL DEFAULT 'Attendee',
		ADD INDEX ( `jointype` ),
		ADD `refcode` VARCHAR( 20 ) NULL DEFAULT NULL , ADD INDEX ( `refcode` ) ;
	UPDATE `sgz_org_dos` SET  `regtype`="Walk In", `jointype`="Attendee";
	UPDATE `sgz_org_dos` SET `isjoin`=1 WHERE `jointype`="Attendee";

	RENAME TABLE `sgz_org_id` TO `sgz_org_ojoin`;
	ALTER TABLE `sgz_org_ojoin`
			ADD `orgid` INT UNSIGNED NOT NULL COMMENT 'id ขององค์กรเจ้าของกิจกรรม' FIRST,
			ADD INDEX ( `orgid` ),
			CHANGE `org_id` `jorgid` INT( 10 ) UNSIGNED NOT NULL COMMENT 'id ขององค์กรที่เข้าร่วมกิจกรรม',
			CHANGE `owner` `uid` INT( 10 ) UNSIGNED NULL DEFAULT NULL,
			CHANGE `org_type` `type` INT( 10 ) UNSIGNED NULL DEFAULT NULL ,
			CHANGE `org_issue` `issue` INT( 10 ) UNSIGNED NULL DEFAULT NULL ,
			CHANGE `org_register_date` `joindate` DATE NULL DEFAULT NULL ,
			CHANGE `org_created_date` `created` DATETIME NULL DEFAULT NULL ;



	CREATE TABLE IF NOT EXISTS `sgz_org_mjoin` (
	  `orgid` int(10) unsigned NOT NULL,
	  `psnid` int(10) unsigned NOT NULL,
	  `uid` int(10) unsigned DEFAULT NULL,
	  `joindate` date DEFAULT NULL,
	  `created` datetime DEFAULT NULL,
	  PRIMARY KEY (`orgid`,`psnid`),
	  KEY `uid` (`uid`),
	  KEY `joindate` (`joindate`),
	  KEY `created` (`created`)
	) ENGINE=MyISAM;

	INSERT INTO `sgz_org_mjoin` SELECT NULL,`id`,`owner`,`register_date`,`created_date` FROM `sgz_org_member` ORDER BY `register_date` ASC;

	CREATE TABLE IF NOT EXISTS `sgz_org_morg` (
	  `psnid` int(10) unsigned NOT NULL,
	  `orgid` int(10) unsigned DEFAULT NULL,
	  `uid` int(10) unsigned DEFAULT NULL,
	  `department` varchar(50) DEFAULT NULL,
	  `position` varchar(50) DEFAULT NULL,
	  PRIMARY KEY (`psnid`, `orgid`),
	  KEY `orgid` (`orgid`)
	);
	INSERT INTO `sgz_org_morg` SELECT `id`, `org_id`, `owner`,`department`,`position` FROM `sgz_org_member` WHERE `org_id`>0 ORDER BY `register_date` ASC;


	// For communeinfo only
	UPDATE `sgz_org_ojoin` SET `orgid`=781;
	UPDATE `sgz_org_doings` SET `orgid`=781;
	UPDATE `sgz_org_mjoin` SET `orgid`=781;
	UPDATE `sgz_tag` SET `ownid`=781 WHERE `taggroup`='org:issue';
	UPDATE `sgz_tag` SET `taggroup`='org:type', `vid`=NULL WHERE vid=9;

	// Change all uid to 74 : punya

	// Upgrade version
	ALTER TABLE `sgz_tag` ADD `catid` INT UNSIGNED NULL DEFAULT NULL AFTER `taggroup` , ADD INDEX ( `catid` ) ;
	ALTER TABLE `sgz_tag` ADD `ownid` INT UNSIGNED NULL DEFAULT NULL AFTER `vid` , ADD INDEX ( `ownid` ) ;



	ALTER TABLE `sgz_db_person` ADD `rhouse` CHAR(255) NULL DEFAULT NULL AFTER `zip`, ADD `rvillage` CHAR(2) NULL DEFAULT NULL AFTER `rhouse`, ADD `rtambon` CHAR(2) NULL DEFAULT NULL AFTER `rvillage`, ADD `rampur` CHAR(2) NULL DEFAULT NULL AFTER `rtambon`, ADD `rchangwat` CHAR(2) NULL DEFAULT NULL AFTER `rampur`, ADD `rzip` CHAR(5) NULL DEFAULT NULL AFTER `rchangwat`, ADD INDEX (`rvillage`), ADD INDEX (`rtambon`), ADD INDEX (`rampur`), ADD INDEX (`rchangwat`), ADD INDEX (`rzip`);

	ALTER TABLE  `sgz_db_person` ADD  `commune` VARCHAR( 100 ) NULL DEFAULT NULL AFTER  `village` ,
	ADD INDEX (  `commune` );

	ALTER TABLE `sgz_db_person` ADD `gis` BIGINT UNSIGNED NULL DEFAULT NULL AFTER `remark`, ADD INDEX (`gis`);


	ALTER TABLE  `sgz_db_person` ADD  `nickname` VARCHAR( 50 ) NULL DEFAULT NULL AFTER  `lname` ,
	ADD INDEX (  `nickname` );

	ALTER TABLE `sgz_db_person` CHANGE `nickname` `nickname` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;

	ALTER TABLE `sgz_db_person` ADD `importseries` INT NULL DEFAULT NULL AFTER `umodify`, ADD `importtype` ENUM('update') NULL DEFAULT NULL AFTER `importseries`, ADD INDEX (`importseries`);
	*/
	}
}
?>