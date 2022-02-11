<?php
/**
* Garage :: Car In Detail
* Created 2020-07-25
* Modify  2020-07-25
*
* @param Object $self
* @param Object $jobInfo
* @return String
*/

$debug = true;

function garage_job_in_detail($self, $jobInfo) {
	$shopInfo = R::Model('garage.get.shop');
	if (!($jobId = $jobInfo->tpid)) return message('error', 'NO JOB');

	$shopId = ($shopInfo = $jobInfo->shopInfo) ? $shopInfo->shopId : NULL;

	new Toolbar($self,'ข้อมูลทั่วไป - '.$jobInfo->plate,'in', $jobInfo);

	$ret = '';

	$navUi = new Ui(NULL, 'ui-menu');
	$navUi->addConfig('nav', '{class: "nav"}');
	$navUi->add('<a>รายละเอียดรถยนต์</a>');
	$navUi->add('<a>ประกัน/ผู้ชำระเงิน</a>');
	$navUi->add('<a>เจ้าของรถยนต์</a>');
	$navUi->add('<a>รับรถเข้าซ่อม</a>');
	$navUi->add('<a>ส่งมอบรถ</a>');

	//$ret .= R::Page('garage.job.view',NULL, $jobInfo, 'edit');

	$ret .= '<div class="garage-job-in-detail sg-view -co-2">';

	$ret .= '<div class="-sg-view">'
		. R::Page('garage.job.detail.form',NULL, $jobInfo, 'edit')
		. '</div>';

	$ret .= '<div class="-sg-view">'
		. $navUi->build()
		. '</div>';

	$ret .= '</div>';

	//$ret .= print_o($jobInfo,'$jobInfo');
	//$ret .= print_o($shopInfo,'$shopInfo');
	return $ret;
}
?>