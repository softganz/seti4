<?php
/**
* Project :: Fund Setting Population
* Created 2019-04-01
* Modify  2020-06-11
*
* @param Object $self
* @return String
*
* @usage project/fund/setting/population
*/

$debug = true;

function project_fund_setting_population($self) {
	R::View('project.toolbar',$self,'ระบบบริหารกองทุนสุขภาพตำบล','fund');

	$isWebAdmin = user_access('administer projects');

	if (!$isWebAdmin) return message('error', 'access denied');

	$ret = '<h3>บันทึกข้อมูลประชากรตามทะเบียนราษฎร์</h3>';

	$form = new Form(NULL, url('project/fund/setting/population'));

	$form->addField(
					'open',
					array(
						'type'=>'checkbox',
						'label'=>'เปิดการบันทึกข้อมูล',
						'options' => array(1=>'เปิดการบันทึกข้อมูล')
					)
				);

	$form->addField(
					'year',
					array(
						'type' => 'select',
						'label' => 'สำหรับปีงบประมาณ',
						'options' => array(
													date('Y')+1 => date('Y')+543+1,
													date('Y')+2 => date('Y')+543+2,
												)
					)
				);

	$form->addField(
					'start',
					array(
						'type' => 'text',
						'label' => 'วันที่เริ่มบันทึก',
						'class' => 'sg-datepicker',
					)
				);

	$form->addField(
					'end',
					array(
						'type' => 'text',
						'label' => 'วันที่สิ้นสุดบันทึก',
						'class' => 'sg-datepicker',
					)
				);

	$form->addField(
					'save',
					array(
						'type' => 'button',
						'value' => '<i class="icon -material">done_all</i>{tr:SAVE}',
						'pretext' => '<a class="btn -link -cancel" href=""><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
						'container' => '{class: "-sg-text-right"}'
					)
				);

	$ret .= $form->build();
	return $ret;
}
?>