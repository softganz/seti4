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

function garage_job_detail($self, $jobInfo) {
	$ret.='<div class="-header"><h3>รายละเอียดรถ</h3></div>';

	$ret.='<div class="card-item"><span class="label">เลขใบสั่งซ่อม</span><span class="value">'.$jobInfo->jobno.'</span></div>'._NL;
	$ret.='<div class="card-item"><span class="label">ทะเบียน</span><span class="value">'.$jobInfo->plate.'</span></div>'._NL;
	$ret.='<div class="card-item"><span class="label">ผู้รับรถ</span><span class="value">'.$jobInfo->rcvbyName.'</span></div>'._NL;
	$ret.='<div class="card-item"><span class="label">วันที่เปิดใบสั่งซ่อม</span><span class="value">'.sg_date($jobInfo->rcvdate,'d/m/ปปปป').'</span></div>'._NL;
	$ret.='<div class="card-item"><span class="label">ยี่ห้อ</span><span class="value">'.$jobInfo->brandid.'</span></div>'._NL;
	$ret.='<div class="card-item"><span class="label">รุ่น</span><span class="value">'.$jobInfo->modelname.'</span></div>'._NL;
	$ret.='<div class="card-item"><span class="label">สี</span><span class="value">'.$jobInfo->colorname.'&nbsp;</span></div>'._NL;
	$ret.='<div class="card-item"><span class="label">เลขรถรอ</span><span class="value">'.$jobInfo->carwaitno.'&nbsp;</span></div>'._NL;
	$ret.='<div class="card-item"><span class="label">เลขรถเข้า</span><span class="value">'.$jobInfo->carinno.'&nbsp;</span></div>'._NL;
	$ret.='<div class="card-item"><span class="label">ชื่อลูกค้า</span><span class="value">'.$jobInfo->customername.'&nbsp;</span></div>'._NL;
	$ret.='<div class="card-item"><span class="label">โทรศัพท์</span><span class="value">'.$jobInfo->customerphone.'&nbsp;</span></div>'._NL;
	$ret.='<div class="card-item"><span class="label">วันที่นัดรับรถ</span><span class="value">'.($jobInfo->datetoreturn?sg_date($jobInfo->datetoreturn,'d/m/Y'):'').'&nbsp;</span></div>'._NL;
	$ret.='<div class="card-item"><span class="label">เวลานัดรับรถ</span><span class="value">'.substr($jobInfo->timetoreturn,0,5).'&nbsp;</span></div>'._NL;
	$ret.='<div class="card-item"><span class="label">หมายเลขตัวถัง</span><span class="value">'.$jobInfo->bodyno.'&nbsp;</span></div>'._NL;
	$ret.='<div class="card-item"><span class="label">หมายเลขเครื่อง</span><span class="value">'.$jobInfo->enginno.'&nbsp;</span></div>'._NL;
	$ret.='<div class="card-item"><span class="label">ประกัน</span><span class="value">'.$jobInfo->insurername.'&nbsp;</span></div>'._NL;
	$ret.='<div class="card-item"><span class="label">เลขรับแจ้งประกันภัย</span><span class="value">'.$jobInfo->insuclaimcode.'&nbsp;</span></div>'._NL;
	//$ret.=print_o($jobInfo);
	return $ret;
}
?>