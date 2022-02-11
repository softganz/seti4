<?php
function garage_finance($self) {
	$shopInfo=R::Model('garage.get.shop');

	R::Model('garage.verify',$self, $shopInfo,'FINANCE');

	new Toolbar($self,'การเงิน','finance');


	$ui = new Ui('div', 'ui-card');

	$stmt = 'SELECT COUNT(*) `totalJob` FROM %garage_job% j WHERE j.`shopid` = :shopid LIMIT 1';
	$totalJob = mydb::select($stmt,':shopid',$shopInfo->shopid)->totalJob;
	$ui->add(
		'<div class="header"><h3>ใบสั่งซ่อม</h3></div>'
		. '<div class="detail"><span>จำนวน '.number_format($totalJob).' ใบ</span></div>'
	);

	$stmt = 'SELECT COUNT(*) `totalQt` FROM %garage_qt% q LEFT JOIN %garage_job% j USING(`tpid`) WHERE j.`shopid` = :shopid LIMIT 1';
	$totalQt = mydb::select($stmt,':shopid',$shopInfo->shopid)->totalQt;
	$ui->add(
		'<div class="header"><h3>ใบเสนอราคา</h3></div>'
		. '<div class="detail"><span>จำนวน '.number_format($totalQt).' ใบ</span></div>'
	);

	$stmt = 'SELECT COUNT(*) `totalInvoice` FROM %garage_invoice% WHERE `shopid`=:shopid LIMIT 1';
	$totalInvoice = mydb::select($stmt,':shopid',$shopInfo->shopid)->totalInvoice;
	$ui->add(
		'<div class="header"><h3>ใบแจ้งหนี้</h3></div>'
		. '<div class="detail"><span>จำนวน '.number_format($totalInvoice).' ใบ</span></div>'
	);

	$stmt = 'SELECT COUNT(*) `totalBilling` FROM %garage_billing% WHERE `shopid`=:shopid LIMIT 1';
	$totalBilling = mydb::select($stmt,':shopid',$shopInfo->shopid)->totalBilling;
	$ui->add(
		'<div class="header"><h3>ใบวางบิล</h3></div>'
		. '<div class="detail"><span>จำนวน '.number_format($totalBilling).' ใบ</span></div>'
	);

	$stmt = 'SELECT COUNT(*) `totalRcv` FROM %garage_rcv% WHERE `shopid`=:shopid LIMIT 1';
	$totalRcv = mydb::select($stmt,':shopid',$shopInfo->shopid)->totalRcv;
	$ui->add(
		'<div class="header"><h3>ใบเสร็จรับเงิน</h3></div>'
		. '<div class="detail"><span>จำนวน '.number_format($totalRcv).' ใบ</span></div>'
	);

	$stmt = 'SELECT COUNT(*) `totalRcvMoney` FROM %garage_job% WHERE `shopid`=:shopid AND `isrecieved`="Yes" LIMIT 1';
	$totalRcvMoney = mydb::select($stmt,':shopid',$shopInfo->shopid)->totalRcvMoney;
	$ui->add(
		'<div class="header"><h3>ใบรับเงิน</h3></div>'
		. '<div class="detail"><span>จำนวน '.number_format($totalRcvMoney).' ใบ</span></div>'
	);

	$stmt = 'SELECT COUNT(*) `totals` FROM %garage_appaid% WHERE `shopid`=:shopid LIMIT 1';
	$totalApPaid = mydb::select($stmt,':shopid',$shopInfo->shopid)->totals;
	$ui->add(
		'<div class="header"><h3>ใบจ่ายชำระหนี้</h3></div>'
		. '<div class="detail"><span>จำนวน '.number_format($totalApPaid).' ใบ</span></div>'
	);

	$ret .= $ui->build();

	return $ret;
}
?>