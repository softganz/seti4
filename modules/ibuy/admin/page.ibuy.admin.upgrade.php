<?php
/**
 * Upgrade ibuy table staucture
 *
 * @return String
 */
function ibuy_admin_upgrade($self) {
	$self->theme->title='iBuy Upgrade';
	$self->theme->sidebar=R::Page('ibuy.admin.menu');


	if (!SG\confirm()) return '<p>ระบบจะทำการปรับปรุงฐานข้อมูลให้เป็นรุ่นล่าสุด กรุณายืนยันการอัพเกรด <br /><br /><a class="btn -primary" href="'.url('ibuy/admin/upgrade',array('confirm'=>'Yes')).'">ยืนยันการอัพเกรด</a> <a class="btn -cancel" href="'.url('ibuy/admin').'">ยกเลิก</a></p>';

	$targetDb=cfg('ibuy.backup.db');

	$result=array();
	if (!mydb::columns('ibuy_order','orderno')) {
		$stmt='ALTER TABLE %ibuy_order% ADD `orderno` VARCHAR(20) NULL AFTER `classid`, ADD UNIQUE (`orderno`); ';
		mydb::query($stmt);
		$result[]=mydb()->_query;
	}

	if ($targetDb && !mydb::columns('ibuy_order','orderno',$targetDb)) {
		$stmt='ALTER TABLE `'.$targetDb.'`.`sgz_ibuy_order` ADD `orderno` VARCHAR(20) NULL AFTER `classid`, ADD UNIQUE (`orderno`); ';
		mydb::query($stmt);
		$result[]=mydb()->_query;		
	}

	$orderMonths=mydb::select('SELECT DISTINCT FROM_UNIXTIME(`orderdate`,"%Y-%m") `orderMonth` FROM %ibuy_order% WHERE `orderno` IS NULL ORDER BY `orderMonth` ASC')->items;
	foreach ($orderMonths as $item) {
		list($orderYear,$orderMonth)=explode('-', $item->orderMonth);
		$orderYear=substr($orderYear+543,2,2);
		$lastOrderNo=mydb::select('SELECT MAX(`orderno`) `lastNo` FROM %ibuy_order% WHERE FROM_UNIXTIME(`orderdate`,"%Y-%m")=:month LIMIT 1',':month',$item->orderMonth)->lastNo;
		if (empty($lastOrderNo)) {
			$lastOrderNo=$orderYear.$orderMonth.sprintf('%0'.cfg('ibuy.orderdigit').'d',1);
			$stmt='UPDATE %ibuy_order% SET `orderno`=:orderno WHERE `orderno` IS NULL AND FROM_UNIXTIME(`orderdate`,"%Y-%m")=:month ORDER BY `orderdate` ASC LIMIT 1';
			mydb::query($stmt,':month',$item->orderMonth,':orderno',$lastOrderNo);
			$result[]=mydb()->_query;		
		}
		$ret.='Generate orderno of month <b>'.$item->orderMonth.'</b> from <b>'.$lastOrderNo.'</b><br />';
		$stmt='UPDATE %ibuy_order% o ,
						(SELECT @r:=MAX(`orderno`) `maxOrder` FROM %ibuy_order% WHERE FROM_UNIXTIME(`orderdate`,"%Y-%m")=:month) m
					 SET o.`orderno` = @r:=@r+1
					 WHERE `orderno` IS NULL AND FROM_UNIXTIME(`orderdate`,"%Y-%m")=:month';
		mydb::query($stmt,':month',$item->orderMonth);
		$result[]=mydb()->_query;
		$lastOrderNo=mydb::select('SELECT MAX(`orderno`) `lastNo` FROM %ibuy_order% LIMIT 1')->lastNo;
		cfg_db('ibuy.lastorderno',$lastOrderNo);
	}

/*
UPDATE `sgz_ibuy_order` SET `orderno`=NULL
UPDATE `sgz_ibuy_order` SET `orderno`=NULL WHERE `oid`<76036

	UPDATE `sgz_ibuy_order` SET `orderno`=CONCAT(FROM_UNIXTIME(`orderdate`,"%Y%m"),`oid`) WHERE `orderno` IS NULL
UPDATE `sgz_ibuy_order` SET `orderno`=CONCAT(SUBSTRING(FROM_UNIXTIME(`orderdate`,"%Y")+543,0,2),FROM_UNIXTIME(`orderdate`,"%m"),`oid`) WHERE LEFT(`orderno`,2)="20"
SELECT CONCAT(SUBSTRING(FROM_UNIXTIME(`orderdate`,"%Y")+543,2,2),FROM_UNIXTIME(`orderdate`,"%m"),`oid`) `newOrderNo`
FROM  `sgz_ibuy_order` WHERE `orderno` IS NULL
*/



ALTER TABLE `sgz_ibuy_product` CHANGE `stockid` `stockid` VARCHAR(64);
ALTER TABLE `sgz_ibuy_product` CHANGE `prcode` `prcode` VARCHAR(64);
ALTER TABLE `sgz_ibuy_product` CHANGE `barcode` `barcode` VARCHAR(64);
ALTER TABLE `sgz_ibuy_product` CHANGE `forbrand` `forbrand` VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `sgz_ibuy_product` CHANGE `listprice` `listprice` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `sgz_ibuy_product` CHANGE `retailprice` `retailprice` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `sgz_ibuy_product` CHANGE `price1` `price1` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `sgz_ibuy_product` CHANGE `price2` `price2` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `sgz_ibuy_product` CHANGE `price3` `price3` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `sgz_ibuy_product` CHANGE `price4` `price4` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `sgz_ibuy_product` CHANGE `price5` `price5` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `sgz_ibuy_product` CHANGE `resalerprice` `resalerprice` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `sgz_ibuy_product` CHANGE `balance` `balance` INT(11) NOT NULL DEFAULT '0';
ALTER TABLE `sgz_ibuy_product` CHANGE `cost` `cost` DECIMAL(10,2) NULL DEFAULT NULL; 
ALTER TABLE `sgz_ibuy_product` CHANGE `minamt` `minamt` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `sgz_ibuy_product` CHANGE `maxamt` `maxamt` DECIMAL(10,2) NULL DEFAULT NULL;
ALTER TABLE `sgz_ibuy_product` CHANGE `unitid` `unitid` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL; 
ALTER TABLE `sgz_ibuy_product` CHANGE `subunitid` `subunitid` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `sgz_ibuy_product` CHANGE `remember` `remember` VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `sgz_ibuy_order` CHANGE `shipcode` `shipcode` VARCHAR(2) NULL DEFAULT NULL; 




ALTER TABLE `sgz_ibuy_product` ADD `type_id` VARCHAR(32) NULL DEFAULT 'simple' AFTER `tpid`, ADD INDEX (`type_id`);
ALTER TABLE `sgz_ibuy_product` ADD `has_options` TINYINT NOT NULL DEFAULT '0' AFTER `prcode`;
ALTER TABLE `sgz_ibuy_product` ADD `required_options` TINYINT NOT NULL DEFAULT '0' AFTER `has_options`;


UPDATE `sgz_ibuy_franchise` SET `shop_type` = NULL WHERE `shop_type` = '';
UPDATE `sgz_ibuy_franchise` SET `shop_type` = NULL WHERE `shop_type` = '0';
UPDATE `sgz_ibuy_franchise` SET `shop_type` = 'price1' WHERE `shop_type` = '001';
UPDATE `sgz_ibuy_franchise` SET `shop_type` = 'price2' WHERE `shop_type` = '002';
UPDATE `sgz_ibuy_franchise` SET `shop_type` = 'price3' WHERE `shop_type` = '003';
UPDATE `sgz_ibuy_franchise` SET `shop_type` = 'price4' WHERE `shop_type` = '004';

ALTER TABLE `sgz_ibuy_franchise` CHANGE `shop_type` `shop_type` CHAR(20) NULL DEFAULT NULL; 

ALTER TABLE `sgz_ibuy_product` ADD `minsaleqty` INT NULL DEFAULT NULL AFTER `resalerprice`; 

RENAME TABLE `sgz_ibuy_franchise` TO `sgz_ibuy_customer`; 



ALTER TABLE `sgz_ibuy_customer` CHANGE `prename` `prename` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `sgz_ibuy_customer` CHANGE `shop_type` `custtype` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `sgz_ibuy_customer` CHANGE `shop_name` `custname` VARCHAR(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `sgz_ibuy_customer` CHANGE `shop_address` `custaddress` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `sgz_ibuy_customer` CHANGE `shop_license` `custlicense` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `sgz_ibuy_customer` CHANGE `shop_payment_status` `custpaymentstatus` TINYINT(4) NULL DEFAULT '0';
ALTER TABLE `sgz_ibuy_customer` CHANGE `latlng` `location` POINT NULL DEFAULT NULL;


-- Import from ARMAST
INSERT INTO `sgz_ibuy_customer`
(`custimport`, `custcode`, `prename`, `custname`, `custaddress`, `custphone`)
SELECT "C", `CUSCOD`, `PRENAM`, `CUSNAM`, CONCAT(`ADDR01`," ",`ADDR02`, " ", `ADDR03`), `TELNUM` FROM `sgz_ARMAS`


	if ($result) {
		$ret.='<ul><li>'.implode('</li><li>',$result).'</li></ul>';
	} else {
		$ret.='<p>ระบบเป็นรุ่นล่าสุดแล้ว</p>';
	}
	return $ret;
}