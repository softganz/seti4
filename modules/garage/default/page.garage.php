<?php
/**
* Garage Main Page
* Created 2018-08-12
* Modify  2020-07-23
*
* @param Object $self
* @return String
*
* @usage garage
*/

$debug = true;

function garage($self) {
	// Data Model
	cfg('page_id','home');

	$shopInfo = R::Model('garage.get.shop');
	$shopId = $shopInfo->shopid;

	if (!R::Model('garage.right',$shopInfo,'job')) return R::Page('garage.app', $self);

	$welcomeTime = 'Good morning';
	if (date('H')>=17) $welcomeTime = 'Good evening';
	else if (date('H')>=12) $welcomeTime = 'Good afternoon';

	$stmt = 'SELECT COUNT(*) `totalCarIn`
		FROM %garage_job% j
		WHERE j.`shopid` IN ( :shopid )
			AND j.`templateid` IS NOT NULL
		LIMIT 1';
	$totalCarIn = mydb::select($stmt, ':shopid', 'SET:'.$shopInfo->branchId)->totalCarIn;

	$stmt = 'SELECT COUNT(*) `totalJob`
		FROM %garage_job% j
		WHERE (j.`shopid` = :shopid OR (j.`shopid` IN (SELECT `shopid` FROM %garage_shop% WHERE `shopparent` = :shopid)))
			AND j.`iscarreturned` = "No"
		LIMIT 1';
	$totalJob = mydb::select($stmt, ':shopid', $shopId)->totalJob;

	$stmt = 'SELECT COUNT(DISTINCT `tpid`) `totalDo`
		FROM %garage_do% do
			LEFT JOIN %garage_job% j USING(`tpid`)
		WHERE do.`status` = "OPEN"
			AND (j.`shopid` = :shopid OR (j.`shopid` IN (SELECT `shopid` FROM %garage_shop% WHERE `shopparent` = :shopid)))
			AND j.`iscarreturned` = "No"
		LIMIT 1';
	$totalDo = mydb::select($stmt, ':shopid', $shopId)->totalDo;

	$stmt = 'SELECT
		DATE_FORMAT(`datecmd`,"%Y-%m-01") `rcvMonth`
		, tr.`jobtrid`
		, COUNT(DISTINCT `tpid`) `totalJob`
		, COUNT(*) `totalPart`
		FROM %garage_jobtr% tr
			LEFT JOIN %garage_job% j USING(`tpid`)
		WHERE tr.`wait` > 0
			AND (j.`shopid` = :shopid OR (j.`shopid` IN (SELECT `shopid` FROM %garage_shop% WHERE `shopparent` = :shopid)))
		GROUP BY `rcvMonth`
		ORDER BY `rcvMonth` ASC;
		-- {sum: "totalPart"}';

	$waitDbs = mydb::select($stmt,':shopid',$shopId);
	$waitCount = $waitDbs->sum->totalPart;

	$chartDbs = mydb::select(
		'SELECT `jobstatus`,COUNT(*) `total`
		FROM %garage_job%
		WHERE `shopid`=:shopid AND `jobstatus` < 10
		GROUP BY `jobstatus`
		ORDER BY `jobstatus` ASC',
		':shopid',$shopId
	);



	// View Model
	$ret = '<div class="home">';
	$ret .= '<h2>'.$welcomeTime.', '.i()->name.'</h2>';

	$ret .= '<form id="search" class="search-box" method="get" action="'.url('garage/job/search').'" role="search"><input type="hidden" name="jid" id="jid" /><input id="search-box" class="sg-autocomplete form-text" type="text" name="q" size="40" value="'.post('q').'" placeholder="ป้อนทะเบียนรถหรือเลข job" data-query="'.url('garage/api/job', array('shop' => '*')).'" data-callback="'.url('garage/job').'" data-altfld="jid"><button class="button" type="submit"><i class="icon -search"></i>ค้นหา</button></form>'._NL;

	$mainUi = new Ui('div', 'ui-card');
	$mainUi->addConfig('nav', '{class:"nav -master"}');

	$userUi = new Ui(NULL,'-user -sg-flex -justify-right');
	$userUi->add('<span class="count" title="ใบรับรถที่ยังไม่ทำใบเสนอราคา">'.$totalCarIn.'</span>');

	$mainUi->add(
		$userUi->build()
		.'<i class="icon -i48 -carin"></i><span>รับรถ<br />&nbsp;</span>',
		array(
			'class' => 'sg-action',
			'href' => url('garage/in'),
			'data-webview' => 'รับรถ',
			'onclick' => '',
		)
	);

	$userUi = new Ui(NULL,'-user -sg-flex -justify-right');
	$userUi->add('<span class="count" title="จ็อบที่ยังไม่คืนรถ">'.$totalJob.'</span>');
	$mainUi->add(
		$userUi->build()
		. '<i class="icon -i48 -job"></i><span>สั่งซ่อม</span>',
		array(
			'class' => 'sg-action',
			'href' => url('garage/job'),
			'onclick' => '',
		)
	);


	$userUi = new Ui(NULL,'-user -sg-flex -justify-right');
	$userUi->add('<span class="count";">'.$totalDo.'</span>');
	$mainUi->add(
		$userUi->build()
		. '<i class="icon -i48 -do"></i><span>สั่งงาน</span>',
		array(
			'class' => 'sg-action',
			'href' => url('garage/do'),
			'onclick' => '',
		)
	);

	$userUi = new Ui(NULL,'-user -sg-flex -justify-right');
	$userUi->add('<span class="count">'.$waitCount.'</span>');

	$mainUi->add(
		$userUi->build()
		. '<i class="icon -i48 -part"></i><span>อะไหล่</span>',
		array(
			'class' => 'sg-action',
			'href' => url('garage/part'),
			'onclick' => '',
		)
	);
	$mainUi->add('<a href="'.url('garage/finance').'"><i class="icon -i48 -finance"></i><span>การเงิน</span></a>');
	$mainUi->add('<a href="'.url('garage/report').'"><i class="icon -i48 -report"></i><span>วิเคราะห์</span></a>');

	$ret .= $mainUi->build()._NL;


	$ui = new Ui('div', 'ui-card');
	$ui->addConfig('nav', '{class: "nav -submaster"}');

	$ui->add('<a href="'.url('garage/job').'"><i class="icon -material">directions_car</i><span>ใบสั่งซ่อม</span></a>');
	$ui->add('<a href="'.url('garage/do').'"><i class="icon -material">assignment</i><span>ใบสั่งงาน</span></a>');
	$ui->add('<a href="'.url('garage/do/photo').'"><i class="icon -material">photo_album</i><span>ภาพถ่าย</span></a>');
	$ui->add('<a class="" href="'.url('garage/order').'"><i class="icon -material">directions_car</i><span>ใบสั่งของ</span></a>');
	$ui->add('<a class="" href="'.url('garage/aprcv').'"><i class="icon -material">directions_car</i><span>ใบรับของ</span></a>');
	$ui->add('<a href="'.url('garage/qt').'"><i class="icon -material">mail</i><span>ใบเสนอราคา</span></a>');
	$ui->add('<a href="'.url('garage/invoice').'"><i class="icon -material">alarm_on</i><span>ใบแจ้งหนี้</span></a>');
	$ui->add('<a href="'.url('garage/billing').'"><i class="icon -material">check_box</i><span>ใบวางบิล</span></a>');
	$ui->add('<a class="" href="'.url('garage/recieve').'"><i class="icon -material">attach_money</i><span>ใบเสร็จรับเงิน</span></a>');

	$ret .= $ui->build()._NL;

	$ui = new Ui('div', 'ui-card');
	$ui->addConfig('nav', '{class: "nav -submaster"}');

	$ui->add('<a href="'.url('garage/code/insurer').'"><i class="icon -material">directions_car</i><span>บริษัทประกัน</span></a>');
	$ui->add('<a class="" href="'.url('garage/code/customer').'"><i class="icon -material">group</i><span>ลูกค้า</span></a>');
	$ui->add('<a class="" href="'.url('garage/code/ap').'"><i class="icon -material">group</i><span>เจ้าหนี้</span></a>');
	//$ui->add('<a class="" href="'.url('garage/code/partner').'"><i class="icon -material">group</i><span>ตัวแทน</span></a>');
	//$ui->add('<a class="-disabled" href="'.url('garage/job').'"><i class="icon -material">directions_car</i><span>ธนาคาร</span></a>');
	//$ui->add('<a class="-disabled" href="'.url('garage/job').'"><i class="icon -material">directions_car</i><span>อู่ซ่อม</span></a>');
	//$ui->add('<a class="-disabled" href="'.url('garage/job').'"><i class="icon -material">directions_car</i><span>ประเภท Job</span></a>');
	$ui->add('<a href="'.url('garage/shop/member').'"><i class="icon -material">group</i><span>พนักงาน</span></a>');
	//$ui->add('<a href="'.url('garage/job').'"><i class="icon -material">directions_car</i><span>คำนำหน้าชื่อ</span></a>');
	$ret .= $ui->build()._NL;



	$ui = new Ui('div', 'ui-card');
	$ui->addConfig('nav', '{class: "nav -submaster"}');

	$ui->add('<a href="'.url('garage/code/damage').'"><i class="icon -material">directions_car</i><span>ความเสียหาย</span></a>');
	$ui->add('<a href="'.url('garage/code/repair').'"><i class="icon -material">directions_car</i><span>รหัสสั่งซ่อม</span></a>');
	$ui->add('<a href="'.url('garage/code/part').'"><i class="icon -material">directions_car</i><span>รหัสอะไหล่</span></a>');
	$ui->add('<a href="'.url('garage/code/wage').'"><i class="icon -material">directions_car</i><span>รหัสค่าแรง</span></a>');
	$ui->add('<a href="'.url('garage/code/jobtemplate').'"><i class="icon -material">directions_car</i><span>แบบสั่งซ่อม</span></a>');
	$ui->add('<a href="'.url('garage/code/brand').'"><i class="icon -material">directions_car</i><span>ยี่ห้อรถ</span></a>');
	//$ui->add('<a class="-disabled" href="'.url('garage/code/cartype').'"><i class="icon -material">directions_car</i><span>ประเภทรถ</span></a>');
	//$ui->add('<a class="-disabled" href="'.url('garage/code/gear').'"><i class="icon -material">directions_car</i><span>เกียร์</span></a>');
	//$ui->add('<a class="-disabled" href="'.url('garage/code/colortype').'"><i class="icon -material">directions_car</i><span>ประเภทสี</span></a>');
	//$ui->add('<a class="-disabled" href="'.url('garage/code/color').'"><i class="icon -material">directions_car</i><span>สี</span></a>');
	$ret .= $ui->build()._NL;


	// Show analysis chart
	$graphYear = new Table();
	foreach ($chartDbs->items as $rs) {
		$graphYear->rows[] = array(
			'string:Year'=> GarageVar::$jobStatusList[$rs->jobstatus],
			'number:Budget'=>$rs->total
		);
	}

	$ret .= '<div class="garage-chart -sg-flex" style="padding: 32px 0;">';

	$ret .= '<div class="present"><h3>Job Analysis</h3><div id="chart-job" class="sg-chart -chart-job" data-chart-type="pie">'._NL.$graphYear->build().'</div></div>'._NL;

	$graphYear = new Table();
	foreach ($waitDbs->items as $rs) {
		$graphYear->rows[] = array(
			'string:เดือน-ปี'=>sg_date($rs->rcvMonth,'m/ปปปป'),
			'number:จำนวนรถรออะไหล่'=>$rs->totalJob,
			'number:จำนวนอะไหล่รอ'=>$rs->totalPart,
		);
	}
	$ret .= '<div class="present"><h3>Part Analysis</h3><div id="chart-part" class="sg-chart -chart-part" data-chart-type="col">'._NL.$graphYear->build().'</div></div>'._NL;
	$ret .= '</div>';


	$ret .= '<div class="qrcode-playstore" style="clear: both; margin: 32px 0; background-color: #fff; text-align: center;"><a href="https://play.google.com/store/apps/details?id=com.softganz.shop.abcg" title="ดาวน์โหลดแอบจาก Play Store" target="_blank"><img class="qrcode" src="https://chart.googleapis.com/chart?cht=qr&chs=120x120&choe=UTF-8&chld=L|2&chl=https://play.google.com/store/apps/details?id=com.softganz.shop.abcg" alt="" style="display: block; margin:0 auto;"><br>ดาวน์โหลดแอพสำหรับสมาร์ทโฟนแอนดรอยด์<br />(Android Smart Phone)</a></div>';

	$ret.='</div>';


	head('googlegraph','<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');
	return $ret;
}
?>