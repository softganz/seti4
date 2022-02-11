<?php
/**
* Garage :: Report Home Page
* Created 2020-06-01
* Modify  2020-06-01
*
* @param Object $self
* @return String
*/

$debug = true;

function garage_report_home($self) {
	new Toolbar($self,'รายงานภาพรวม');

	$shopInfo = R::Model('garage.get.shop');

	$getMonth = post('mm');

	$ret = '';


	$stmt = 'SELECT YEAR(`rcvdate`) `year`, DATE_FORMAT(`rcvdate`, "%Y-%m") `month`
		FROM %garage_job%
		WHERE `shopid` IN ( :shopid ) AND `rcvdate` IS NOT NULL
		GROUP BY `month`
		ORDER BY `month`';
	$monthDbs = mydb::select($stmt, ':shopid', 'SET:'.$shopInfo->branchId);


	$optionMonth = Array();
	foreach ($monthDbs->items as $rs) {
		$optionMonth[$rs->year] = 'พ.ศ.'.($rs->year+543);
	}
	foreach ($monthDbs->items as $rs) {
		$optionMonth[$rs->month] = sg_date($rs->month.'-01', 'ดดด ปปปป');
	}

	mydb::where('j.`shopid` IN ( :shopid )', ':shopid', 'SET:'.$shopInfo->branchId);
	if (strlen($getMonth) == 4) {
		mydb::where('YEAR(j.`rcvdate`) = :year', ':year', $getMonth);
	} else if (strlen($getMonth) == 7) {
		mydb::where('DATE_FORMAT(j.`rcvdate`, "%Y-%m") = :month', ':month', $getMonth);
	}

	$stmt = 'SELECT
		COUNT(*) `totalJob`
		, COUNT(`insurerid`) `totalInsu`
		, COUNT(IF(j.`insurerid`>0,NULL,1)) `totalCash`
		, COUNT(IF(`hasReply`>0,1,NULL)) `totalReply`
		, COUNT(IF(`hasReply`>0,1,NULL)) `totalReply`
		, COUNT(IF(`isjobclosed` != "Yes",1,NULL)) `totalNotClose`
		, COUNT(IF(`iscarreturned` != "Yes",1,NULL)) `totalNotReturn`
		, COUNT(IF(`isrecieved` != "Yes",1,NULL)) `totalNotRecieved`
		, SUM(`rcvmoneyamt`) `totalRcvMoney`
		, SUM(`wage`) `totalWage`
		, SUM(`replywage`) `totalReplyWage`
		, SUM(`part`) `totalPart`
		, SUM(`replypart`) `totalReplyPart`
		FROM 
		(
			SELECT
			j.`tpid`, j.`insurerid`
			, j.`isjobclosed`
			, j.`iscarreturned`
			, j.`isrecieved`
			, j.`rcvmoneyamt`
			, COUNT(IF(q.`replyprice` > 0, 1, NULL)) `hasReply`
			, SUM(q.`replywage`) `replywage`
			, SUM(q.`replypart`) `replypart`
			, (SELECT SUM(`totalsale`) FROM %garage_jobtr% jtr LEFT JOIN %garage_repaircode% rc USING(`repairid`) WHERE `tpid` = j.`tpid` AND rc.`repairtype` = 1) `wage` 
			, (SELECT SUM(`totalsale`) FROM %garage_jobtr% jtr LEFT JOIN %garage_repaircode% rc USING(`repairid`) WHERE `tpid` = j.`tpid` AND rc.`repairtype` = 2) `part` 
			FROM %garage_job% j
				LEFT JOIN %garage_qt% q USING(`tpid`)
			%WHERE%
			GROUP BY `tpid`
		) j
		LIMIT 1
		';

	$jobSummary = mydb::select($stmt);



	$ret .= '<div class="garage-report-home -sg-flex">';

	$ret .= '<div class="-menu">';
	$ret .= '<h3>ภาพรวมใบสั่งซ่อม</h3>';

	$form = new Form(NULL, url('garage/report'));
	$form->addField(
		'mm',
		array(
			'type' => 'select',
			'options' => array('' => 'ทั้งหมด')+$optionMonth,
			'value' => $getMonth,
			'attr' => array('onChange'=>'this.form.submit()'),
		)
	);

	$ret .= $form->build();



	$ret .= '<h3>ใบสั่งซ่อม</h3>';

	$ui = new Ui();
	$ui->add('<span class="-desc">จำนวนรถ (เงินสด)</span><span class="-value">'.$jobSummary->totalCash.' คัน</span>');
	//$ui->add('<span class="-desc">จำนวนรถ (เครดิต)</span><span class="-value">0 คัน</span>');
	$ui->add('<span class="-desc">จำนวนรถ (ใช้ประกัน)</span><span class="-value">'.$jobSummary->totalInsu.' คัน</span>');
	$ui->add('<span class="-desc">จำนวนรถ (ยอดรวม)</span><span class="-value">'.$jobSummary->totalJob.' คัน</span>');
	$ui->add('<span class="-desc">จำนวนรถ (อนุมัติ)</span><span class="-value">'.$jobSummary->totalReply.' คัน</span>');
	$ui->add('<span class="-desc">จำนวนรถ (คงเหลือเก็บเงิน)</span><span class="-value">'.$jobSummary->totalNotRecieved.' คัน</span>');
	$ui->add('<span class="-desc">จำนวนรถ (ในอู่)</span><span class="-value">'.$jobSummary->totalNotReturn.' คัน</span>');
	$ui->add('<span class="-desc">จำนวนรถ (ยังไม่ปิดจ็อบ)</span><span class="-value">'.$jobSummary->totalNotClose.' คัน</span>');

	$ret .= $ui->build();

	$ret .= '<h3>รายได้</h3>';

	$ui = new Ui();
	$ui->add('<span class="-desc">รายได้รวม (เสนอ)</span><span class="-value">'.number_format($jobSummary->totalWage + $jobSummary->totalPart,2).' บาท</span>');
	$ui->add('<span class="-desc">รายได้รวม (อนุมัติ)</span><span class="-value">'.number_format($jobSummary->totalReplyWage + $jobSummary->totalReplyPart,2).' บาท</span>');
	$ui->add('<span class="-desc">รายได้รวม (เก็บเงินแล้ว)</span><span class="-value">'.number_format($jobSummary->totalRcvMoney,2).' บาท</span>');
	$ui->add('<span class="-desc">รายได้รวม (คงเหลือเก็บเงิน)</span><span class="-value">'.number_format($jobSummary->totalReplyWage +$jobSummary->totalReplyPart - $jobSummary->totalRcvMoney,2).' บาท</span>');

	$ret .= $ui->build();

	$ret .= '<h3>ค่าแรงและค่าอะไหล่</h3>';

	$ui = new Ui();
	$ui->add('<span class="-desc">ค่าแรง (เสนอ)</span><span class="-value">'.number_format($jobSummary->totalWage,2).' บาท</span>');
	$ui->add('<span class="-desc">ค่าแรง (อนุมัติ)</span><span class="-value">'.number_format($jobSummary->totalReplyWage,2).' บาท</span>');
	$ui->add('<span class="-desc">ค่าอะไหล่ (เสนอ)</span><span class="-value">'.number_format($jobSummary->totalPart,2).' บาท</span>');
	$ui->add('<span class="-desc">ค่าอะไหล่ (อนุมัติ)</span><span class="-value">'.number_format($jobSummary->totalReplyPart,2).' บาท</span>');

	$ret .= $ui->build();

	$ret .= '</div><!-- -->';

	$ret .= '<div class="-info -fill">';

	//$ret .= print_o(post(),'post()');
	//$ret .= print_o($jobSummary, '$jobSummary');

	//$ret .= '<div>GRAPH ใบสั่งซ่อม</div>';
	//$ret .= '<div>GRAPH รายได้ เงินสด/เครดิต/ใช้ประกัน</div>';
	//$ret .= '<div>GRAPH รายได้ ค่าแรง/ค่าอะไหล่</div>';

	$graphYear = new Table();
	$graphYear->rows[] = array(
		'string:Year'=>'เงินสด',
		'number:Budget'=>$jobSummary->totalCash
	);
	$graphYear->rows[] = array(
		'string:Year'=>'ใช้ประกัน',
		'number:Budget'=>$jobSummary->totalInsu
	);

	$ret.='<div class="present"><h3>ใบสั่งซ่อม เงินสด/ใช้ประกัน</h3><div id="chart-cash" class="sg-chart -chart-job" data-chart-type="pie">'._NL.$graphYear->build().'</div></div>'._NL;

	$graphYear = new Table();
	$graphYear->rows[] = array(
		'string:Year'=>'ในอู่',
		'number:Budget'=>$jobSummary->totalNotReturn
	);
	$graphYear->rows[] = array(
		'string:Year'=>'คืนรถ',
		'number:Budget'=>$jobSummary->totalJob - $jobSummary->totalNotReturn
	);

	$ret.='<div class="present"><h3>ใบสั่งซ่อม ในอู่/คืนรถ</h3><div id="chart-return" class="sg-chart -chart-job" data-chart-type="pie">'._NL.$graphYear->build().'</div></div>'._NL;

	$graphYear = new Table();
	$graphYear->rows[] = array(
		'string:Year'=>'เก็บเงินแล้ว',
		'number:Budget'=>$jobSummary->totalRcvMoney
	);
	$graphYear->rows[] = array(
		'string:Year'=>'คงเหลือเก็บเงิน',
		'number:Budget'=>$jobSummary->totalReplyWage +$jobSummary->totalReplyPart - $jobSummary->totalRcvMoney
	);

	$ret.='<div class="present"><h3>ใบสั่งซ่อม เก็บเงินแล้ว/คงเหลือเก็บเงิน</h3><div id="chart-rcvmoney" class="sg-chart -chart-job" data-chart-type="pie">'._NL.$graphYear->build().'</div></div>'._NL;

	$graphYear = new Table();
	$graphYear->rows[] = array(
		'string:Year'=>'ค่าแรง',
		'number:Budget'=>$jobSummary->totalReplyWage
	);
	$graphYear->rows[] = array(
		'string:Year'=>'ค่าอะไหล่',
		'number:Budget'=>$jobSummary->totalReplyPart
	);

	$ret.='<div class="present"><h3>ค่าแรง/ค่าอะไหล่อนุมัติ ค่าแรง/ค่าอะไหล่</h3><div id="chart-wage" class="sg-chart -chart-job" data-chart-type="pie">'._NL.$graphYear->build().'</div></div>'._NL;


	$ret.='</div>';



	//$ret .= print_o($shopInfo);
	$ret .= '</div>';

	$ret .= '</div>';

	head('googlegraph','<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');
	head('<style type="text/css">
		.garage-report-home .-info {overflow: hidden;}}
		.present {width: 100%;}
		.present h3 {font-size: 1.1em;}
		.sg-chart {width: 100%;}
		</style>');

	return $ret;
}
?>