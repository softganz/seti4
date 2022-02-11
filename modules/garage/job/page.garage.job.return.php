<?php
function garage_job_return($self,$jobInfo,$action=NULL) {
	if (!($jobId = $jobInfo->jobId)) return message('error', 'PROCESS ERROR');

	$shopInfo = $jobInfo->shopInfo;

	if (empty($jobInfo)) return message('error','ไม่มีข้อมูลตามที่ระบุ');

	new Toolbar($self,'บันทึกการคืนรถ - '.$jobInfo->plate,'job',$jobInfo);

	$ret = '<header class="header"><h3>บันทึกการคืนรถ</h3></header>';

	$form = new Form(NULL,url('garage/job/'.$jobId.'/info/car.return'),'','sg-form return-date');
	$form->addData('rel', 'notify');
	$form->addData('done', 'load:#garage-job-detail');

	$form->addField(
		'date',
		array(
			'type' => 'text',
			'label' => 'วันที่คืนรถ',
			'name' => 'date',
			'class' => '-date sg-datepicker -fill',
			'value'=>$jobInfo->returndate ? sg_date($jobInfo->returndate,'d/m/Y') : '',
			'placeholder' => '31/12/1999',
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done</i><span>บันทึกการคืนรถ</span>'
		)
	);

	$ret .= $form->build();

	$ret .= '<style type="text/css">
	.return-date {width:200px;}
	.return-date .btn.-primary {width:100%;}
	</style>';
	return $ret;
}

?>