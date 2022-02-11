<?php
/**
* Module Method
* Created 2019-10-01
* Modify  2019-10-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function garage_job_qt_tran($self, $jobInfo, $trid = NULL) {
	$tpid = $jobInfo->tpid;
	$action = post('action');

	$ret = '';


	$tables = new Table();
	$tables->addClass('-garage-job-tran'.($action?' -'.$action:''));
	$tables->thead = array(
		'รายละเอียดการซ่อม',
		'center -damage'=>'',
		'money -price'=>'ราคาอู่เสนอ',
		'money -total'=>'ราคาประกันอนุมัติ'
	);

	$tables->rows[] = array('<th class="-sg-text-left">ค่าแรง</th><th>แผล</th><th></th><th></th>');
	if ($jobInfo->command) {
		foreach ($jobInfo->command as $rs) {
			if (empty($trid) || $trid == $rs->qtid) {
				$tables->rows[] = array(
					$rs->repairname,
					'<span>'.$rs->damagecode.'</span>',
					number_format($rs->totalsale,2),
					'',
				);
				$totalservice += $rs->totalsale;
			}
		}
		$tables->rows[] = array(
			'รวมค่าแรง',
			'',
			number_format($totalservice,2),
			$action == 'reply'?'<input id="replywage" class="form-text -fill -money" type="text" name="reply[replywage]" value="'.$jobInfo->qt[$trid]->replywage.'" placeholder="0.00" />':number_format($jobInfo->qt[$trid]->replywage,2),
			'config' => '{class: "subfooter"}',
		);
	} else {
		$tables->rows[] = array('<td colspan="4" align="center">ไม่มีรายการ</td>');
	}

	$tables->rows[] = array('<td colspan="4"></td>', 'config' => '{class: "-no-print"}');
	$tables->rows[] = array('<th class="-sg-text-left">ค่าอะไหล่</th>','<th>จำนวน</th>','<th></th>','<th></th>');
	if ($jobInfo->part) {
		foreach ($jobInfo->part as $rs) {
			if (empty($trid) || $trid == $rs->qtid) {
				$tables->rows[] = array(
					$rs->repairname,
					number_format($rs->qty),
					number_format($rs->totalsale,2),
					'',
				);
				$totalpart += $rs->totalsale;
			}
		}
		$tables->rows[] = array(
			'รวมค่าอะไหล่',
			'',
			number_format($totalpart,2),
			$action == 'reply'?'<input id="replypart" class="form-text -fill -money" type="text" name="reply[replypart]" value="'.$jobInfo->qt[$trid]->replypart.'" placeholder="0.00" />':number_format($jobInfo->qt[$trid]->replypart,2),
			'config' => '{class: "subfooter"}',
		);
	} else {
		$tables->rows[] = array('<td colspan="4" align="center">ไม่มีรายการ</td>');
	}
	$tables->rows[] = array('<td colspan="4"></td>','config' => '{class: "-no-print"}');

	$tables->rows[] = array(
		'ค่า Accept',
		'',
		'',
		$action == 'reply'?'<input id="replyaccept" class="form-text -fill -money" type="text" name="reply[replyaccept]" value="'.$jobInfo->qt[$trid]->replyaccept.'" placeholder="0.00" />':number_format($jobInfo->qt[$trid]->replyaccept,2),
		'config' => '{class: "subfooter"}',
	);
	$tables->rows[] = array('<td colspan="4"></td>','config' => '{class: "-no-print"}');

	$tables->rows[] = array(
		'รวมทั้งสิ้น',
		'',
		number_format($totalservice+$totalpart,2),
		'<span id="replyprice">'.number_format($jobInfo->qt[$trid]->replyprice,2).'</span>',
		'config' => '{class: "subfooter"}',
	);

	if ($action == 'reply') {
		$ret .= '<form class="sg-form" method="post" action="'.url('garage/job/'.$tpid.'/info/qt.reply/'.$trid).'" data-rel="notify" data-done="load:#garage-job-qt-trans">';
	}

	$ret .= $tables->build();
	if ($action == 'reply') {
		$ret .= '<div class="form-item -sg-text-right"><button id="save" class="btn -primary" type="submit"><i class="icon -save -white"></i><span>บันทึกราคาประกันอนุมัติ</span></button></div>';
		$ret .= '</form>';
		$ret .= '<script type="text/javascript">
		$("#replyprice").focus();
		$("html, body").animate({scrollTop:$("#save").offset().top+"px"});
		</script>';
		//$ret.=print_o($_POST,'$_POST');
	}

	//$ret.=print_o($jobInfo,'$jobInfo');
	return $ret;
}
?>