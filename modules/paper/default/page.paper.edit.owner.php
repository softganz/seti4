<?php
/**
* Paper Edit Owner
* Created 2019-06-01
* Modify  2019-06-01
*
* @param Object $self
* @param Object $topicInfo
* @return String
*/

$debug = true;

function paper_edit_owner($self, $topicInfo) {
	if (!$topicInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $topicInfo->tpid;

	$ret = '<header class="header -box"><nav class="nav -back"><a class="" href="'.url('paper/'.$tpid.'/edit').'"><i class="icon -material">arrow_back</i></a></nav><h3>เปลี่ยนเจ้าของ</h3></header>';

	$form = new Form([
		'variable' => 'topic',
		'action' => url('api/paper/'.$tpid.'/detail.update'),
		'id' => 'edit-topic',
		'class' => 'sg-form',
		'rel' => 'notify',
		'done' => 'reload',
		'checkValid' => true,
		'children' => [
			'uid' => [
				'type' => 'text',
				'label' => 'New owner id',
				'require' => true,
				'class' => '-fill',
				'value' => $topicInfo->uid,
			],
			'save' => [
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
				'pretext' => '<a class="btn -link -cancel" href="'.url('paper/'.$tpid.'/edit').'"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
				'container' => '{class: "-sg-text-right"}',
			],
		], // children
	]);

	$ret .= $form->build();

	return $ret;
}
?>