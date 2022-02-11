<?php
/**
* Project :: Page Init Command
* Created 2020-06-04
* Modify  2020-06-04
*
* @param Object $self
* @param Object $projectInfo
* @return String
*/

$debug = true;

function project_page_init($self, $projectInfo = NULL) {
	$tpid = $projectInfo->tpid;
	if (!$tpid) return message('error', 'PROCESS ERROR');

	$isAdmin = user_access('access administrator pages');

	if (!$isAdmin) return message('error', 'Access Denied');

	$initCmdKey='project:INIT:'.$tpid;

	if (post('init')) {
		property($initCmdKey,post('init'));
		return 'SAVED';
	}

	$ret = '<header class="header">'._HEADER_BACK.'<h3>Project Page Init Command</h3></header>';

	$form = new Form(NULL,url('project/'.$tpid.'/page.init'), NULL, 'sg-form');
	$form->addData('rel', 'notify');
	$form->addData('done', 'reload');

	$initCmd = property($initCmdKey);

	$form->addField(
		'init',
		array(
			'type'=>'textarea',
			'label'=>'Initial Command',
			'class'=>'-fill',
			'rows'=>20,
			'value'=>htmlspecialchars($initCmd),
		)
	);

	$form->addField(
		'save',
		array(
			'type'=>'button',
			'value'=>'<i class="icon -save -white"></i><span>{tr:Save}</span>',
		)
	);

	$ret.=$form->build();
	return $ret;
}
?>