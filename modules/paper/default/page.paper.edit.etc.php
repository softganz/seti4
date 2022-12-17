<?php
/**
* Paper Edit Other Information
* Created 2019-06-02
* Modify  2019-06-02
*
* @param Object $self
* @param Object $topicInfo
* @return String
*/

$debug = true;

function paper_edit_etc($self, $topicInfo) {
	if (!$topicInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $topicInfo->tpid;


	$ret = '<header class="header -box"><nav class="nav -back"><a class="" href="'.url('paper/'.$tpid.'/edit').'"><i class="icon -material">arrow_back</i></a></nav><h3>แก้ไขรายละเอียดอื่น ๆ</h3></header>';

	$type = CommonModel::get_topic_type($topicInfo->info->type);

	$form = new Form([
		'variable' => 'topic',
		'action' => url('paper/info/api/'.$tpid.'/update'),
		'class' => 'sg-form',
		'rel' => 'notify',
		'done' => 'reload',
		'children' => [
			'redirect' => [
				'type' => 'text',
				'name' => 'detail[redirect]',
				'label' => 'Redirect to URL',
				'maxlength' => 255,
				'class' => '-fill',
				'value' => $topicInfo->info->redirect,
				'placeholder' => 'e.g. http://www.example.com',
			],
			'poster' => cfg('member.name_alias') ? [
				'type' => 'text',
				'name' => 'topic[poster]',
				'label' => 'ชื่อผู้ส่ง',
				'maxlength' => 150,
				'class' => '-fill',
				'value' => $topicInfo->info->poster,
				'placeholder' => 'e.g. Mr. John Doe',
			] : NULL,
			'save' => [
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
				'pretext' => '<a class="btn -link -cancel" href="'.url('paper/'.$tpid.'/edit').'"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
				'container' => '{class: "-sg-text-right"}',
			]
		], // children
	]);

	$ret .= $form->build();

	return $ret;
}
?>