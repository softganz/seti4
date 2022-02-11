<?php
/**
* Project Action Join Setting
* Created 2019-07-26
* Modify  2019-07-30
*
* @param Object $self
* @param Int $projectInfo
* @param Int $calid
* @return String
*/

$debug = true;

function project_join_setting_options($self, $projectInfo, $calid) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;


	$isAdmin = $projectInfo->RIGHT & _IS_ADMIN;
	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;

	if (!$isAdmin) return message('error', 'access denied');


	$doingInfo = R::Model('org.doing.get', array('calid' => $calid), '{data: "info"}');


	$ret .= '<h3>Options</h3>';

	$form = new Form(NULL, url('project/edit/tr/'.$tpid),NULL,'sg-form');
	$form->addData('rel','notify:ดำเนินการเรียบร้อย');
	$form->addField('group',array('type'=>'hidden','value'=>'doings'));
	$form->addField('tpid',array('type'=>'hidden','value'=>$tpid));
	$form->addField('tr',array('type'=>'hidden','value'=>$doingInfo->doid));
	$form->addField('fld',array('type'=>'hidden','value'=>'options'));
	$form->addField('action',array('type'=>'hidden','value'=>'save'));
	//$form->addField('debug',array('type'=>'hidden','value'=>'yes'));
	$form->addField('preservtab',array('type'=>'hidden','value'=>'yes'));

	$form->addField(
		'value',
		array(
			'type' => 'textarea',
			'class' => '-fill',
			'rows' => 20,
			'value' => sg_json_encode($doingInfo->options),
		)
	);

	$form->addField(
		'submit',
		array(
			'type' => 'button',
			'value' => '<i class="icon -save -white"></i>{tr:SAVE}',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();


	$ret .= '</div><!-- setting -->';

	//$ret .= print_o($doingInfo, '$doingInfo');
	//$ret .= print_o($projectInfo);

	$ret .= '<script type="text/javascript">
	function projectJoinSettingImportFromDefault() {
		$("#edit-value").val($("#default").val())
	}
	</script>';
	return $ret;
}
?>