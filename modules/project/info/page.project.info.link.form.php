<?php
/**
* Project :: Link to External Project
* Created 2020-02-24
* Modify  2020-02-24
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function project_info_link_form($self, $projectInfo) {
	$tpid = $projectInfo->tpid;

	if (!$tpid) return message('error', 'PROCESS ERROR');

	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE && $action == 'edit';

	$ret = '<header class="header">'._HEADER_BACK.'<h3>เชื่อมโยงโครงการ</h3></header>';

	$form = new Form(NULL, url('project/'.$tpid.'/info/link.save'), NULL, 'sg-form');
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | load->replace:#project-info-link');

	$form->addField('tpid', array('type' => 'hidden', 'value' => ''));

	$form->addField(
		'q',
		array(
			'type' => 'text',
			'label' => 'โครงการในระบบ',
			'class' => 'sg-autocomplete -fill',
			'value' => '',
			'placeholder' => 'ค้นหาชื่อโครงการในระบบ',
			'attr' => array(
				'data-query' => url('project/api/follows', array('result' => 'autocomplete', 'type' => 'all')),
				'data-altfld' => 'edit-tpid',
			),
		)
	);

	$form->addField(
		'link',
		array(
			'type' => 'text',
			'label' => 'URL ของโครงการภายนอกที่ต้องการเชื่อมโยง',
			'class' => '-fill',
			'value' => htmlspecialchars($projectInfo->info->link),
			'placeholder' => 'https://www.example.com/project/1',
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done</i><span>{tr:SAVE}</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" data-rel="back"><i class="icon -material">cancel</i><span>ยกเลิก</span></a>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	return $ret;
}
?>