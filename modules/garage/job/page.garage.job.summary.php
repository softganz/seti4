<?php
/**
* Module : Module Method
* Created 2020-08-01
* Modify  2020-08-01
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage module/{$Id}/method
*/

$debug = true;

function garage_job_summary($self, $jobInfo) {
	$jobId = $jobInfo->tpid;
	$ret .= R::Page('garage.job.detail', NULL, $jobInfo);
	$ret .= '<div class="card-item"><span class="label">รวมค่าอะไหล่</span><span class="value">'.number_format($jobInfo->totalservice,2).' บาท</span></div>'._NL;
	$ret .= '<div class="card-item"><span class="label">รวมค่าแรง</span><span class="value">'.number_format($jobInfo->totalpart,2).' บาท</span></div>'._NL;
	$ret .= '<div class="card-item"><span class="label">รวมค่าซ่อม</span><span class="value">'.number_format($jobInfo->totalservice+$jobInfo->totalpart,2).' บาท</span></div>'._NL;
	$ret .= '<div class="card-item"><span class="label">รวมต้นทุน</span><span class="value">'.number_format(abs($jobInfo->totalCost),2).' บาท</span></div>'._NL;

	$ret .= '<div class="card-item"><span class="label">วันที่คืนรถ</span><span class="value">'.($jobInfo->returndate?sg_date($jobInfo->returndate,'d/m/ปปปป'):'').'</span></div>'._NL;

	$ret .= '<div class="card-item"><span class="label">ใบเสนอราคา</span><span class="value">';
	foreach ($jobInfo->qt as $item) $ret.='<a class="btn" href="'.url('garage/job/'.$jobId.'/qt/'.$item->qtid).'" target="_blank">'.$item->qtno.'</a> ';
	$ret .= '</span></div>'._NL;

	$ret .= '<div class="card-item"><span class="label">ใบวางบิล</span><span class="value">';

	foreach ($jobInfo->billing as $k=>$v) {
		$ret.='<a class="btn" href="'.url('garage/billing/view/'.$k).'" target="_blank">'.$v.'</a> ';
	}

	$ret .= '</span></div>'._NL;

	$ret .= '<div class="card-item"><span class="label">ใบเสร็จรับเงิน</span><span class="value">';

	foreach ($jobInfo->recieve as $k=>$v) {
		$ret.='<a class="btn" href="'.url('garage/recieve/'.$k).'" target="_blank">'.$v.'</a> ';
	}

	$ret .= '</span></div>'._NL;

	$ret .= R::Page('garage.job.tran', NULL, $jobInfo);

	//$ret.=print_o($jobInfo);
	$ret .= '<style type="text/css">
	.garage-job.-tran .label {display: block; width: 160px; float:left;}
	.garage-job.-tran .value {min-height:1.2em; margin-left: 160px;}
	.item.-garage-job-tran .-input {display: none;}
	.item.-garage-job-tran thead {display: none;}
	</style>';
	$ret.='<script type="text/javascript">$("#repaircode").focus();</script>';
	return $ret;
}
?>