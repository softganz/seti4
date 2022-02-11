<?php
/**
* Create New Quotation
* Created 2019-10-14
* Modify  2019-10-14
*
* @param Object $self
* @param Object $jobInfo
* @param Int $qtId
* @return String
*/

$debug = true;

function garage_job_qt_form($self, $jobInfo, $qtId = NULL) {
	if (!($jobId = $jobInfo->jobId)) return message('error', 'PROCESS ERROR');

	$shopInfo = $jobInfo->shopInfo;

	if (!$jobId) return message('error', 'PROCESS ERROR');

	$ret .= '<header class="header">'._HEADER_BACK.'<h3>ใบเสนอราคา '.R::Model('garage.nextno', $jobInfo->shopid, 'qt')->nextNo.'</h3></header>';

	$qtInfo = $jobInfo->qt[$qtId];

	if (!$qtInfo) {
		$qtInfo = new stdClass();
		$qtInfo->qtdate = date('Y-m-d');
		$qtInfo->insurerid = $jobInfo->insurerid;
		$qtInfo->insurername = $jobInfo->insurername;
		$qtInfo->insuno = $jobInfo->insuno;
		$qtInfo->insuclaimcode = $jobInfo->insuclaimcode;
	}

	//if (empty($qtInfo->insuclaimcode)) $ret.='<p class="notify">ใบเสนอราคายังไม่ได้ระบุ <b>เลขรับแจ้งประกันภัย</b></p>';

	$form = new Form('qt',url('garage/job/'.$jobInfo->tpid.'/info/qt.save/'.$qtId));

	$form->addField(
		'qtdate',
		array(
			'type' => 'text',
			'label' => 'วันที่เสนอราคา',
			'class' => 'sg-datepicker',
			'value' => sg_date($qtInfo->qtdate,'d/m/Y'),
		)
	);

	$form->addField('insuid', array('type' => 'hidden', 'id' => 'insuid', 'value' => htmlspecialchars($qtInfo->insurerid)));

	$form->addField(
		'insuname',
		array(
			'type' => 'text',
			'id' => 'insuname',
			'name' => false,
			'class' => 'sg-autocomplete -fill',
			'attr' => array(
				'data-query' => url('garage/api/insurer'),
				'data-altfld' => "insuid",
			),
			'value' => htmlspecialchars($qtInfo->insurername),
			'placeholder' => 'ชื่อบริษัทประกัน',
			'posttext' => '<div class="input-append"><span><a class="sg-action" href="'.url('garage/api/insurername').'" data-rel="box" data-width="480"><i class="icon -material -gray">keyboard_arrow_down</i></a></span><span><a class="-sg-16" href="javascript:void(0)" onclick=\'$("#insuid").val("");$("#insuname").val("");\'><i class="icon -material -gray -sg-16">clear</i></a></span></div>',
			'container' => '{class: "-group"}',
		)
	);

	$form->addField(
		'insuno',
		array(
			'type' => 'text',
			'label' => 'เลขกรมธรรม์',
			'class' => '-fill',
			'value'=>$qtInfo->insuno
		)
	);

	$form->addField(
		'claimcode',
		array(
			'type' => 'text',
			'label' => 'เลขรับแจ้งประกันภัย',
			'class' => '-fill',
			'value'=>$qtInfo->insuclaimcode
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -save -white"></i><span>{tr:SAVE}</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" data-rel="close"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	//$ret.=print_o($qtInfo);
	//$ret .= print_o($jobInfo,'$jobInfo');

	return $ret;
}
?>