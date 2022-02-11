<?php
function garage_job_appointment($self,$jobInfo) {
	if (!($jobId = $jobInfo->jobId)) return message('error', 'PROCESS ERROR');

	$shopInfo = $jobInfo->shopInfo;

	if (empty($jobInfo)) return message('error','ไม่มีข้อมูลตามที่ระบุ');

	new Toolbar($self,'บันทึกการคืนรถ - '.$jobInfo->plate,'job',$jobInfo);

	$ret = '<h3>บันทึกวันที่นัดรับรถ</h3>';

	$form = new Form(NULL,url('garage/job/'.$jobInfo->tpid.'/info/car.appointment'),'appointment-date','sg-form appointment-date');
	$form->addData('rel', 'notify');
	$form->addData('done', 'load:#garage-job-detail');

	$form->addField(
		'date',
		array(
			'type' => 'text',
			'label' => 'วันที่นัดรับรถ',
			'name' => 'date',
			'class' => '-date sg-datepicker -fill',
			'value'=>$jobInfo->datetoreturn ? sg_date($jobInfo->datetoreturn,'d/m/Y') : '',
			'attr'=>array('data-callback' => 'showCarDateReturn'),
			'placeholder' => '31/12/1999',
		)
	);

	$form->addField('timetoreturn',
		array(
			'type' => 'time',
			'label' => 'เวลานัดรับรถ :',
			'name' => 'time',
			'class' => '-fill',
			'value' => substr($jobInfo->timetoreturn,0,5),
			'step' => 30,
			'start' => 8,
			'end' => 19,
		)
	);

	$form->addField('save',array('type' => 'button','class' => '-fill','value' => '<i class="icon -save -white"></i><span>บันทึกวันนัดรับรถ</span>'));


	if ($jobInfo->datetoreturn) {
		$form->addText('<div class="form-item">กรณีที่ยังไม่นัดรับรถให้คลิกปุ่ม "ยกเลิกวันนัดรับรถ" ด้านล่าง<br /><br /><a class="sg-action btn -danger -fill" href="'.url('garage/job/'.$jobInfo->tpid.'/info/car.appointment.cancel').'" data-rel="notify" data-done="load:#garage-job-detail" data-title="วันนัดรับรถ" data-confirm="ต้องการยกเลิกวันนัดรับรถ กรุณายืนยัน?"><i class="icon -material">cancel</i><span>ยกเลิกวันนัดรับรถ</span></a></div>');
	}

	$ret .= $form->build();

	$ret .= '<div id="carreturndate"></div>';

	
	$ret .= '<style type="text/css">
	.appointment-date {width:200px;}
	.appointment-date .btn.-primary {width:100%;}
	</style>';
	$ret.='<script type="text/javascript">
	function showCarDateReturn(dateText,inst) {
		$.get("'.url('garage/api/appointment').'",{date:dateText},function(html){
			$("#carreturndate").html(html)
		});
	}</script>';
	return $ret;
}

?>