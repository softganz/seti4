<?php

/**
 * Monthly Result
 *
 */
function project_report_monthprocess($self) {
	R::View('project.toolbar', $self, 'รายงานผลการดำเนินงานโครงการตามแผนปฏิบัติการพัฒนาจังหวัดชายแดนใต้ ประจำปีงบประมาณ ', 'report');

	$year=SG\getFirst(post('y'));
	$province=post('p');

	$stmt='SELECT
						p.`tpid`, t.`title`
						, o.`sector`, p.`budget`, p.`otherbudget`, p.`totaltarget`, p.`area`
						, COUNT(`formid`) `totalReport`
						, SUM(tr.`num1`) paid
						, GROUP_CONCAT(tr.`num5` order by tr.`date1`) percentdone
						, GROUP_CONCAT(tr.`date1` order by tr.`date1`) lastreportdate
						, `text1` `msg`, `text2` `problem`, `text3` `recommendation`
					FROM %project% p
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %db_org% o USING(`orgid`)
						LEFT JOIN %project_tr% tr ON tr.`tpid`=p.`tpid` AND tr.`formid`="report"
					WHERE o.`sector`>1
					GROUP BY p.`tpid`
					ORDER BY p.`tpid` ASC, tr.`date1` ASC';
	$dbs=mydb::select($stmt);

	$tables = new Table();
	$tables->thead=array('no'=>'ลำดับ','แผนงาน ผลผลิต กิจกรรม/รายการ กิจกรรม-โครงการย่อย', 'money budget'=>'งบประมาณตามแผน(บาท)', 'money otherbudget'=>'งบประมาณนอกแผน(บาท)', 'money totalbudget'=>'รวมงบประมาณ(บาท)', 'amt projects'=>'จำนวนเป้าหมาย(คน)','พื้นที่ดำเนินการ','money paid'=>'จำนวนเงินเบิกจ่าย(บาท)','amt percentpaid'=>'ร้อยละ','amt percentdone'=>'ผลการดำเนินงาน(ร้อยละ)','date reportdate'=>'เดือนที่รายงานผลการดำเนินงานล่าสุด','amt totalreport'=>'จำนวนรายงาน(ครั้ง)');
	foreach ($dbs->items as $rs) {
		$projectBudget=$rs->budget+$rs->otherbudget;
		$percentdone=array_pop(explode(',', $rs->percentdone));
		$lastreportdate=array_pop(explode(',', $rs->lastreportdate));
		$tables->rows[]=array(
			++$no,
			'<a href="'.url('paper/'.$rs->tpid).'">'.$rs->title.'</a>',
			number_format($rs->budget,2),
			number_format($rs->otherbudget,2),
			number_format(($projectBudget),2),
			number_format($rs->totaltarget,0),
			$rs->area,
			number_format($rs->paid,2),
			$projectBudget?number_format($rs->paid*100/$projectBudget,2).'%' : '-',
			$percentdone?number_format($percentdone).'%':'',
			$lastreportdate?sg_date($lastreportdate,'ดดด ปปปป') : '',
			$rs->totalReport?$rs->totalReport:'-',
		);

		$totalProjects+=$rs->amt;
		$totalBudgets+=$rs->budget;
		$totalOtherBudgets+=$rs->otherbudget;
		$totalTarget+=$rs->totaltarget;
		$totalPaid+=$rs->paid;
		$totalReport+=$rs->totalReport;
	}
	$tables->rows[]=array(
		'',
		'<strong>รวม</strong>',
		'<strong>'.number_format($totalBudgets,2).'</strong>',
		'<strong>'.number_format($totalOtherBudgets,2).'</strong>',
		'<strong>'.number_format(($totalBudgets+$totalOtherBudgets),2).'</strong>',
		'<strong>'.number_format($totalTarget).'</strong>',
		'',
		'<strong>'.number_format($totalPaid,2).'</strong>',
		'<strong>'.number_format($totalPaid*100/($totalBudgets+$totalOtherBudgets),2).'%'.'</strong>',
		'',
		'',
		'<strong>'.$totalReport.'</strong>',
	);

	$ret .= $tables->build();
	$ret.='<p>หมายเหตุ : งบประมาณ : ล้านบาท ทศนิยม 4 ตำแหน่ง</p>';

	//$tables->thead=array('no'=>'ลำดับ','แผนงาน ผลผลติ กิจกรรม/รายการ กิจกรรม-โครงการย่อย', 'money budget'=>'งบประมาณตามแผน', 'money otherbudget'=>'งบประมาณนอกแผน', 'money totalbudget'=>'รวมงบประมาณ', 'amt projects'=>'จำนวนเป้าหมาย(คน)','พื้นที่ดำเนินการ','money paid'=>'จำนวนเงินเบิกจ่าย','amt percentpaid'=>'ร้อยละ','amt percentdone'=>'ผลการดำเนินงาน(ร้อยละ)','ผลการดำเนินงาน/ปัญหา อุปสรรค/ข้อเสนอแนะ');


	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>