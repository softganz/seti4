<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function map_admin_upgrade($self) {
	$ret = '';

	if (!SG\confirm()) {
		return '<p>ต้องการอัพเกรดฐานข้อมูลของ <b>Mapping</b> จริงหรือไม่?<br />กรุณายืนยันการอัพเกรด<br /><nav class="nav -page"><a class="btn -danger" href="'.url('map/admin/upgrade',array('confirm'=>'Yes')).'"><i class="icon -save"></i><span>ยืนยันการอัพเกรด</span></a></nav></p><p><b>คำเตือน</b> ควรสำรองข้อมูลให้เรียบร้อยก่อนดำเนินการอัพเกรดข้อมูล</p>';
	}

	$result=array();

	if (!mydb::columns('map_name','orgid')) {
		$stmt='ALTER TABLE %map_name%
					  ADD `orgid` INT UNSIGNED NULL DEFAULT NULL AFTER `uid`
					, ADD INDEX (`orgid`);';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}

	if (!mydb::columns('map_networks','reforg')) {
		$stmt='ALTER TABLE %map_networks%
					  ADD `reforg` INT UNSIGNED NULL DEFAULT NULL AFTER `mapgroup`
					, ADD `sector` INT UNSIGNED NULL DEFAULT NULL AFTER `dowhat`
					, ADD `mapemail` VARCHAR(30) NULL DEFAULT NULL AFTER `changwat`
					, ADD `mapphone` VARCHAR(20) NULL DEFAULT NULL AFTER `mapemail`
					, ADD `contactname` VARCHAR(30) NULL DEFAULT NULL AFTER `mapphone`
					, ADD `contactemail` VARCHAR(20) NULL DEFAULT NULL AFTER `contactname`
					, ADD `contactphone` VARCHAR(20) NULL DEFAULT NULL AFTER `contactemail`
					, ADD `yearstart` YEAR NULL DEFAULT NULL AFTER `contactphone`
					, ADD `yearend` YEAR NULL DEFAULT NULL AFTER `yearstart`
					, ADD INDEX (`reforg`)
					, ADD INDEX (`sector`)
					;';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}

	if (!mydb::columns('map_networks','areacode')) {
		$stmt='ALTER TABLE %map_networks%
					  ADD `areacode` VARCHAR(8) NULL DEFAULT NULL AFTER `when`
					, ADD INDEX (`areacode`);';
		mydb::query($stmt);
		$result[]=mydb()->_query;

		$stmt = 'UPDATE %map_networks% SET `areacode` = CONCAT(`changwat`,IFNULL(`ampur`,"00"),IFNULL(`tambon`,"00"),LPAD(TRIM(IFNULL(`village`,"")),2,"0"))';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}



	if ($result) {
		$ret.='<ul><li>'.implode('</li><li>',$result).'</li></ul>';
	} else {
		$ret.='<p>ระบบเป็นรุ่นล่าสุดแล้ว</p>';
	}

	return $ret;

/*
Upgrade
TRUNCATE TABLE sgz_map_networks;
ALTER TABLE `sgz_map_networks` DROP `patriarch` , DROP `parent` ;
ALTER TABLE `sgz_map_networks` ADD `gid` INT UNSIGNED NOT NULL AFTER `mapid` , ADD INDEX ( `gid` ) ;
ALTER TABLE `sgz_map_networks` ADD `detail` TEXT NOT NULL AFTER `address` ;
ALTER TABLE `sgz_map_networks` ADD `privacy` ENUM( 'private', 'public', 'group' ) NOT NULL DEFAULT 'private' AFTER `uid` , ADD INDEX ( `privacy` ) ;
ALTER TABLE `sgz_map_networks` ADD `village` CHAR( 2 ) NULL AFTER `address` ,
ADD `tambon` CHAR( 2 ) NULL AFTER `village` ,
ADD `ampur` CHAR( 2 ) NULL AFTER `tambon` ,
ADD `changwat` CHAR( 2 ) NULL AFTER `ampur` ,
ADD INDEX ( `village` , `tambon` , `ampur` , `changwat` ) ;
ALTER TABLE `sgz_map_networks` ADD `latlng` POINT NOT NULL AFTER `detail` , ADD INDEX ( `latlng` ) ;
INSERT INTO `sgz_map_networks` (SELECT NULL,1,`uid`,2,`who`,`dowhat`,`prepare`,`during`,`after`,`when`,`address`,'','','','90','',`latlng`, `gis`,`photo`,`ip`,`created`
FROM `sgz_map_networks_bak` m
LEFT JOIN `sgz_gis` USING(gis)
WHERE mapid IN (
			SELECT MAX(mapid)
			FROM `sgz_map_networks_bak`
			GROUP BY IFNULL(patriarch,mapid)
		))
ORDER BY `mapid` ASC
*/
}
?>