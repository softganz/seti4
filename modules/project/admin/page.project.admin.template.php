<?php
/**
* Project owner
*
* @param Object $self
* @param Object $tpid
* @return String
*/
function project_admin_template($self,$tpid) {
	$initCmdKey='project:INIT:'.$tpid;
	$homeCmdKey='project:HOMEPAGE:'.$tpid;

	$projectInfo=R::Model('project.get',$tpid,'{data:"info"}');

	R::View('project.toolbar',$self,'Project Template','admin');
	$self->theme->sidebar=R::View('project.admin.menu');

	if (post('init')) {
		property($initCmdKey,post('init'));
	}

	if (post('homepage')) {
		property($homeCmdKey,post('homepage'));
	}

	//$ret.=print_o($projectInfo);

	$initCmd=property($initCmdKey);
	$homeCmd=property($homeCmdKey);

	$form=new Form([
		'action' => url('project/admin/template/'.$tpid),
		'title' => $projectInfo->title.'<a href="'.url('paper/'.$tpid).'"><i class="icon -viewdoc"></i></a>',
		'children' => [
			'init' => [
				'type'=>'textarea',
				'label'=>'Initial Command',
				'class'=>'-fill',
				'rows'=>20,
				'value'=>htmlspecialchars($initCmd),
			],
			'homepage' => [
				'type'=>'textarea',
				'label'=>'Home Page Command',
				'class'=>'-fill',
				'rows'=>20,
				'value'=>htmlspecialchars($homeCmd),
			],
			'save' => [
				'type'=>'button',
				'value'=>'<i class="icon -save -white"></i><span>{tr:Save}</span>',
			],
		],
	]);

	$ret.=$form->build();
	return $ret;
}
?>