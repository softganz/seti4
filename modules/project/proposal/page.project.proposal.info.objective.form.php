<?php
/**
* Project Objective Form
*
* @param Object $self
* @param Object $proposalInfo
* @return String
*/
function project_proposal_info_objective_form($self, $proposalInfo) {
	$tpid = $proposalInfo->tpid;

	if (!$tpid) return message('error', 'PROCESS ERROR');

	$ret .= '<header class="header -box"><h3>เพิ่มวัตถุประสงค์</h3></header>';

	$stmt = 'SELECT p.*,pn.`name` `planName`
		FROM %tag% p
			LEFT JOIN %tag% pn ON pn.`taggroup` = "project:planning" AND CONCAT("project:problem:",pn.`catid`) = p.`taggroup`
		WHERE p.`taggroup` IN
			(SELECT CONCAT("project:problem:",`refid`)
				FROM %project_tr%
				WHERE `tpid` = :tpid AND `formid` = :tagname AND `part` = "supportplan")
		';

	$problemDbs = mydb::select($stmt, ':tpid', $tpid, ':tagname', _PROPOSAL_TAGNAME);

	//$ret .= print_o($problemDbs);

	$form = new Form(
		NULL,
		url('project/proposal/'.$tpid.'/info/objective.save'),
		NULL,
		'sg-form project-objective-form'
	);

	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | load->replace:#project-proposal-objective | load->replace:#project-proposal-plan');


	/*
	$optionsObjective['']='==เลือกตัวอย่างวัตถุประสงค์==';
	foreach ($problemDbs->items as $rs) {
		$detail=json_decode($rs->description);
		$optionsObjective[$rs->planName][$rs->taggroup.':'.$rs->catid]=$detail->objective;
	}
	$form->addField(
						'problemref',
						array(
							'type'=>'select',
							'label'=>'เลือกตัวอย่างวัตถุประสงค์:',
							'class'=>'-fill',
							'options'=>$optionsObjective,
						)
					);
	*/

	$form->addField(
		'objective',
		array(
			'type' => 'text',
			'label' => 'ระบุวัตถุประสงค์',
			'class' => '-fill',
			'require' => true,
			'placeholder' => 'เช่น เพื่อเพิ่มจำนวนผู้มีกิจกรรมทางกายในชุมชน',
		)
	);

	$form->addField(
		'indicator',
		array(
			'type'=>'textarea',
			'label'=>'ตัวชี้วัดความสำเร็จ',
			'class'=>'-fill',
			'rows'=>3,
			'placeholder' => 'เช่น ร้อยละของผู้มีกิจกรรมทางกายในชุมชนเพิ่มขึ้น'
		)
	);

	$form->addField(
		'problemsize',
		array(
			'type'=>'text',
			'label'=>'ขนาดปัญหา (หน่วยตามตัวชี้วัดความสำเร็จ)',
			'class'=>'-fill',
			'placeholder'=>'0.00',
			'autocomplete'=>'off',
		)
	);

	$form->addField(
		'targetsize',
		array(
			'type'=>'text',
			'label'=>'เป้าหมาย 1 ปี (หน่วยตามตัวชี้วัดความสำเร็จ)',
			'class'=>'-fill',
			'placeholder'=>'0.00',
			'autocomplete'=>'off',
		)
	);

	$form->addField(
		'save',
		array(
			'type'=>'button',
			'value'=>'<i class="icon -save -white"></i><span>บันทึกวัตถุประสงค์</span>',
			'pretext'=>'<a class="sg-action btn -link -cancel" data-rel="close"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a> ',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	$ret .= '<script type="text/javascript">
	$("#edit-problemref").change(function(){
		var $this=$(this);
		console.log("Change "+$this.val());
		if ($this.val()!="") {
			$("#form-item-edit-objective").hide();
			$("#edit-objective").val("");
			$("#form-item-edit-indicator").hide();
		} else {
			$("#form-item-edit-objective").show();
			$("#form-item-edit-indicator").show();
		}
	});
	</script>';
	return $ret;
}
?>