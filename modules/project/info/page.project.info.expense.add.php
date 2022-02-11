<?php
/**
* Module Method
* Created 2019-10-01
* Modify  2019-10-01
*
* @param Object $self
* @param Object $projectInfo
* @return String
*/

$debug = true;

function project_info_expense_add($self,$projectInfo,$actionId = NULL) {
	$tpid = $projectInfo->tpid;

	if (!$tpid) return message('error', 'PROCESS ERROR');

	$getExpGroup = post('gr');
	$getTrid = post('tr');

	$actionInfo = R::Model('project.action.get', ['projectId' => $tpid, 'actionId' => $actionId]);

	$ret .= '<header class="header -box">'._HEADER_BACK.'<h3>รายการใช้เงินในกิจกรรม</h3></header>';

	$data = new stdClass();

	if ($getTrid) {
		$data = mydb::select('SELECT *, `num1` `amt`, `num2` `tax`, `detail1` `description`, `detail2` `refno`, `date1` `refdate`, `text1` `remark` FROM %project_tr% WHERE `trid` = :trid LIMIT 1',':trid',$getTrid);
	}

	$expCodeList = R::Model('project.expense.code.get',NULL,$getExpGroup,'{resultType:"select"}');


	$ret.='<h4>เพิ่มรายการใช้เงินในกิจกรรม</h4>';

	$form=new Form('data',url('project/'.$tpid.'/info/expense.save/'.$actionId),NULL,'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'back | load->replace:#project-info-expense');

	$form->addField('trid', array('type' => 'hidden', 'value' => $data->trid));
	$form->addField('calid', array('type' => 'hidden', 'value' => $actionInfo->calid));

	$form->addField(
		'refid',
		array(
			'label' => 'รายการรายจ่าย :',
			'type' => 'select',
			'class' => '-fill',
			'require' => true,
			'options' => $expCodeList,
			'value' => $data->refid,
		)
	);

	$form->addField(
		'amt',
		array(
			'type' => 'text',
			'label' => 'จำนวนเงิน (บาท)',
			'require' => true,
			'value' => htmlspecialchars($data->amt),
		)
	);

	$form->addField(
		'tax',
		array(
			'type' => 'text',
			'label' => 'ภาษีหัก ณ ที่จ่าย (บาท)',
			'value' => htmlspecialchars($data->tax),
		)
	);

	$form->addField(
		'description',
		array(
			'type' => 'text',
			'label' => 'รายละเอียดค่าใช้จ่าย',
			'class' => '-fill',
			'value' => htmlspecialchars($data->description),
		)
	);

	$form->addField(
		'refno',
		array(
			'type' => 'text',
			'label' => 'เลขที่เอกสารอ้างอิง',
			'class' => '-fill',
			'value' => htmlspecialchars($data->refno),
		)
	);

	$form->addField(
		'refdate',
		array(
			'type' => 'text',
			'label' => 'วันที่เอกสารอ้างอิง',
			'class' => 'sg-datepicker',
			'value' => $data->refdate?sg_date($data->refdate,'d/m/Y'):'',
			'attr' => array(
				'data-min-date'=>sg_date($projectInfo->info->date_from,'d/m/Y'),
				'data-max-date'=>sg_date($projectInfo->info->date_end,'d/m/Y'),
				'data-change-month'=>true,
				'data-change-year'=>true,
			),
		)
	);

	$form->addField(
		'remark',
		array(
			'type' => 'textarea',
			'label' => 'หมายเหตุ',
			'class' => '-fill',
			'rows' => 3,
			'value' => $data->remark,
		)
	);

	$form->addField(
		'save',
		array(
			'type'=>'button',
			'value'=>'<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
			'pretext'=>'<a class="sg-action btn -link -cancel" href="javascript:void(0)" data-rel="close"><i class="icon -material">cancel></i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret.=$form->build();

	if (user_access(false)) $ret.='<hr /><a class="sg-action" href="'.url('project/'.$tpid.'/info.expense/'.$calid.'/add/'.$expGroup).'" data-rel="box">Refresh</a>';

	//$ret.=print_o($post,'$post');
	//$ret.=print_o($projectInfo,'$projectInfo');
	return $ret;
}
?>