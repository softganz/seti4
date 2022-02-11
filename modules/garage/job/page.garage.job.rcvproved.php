<?php
function garage_job_rcvproved($self, $jobInfo, $action = NULL) {
	if (!($jobId = $jobInfo->jobId)) return message('error', 'PROCESS ERROR');

	$shopInfo = $jobInfo->shopInfo;

	$isEdit = user_access('administrator garages') || in_array($shopInfo->iam, array('ADMIN', 'FINANCE', 'MANAGER'));

	if (!$isEdit)
		return message('error', 'Access Denied');

	if (empty($jobInfo))
		return message('error', 'ไม่มีข้อมูลตามที่ระบุ');

	new Toolbar( $self, 'บันทึกยืนยันการรับเงิน - '.$jobInfo->plate, 'job', $jobInfo);

	$ret .= '<h3>บันทึกยืนยันการรับเงิน</h3>';
	$form = new Form('', url('garage/job/'.$jobInfo->tpid.'/info/rcvproved.save'), '', 'sg-form garage-job-rcvmoney');
	$form->addData('rel', 'notify');
	$form->addData('done', 'load:#garage-job-detail');

	$form->addField('replyprice',
		array(
			'type' => 'text',
			'label' => 'ราคาประกันอนุมัติ (บาท)',
			'name' => 'replyprice',
			'readonly' => true,
			'class' => '-money -fill',
			'value' => number_format($jobInfo->replyprice, 2),
		)
	);

	$form->addField('date',
		array(
			'type' => 'text',
			'label' => 'วันที่รับเงิน',
			'name' => 'date',
			'class' => '-fill',
			'readonly' => true,
			'value' => $jobInfo->rcvmoneydate ? sg_date($jobInfo->rcvmoneydate, 'd/m/Y') : '',
		)
	);

	$form->addField('amount',
		array(
			'type' => 'text',
			'label' => 'จำนวนเงินที่รับ (บาท)',
			'name' => 'amount',
			'class' => '-money -fill',
			'readonly' => true,
			'placeholder' => '0.00',
			'value' => number_format($jobInfo->rcvmoneyamt, 2),
		)
	);

	$form->addField('isrcvproved',
		array(
			'type' => 'checkbox',
			'name' => 'isrcvproved',
			'options' => array('Yes' => 'ฝ่ายการเงินได้รับเงินครบตามจำนวนเรียบร้อยแล้ว'),
			'value' => $jobInfo->isrcvproved,
		)
	);

	$form->addField('closejob',
		array(
			'type'=>'checkbox',
			'name'=>'closejob',
			'options'=>array('Yes'=>'ปิดใบสั่งซ่อม'),
			'value'=>$jobInfo->isjobclosed,
		)
	);

	$form->addField('save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -save -white"></i><span>บันทึกยืนยันการรับเงิน</span>'
		)
	);

	$ret .= $form->build();


	$ret .= '<style type="text/css">
	.garage-job-rcvmoney {width:300px;}
	.garage-job-rcvmoney .btn.-primary {width:100%;}
	</style>';
	return $ret;
}

?>