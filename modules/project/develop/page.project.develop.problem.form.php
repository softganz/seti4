<?php
/**
* Project Objective Form
*
* @param Object $self
* @param Integer $tpid
* @return String
*/
function project_develop_problem_form($self, $tpid = NULL) {
	$devInfo = is_object($tpid) ? $tpid : R::Model('project.develop.get',$tpid, '{initTemplate: true}');
	$tpid = $devInfo->tpid;

	$form = new Form(NULL,url('project/develop/info/'.$tpid.'/problem.edit'),NULL,'sg-form project-develop-problem-form');
	$form->addData('rel','#project-develop-problem');
	$form->addData('ret',url('project/develop/'.$tpid.'/problem/edit'));
	$form->addData('checkValid',true);
	$form->addConfig('title','เพิ่มสถานการณ์ปัญหา');

	$form->addField(
						'problemother',
						array(
							'type'=>'text',
							'name'=>'problemother',
							'label'=>'ระบุสถานการณ์ปัญหา',
							'class'=>'-fill',
							'require'=>true,
							'placeholder'=>'ระบุสถานการณ์เพิ่มเติม',
						)
					);

	$form->addField(
						'problemsize',
						array(
							'type'=>'text',
							'name'=>'problemsize',
							'label'=>'ขนาด',
							'class'=>'-fill',
							'require'=>true,
							'placeholder'=>'0.00',
							'autocomplete'=>'off',
						)
					);

	$form->addField(
				'save',
				array(
					'type' => 'button',
					'value' => '<i class="icon -save -white"></i><span>บันทึกสถานการณ์</span>',
					'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('project/develop/'.$tpid.'/problem/edit').'" data-rel="#project-develop-problem"><i class="icon -cancel -gray"></i><span>{tr:Cancel}</span></a>',
					'container' => array('class'=>'-sg-text-right'),
					)
				);

	$ret .= $form->build();


	return $ret;
}
?>