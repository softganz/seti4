<?php
/**
* Garage :: View Job Information
* Created 2019-02-28
* Modify  2020-07-23
*
* @param Object $self
* @param Object $jobInfo
* @return String
*/

$debug = true;

function garage_job_view($self, $jobInfo, $action = NULL, $tranId = NULL) {
	//debugMsg($jobInfo, '$jobInfo');
	if (!($jobId = $jobInfo->jobId)) return message('error', 'PROCESS ERROR');

	$shopInfo = $jobInfo->shopInfo;

	//R::Model('garage.verify',$self, $shopInfo,'JOB');

	if (empty($jobInfo)) return message('error','ไม่มีข้อมูลตามที่ระบุ');

	new Toolbar($self,($action == 'edit'?'แก้ไข':'ใบสั่งซ่อม').' - '.$jobInfo->plate.'@'.$jobInfo->shopShortName,'job',$jobInfo);
	page_class('-job-type-'.$jobInfo->shopShortName);

	$ret .= '<div class="sg-view garage-job-view">';
	$ret .= R::View('garage.job.statusbar', $self, $jobInfo);
	$ret .= '<div id="garage-job-info" class="-side">';
	$ret .= '<div class="-menu">'.__garage_job_view_menu($shopInfo, $jobInfo).'</div>';
	$ret .= '<div class="-info">'.R::Page('garage.job.detail', NULL, $jobInfo).'</div>';
	$ret .= '</div>';
	$ret .= '<div id="garage-job-detail" class="-detail" data-url="'.url('garage/job/'.$jobId.'/tran').'">'.R::Page('garage.job.tran', $self,$jobInfo).'</div>';
	$ret .= '</div><!-- garage-job-view -->';

	//$ret.=print_o($shopInfo,'$shopInfo');
	//$ret .= print_o($jobInfo,'$jobInfo');

	return $ret;
}

function __garage_job_view_menu($shopInfo,$jobInfo) {
	$isAdmin = user_access('administrator garages');
	$isMoney = in_array($shopInfo->iam, array('ADMIN', 'FINANCE', 'MANAGER'));

	$ret.='<div class="header -no-print"><h3>เมนู</h3></div>';

	$ui=new Ui(NULL,'ui-menu -ver');

	if ($jobInfo->is->editable && !$jobInfo->is->closed) {
		$ui->add('<a class="sg-action" href="'.url('garage/job/'.$jobInfo->tpid.'/detail.form/edit').'" data-rel="#garage-job-detail"><i class="icon -material">edit</i><span>รายละเอียดรถ</span></a>');
		$ui->add('<a class="sg-action" href="'.url('garage/job/'.$jobInfo->tpid.'/tran').'" data-rel="#garage-job-detail"><i class="icon -material">grading</i><span>รายการสั่งซ่อม</span></a>');
		$ui->add('<a class="sg-action" href="'.url('garage/job/'.$jobInfo->tpid.'/carindate').'" data-rel="#garage-job-detail"><i class="icon -material -'.($jobInfo->carindate ? 'green' : 'gray').' ">assignment_turned_in</i><span>วันรับรถเข้าซ่อม</span></a>');
		$ui->add('<a class="sg-action" href="'.url('garage/job/'.$jobInfo->tpid.'/appointment').'" data-rel="#garage-job-detail"><i class="icon -material -'.($jobInfo->datetoreturn ? 'green' : 'gray').'">date_range</i><span>บันทึกวันนัดรับรถ</span></a>');
		$ui->add('<a class="sg-action" href="'.url('garage/job/'.$jobInfo->tpid.'/returnform').'" data-rel="#garage-job-detail"><i class="icon -material">print</i><span>พิมพ์ใบตรวจและรับรถ</span></a>');
		$ui->add('<a class="sg-action" href="'.url('garage/job/'.$jobInfo->tpid.'/return').'" data-rel="#garage-job-detail"><i class="icon -material -'.($jobInfo->returndate ? 'green' : 'gray').'">reply</i><span>บันทึกการคืนรถ</span></a>');
		$ui->add('<a class="sg-action" href="'.url('garage/job/'.$jobInfo->tpid.'/rcvmoney').'" data-rel="#garage-job-detail"><i class="icon -material">attach_money</i><span>บันทึกการรับเงิน</span></a>');
	}

	if ($isAdmin || $isMoney) {
		$ui->add('<a class="sg-action" href="'.url('garage/job/'.$jobInfo->tpid.'/rcvproved').'" data-rel="#garage-job-detail"><i class="icon -material -'.(strtoupper($jobInfo->isrcvproved) == 'YES' ? 'green' : 'gray').'">money</i><span>ยืนยันการรับเงิน</span></a>');
	}
	$ui->add('<sep>');

	$ui->add('<a class="sg-action" href="'.url('garage/job/'.$jobInfo->tpid.'/summary').'" data-rel="#garage-job-detail"><i class="icon -material">find_in_page</i><span>ภาพรวม</span></a>');
	$ui->add('<a class="sg-action" href="'.url('garage/job/'.$jobInfo->tpid.'/cost').'" data-rel="#garage-job-detail"><i class="icon -material">request_quote</i><span>รายการต้นทุน</span></a>');
	
	if ($jobInfo->is->editable) {
		$ui->add('<a class="sg-action" href="'.url('garage/job/'.$jobInfo->tpid.'/close').'" xdata-callback="'.url('garage/job/'.$jobInfo->tpid).'" data-title="เปิด/ปิดใบสั่งซ่อม" data-confirm="ต้องการ'.($jobInfo->isjobclosed=='Yes'?'เปิด':'ปิด').'ใบสั่งซ่อม กรุณายืนยัน?"><i class="icon -material">'.($jobInfo->isjobclosed=='Yes'?'lock':'lock_open').'</i><span>'.($jobInfo->isjobclosed=='Yes'?'เปิด':'ปิด').'ใบสั่งซ่อม</span></a>');
	}

	$ret .= '<nav class="nav -ver -no-print">'.$ui->build().'</nav>';

	return $ret;
}

?>