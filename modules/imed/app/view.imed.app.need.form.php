<?php
/**
* Module Method
*
* @param
* @return String
*/

$debug = true;

function view_imed_app_need_form($psnInfo) {
	$form = new Form(NULL, url('imed/app/need/'.$psnInfo->psnId.'/new'), NULL, 'sg-form');
	$form->addData('rel', '#main');
	$form->addConfig('title', 'บันทึกความต้องการ');
	$form->addData('checkValid', true);

	$stmt = 'SELECT c.*, p.`name` `parentName`
					FROM %imed_stkcode% c
						LEFT JOIN %imed_stkcode% p ON p.`stkid` = c.`parent`
					WHERE LEFT(c.`parent`,2) IN ("01","02","03","99") ORDER BY c.`stkid`';
	$formOptions = array();
	foreach (mydb::select($stmt)->items as $value) {
		$formOptions[$value->parentName][$value->stkid] = $value->name;
	}
	//$ret .= print_o($formOptions,'$formOptions');

	$form->addField('needtype',
					array(
						'type' => 'select',
						'label' => 'ประเภท',
						'class' => '-fill',
						'require' => true,
						'options' => array('' => '== เลือกประเภท ==') + $formOptions,
					)
				);

	$form->addField('urgency',
					array(
						'type' => 'select',
						'label' => 'ระดับความเร่งด่วน',
						'class' => '-fill',
						'options' => array(1 => 'รอได้', 5 => 'เร่งด่วน', 9=> 'เร่งด่วนมาก'),
						va
					)
				);

	$form->addField('detail',
					array(
						'type' => 'textarea',
						'label' => 'รายละเอียด',
						'class' => '-fill',
					)
				);
	$form->addField('save',
					array(
						'type' => 'button',
						'value' => '<i class="icon -save -white"></i><span>บันทึก</span>',
						'container' => array('class' => '-sg-text-right'),
					)
				);

	$ret .= $form->build();
	return $ret;
}
?>