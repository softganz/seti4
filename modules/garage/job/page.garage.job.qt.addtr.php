<?php
/**
* Add Job Transaction to Quotation
* Created 2019-10-14
* Modify  2019-10-14
*
* @param Object $self
* @param Object $jobInfo
* @return String
*/

$debug = true;

function garage_job_qt_addtr($self, $jobInfo, $tranId) {
	if (!($jobId = $jobInfo->jobId)) return message('error', 'PROCESS ERROR');

	$shopInfo = $jobInfo->shopInfo;

	if (!$jobId) return message('error', 'PROCESS ERROR');

	$ret = '<header class="header">'._HEADER_BACK.'<h3>เพิ่มรายการซ่อม</h3></header>';

	$ret .= '<form class="sg-form" method="post" action="'.url('garage/job/'.$jobId.'/info/qt.tr.save/'.$tranId).'" data-rel="notify" data-done="close | load:#main:'.url('garage/job/'.$jobId.'/qt/'.$tranId).'">';

	$tables = new Table();
	$tables->addClass('-garage-job-tran'.($action?' -'.$action:''));
	$tables->thead=array('center -checkbox'=>'','รายละเอียดการซ่อม','center'=>'','money -price'=>'ราคาอู่เสนอ');

	$tables->rows[]=array('<th><!-- <input type="checkbox" /> --></th>','<th>ค่าแรง</th>','<th>แผล</th>','<th></th>');
	if ($jobInfo->command) {
		foreach ($jobInfo->command as $rs) {
			if ($tranId!=$rs->qtid) {
				$tables->rows[]=array(
					'<input type="checkbox" name="cmd[]" value="'.$rs->jobtrid.'" />',
					$rs->repairname,
					$rs->damagecode,
					number_format($rs->totalsale,2),
				);
				$totalservice+=$rs->totalsale;
			}
		}
		$tables->rows[]=array('','รวมค่าแรง','',number_format(0,2),'config'=>array('class'=>'subfooter'));
	} else {
		$tables->rows[]=array('<td colspan="4" align="center">ไม่มีรายการ</td>');
	}

	$tables->rows[]=array('<td colspan="4"></td>','config'=>array('class'=>'noprint'));
	$tables->rows[]=array('<th><!-- <input type="checkbox" /> --></th>','<th>ค่าอะไหล่</th>','<th>จำนวน</th>','<th></th>');
	if ($jobInfo->part) {
		foreach ($jobInfo->part as $rs) {
			if ($tranId!=$rs->qtid) {
				$tables->rows[]=array(
					'<input type="checkbox" name="part[]" value="'.$rs->jobtrid.'" />',
					$rs->repairname,
					number_format($rs->qty),
					number_format($rs->totalsale,2),
				);
				$totalpart+=$rs->totalsale;
			}
		}
		$tables->rows[]=array('','รวมค่าอะไหล่','',number_format(0,2),'config'=>array('class'=>'subfooter'));
	} else {
		$tables->rows[]=array('<td colspan="4" align="center">ไม่มีรายการ</td>');
	}
	$tables->rows[]=array('<td colspan="4"></td>','config'=>array('class'=>'noprint'));
	$tables->rows[]=array('','รวมทั้งสิ้น','',number_format(0,2),'config'=>array('class'=>'subfooter'));

	$ret.=$tables->build();
	$ret .= '<div class="form-item -sg-text-right"><a class="sg-action btn -link -cancel" data-rel="back"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a> <button class="btn -primary" type="submit"><i class="icon -save -white"></i><span>{tr:SAVE}</span></button></div>';
	$ret.='</form>';

	//$ret.=print_o($jobInfo,'$jobInfo');
	return $ret;
}
?>