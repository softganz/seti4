<?php
/**
* Project Video Input Form
* Created 2019-09-01
* Modify  2019-10-09
*
* @param Object $self
* @param Object $projectInfo
* @param Int $tranId
* @return String
*/

$debug = true;

function project_info_vdo_form($self, $projectInfo, $tranId = NULL) {
	$tpid = $projectInfo->tpid;

	$ret = '';

	$ret .= '<header class="header -box">'._HEADER_BACK.'<h3>ส่ง Video</h3></header>';

	$form = new Form('tran', url('project/'.$tpid.'/info/vdo.save/'.$tranId), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | load:#main:'.url('project/'.$tpid,array('mode'=>'edit')));

	$form->addField('tag', array('type' => 'hidden', 'value' => post('tag')));
	$form->addField(
		'link',
		array(
			'type' => 'text',
			'label' => 'Video Url:',
			'class' => '-fill',
			'require' => true,
			'placeholder' => 'รูปแบบ http://www.youtube.com/watch?v=...',
		)
	);

	$form->addField(
		'title',
		array(
			'type' => 'text',
			'label' => 'ชื่อ Video',
			'class' => '-fill',
			'value' => '',
			'placeholder' => 'ระบุชื่อ Video',
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
			'pretext' => '<a class="sg-action btn -link -cancel" href="javascript:void(0)" data-rel="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	return $ret;
}
?>