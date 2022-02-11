<?php
function garage_job_carindate($self,$jobInfo) {
	if (!($jobId = $jobInfo->jobId)) return message('error', 'PROCESS ERROR');

	$isEdit = $jobInfo->is->editable;
	$shopInfo = $jobInfo->shopInfo;

	if (empty($jobInfo)) return message('error','ไม่มีข้อมูลตามที่ระบุ');

	new Toolbar($self,'บันทึกวันรับรถเข้าซ่อม - '.$jobInfo->plate,'job',$jobInfo);

	$ret = '<header class="header -box"><h3>บันทึกวันรับรถเข้าซ่อม</h3></header>';

	$form = new Form(NULL, url('garage/job/'.$jobInfo->tpid.'/info/car.indate'), NULL, 'sg-form carin-date');
	$form->addData('rel', 'notify');
	$form->addData('done', 'load:#garage-job-detail');

	$form->addField(
		'date',
		array(
			'type' => 'text',
			'label' => 'วันรับรถเข้าซ่อม',
			'name' => 'date',
			'class' => '-date sg-datepicker -fill',
			'value'=> $jobInfo->carindate ? sg_date($jobInfo->carindate,'d/m/Y') : '',
			'placeholder' => '31/12/1999',
			)
		);

	if ($isEdit) {
		$form->addField(
			'save',
			array(
				'type' => 'button',
				'class' => '-fill',
				'value' => '<i class="icon -material">done</i><span>บันทึกวันรับรถเข้าซ่อม</span>'
			)
		);
	}

	$ret .= 'กรณีที่รถรับเข้าซ่อมแล้ว';
	$ret .= $form->build();

	if ($isEdit && $jobInfo->carindate) {
		$ret .= 'กรณีที่รถยังไม่รับเข้าซ่อมให้คลิกปุ่ม "บันทึกรถยังไม่เข้าซ่อม" ด้านล่าง<br /><br /><a class="sg-action btn -danger" href="'.url('garage/job/'.$jobInfo->tpid.'/info/car.notin').'" data-rel="notify" data-done="load:#garage-job-detail" data-title="รถยังไม่เข้าซ่อม" data-confirm="บันทึกรถยังไม่เข้าซ่อม กรุณายืนยัน?"><i class="icon -material">cancel</i><span>บันทึกรถยังไม่รับรถเข้าซ่อม</span></a>';
	}


	$ret .= '<div id="carreturndate"></div>';

	$ret .= '<style type="text/css">
	.carin-date {width:200px;}
	.carin-date .btn.-primary {width:100%;}
	</style>';

	$ret .= '<script type="text/javascript">
	function showCarDateReturn(dateText,inst) {
		$.get("'.url('garage/api/appointment').'",{date:dateText},function(html){
			$("#carreturndate").html(html)
		});
	}</script>';
	return $ret;
}

?>