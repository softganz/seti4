<?php
/**
* Paper   :: Edit Weight
* Created :: 2019-06-01
* Modify  :: 2024-03-20
* Version :: 2
*
* @param Object $self
* @param Object $topicInfo
* @return String
*/

$debug = true;

function paper_edit_weight($self, $topicInfo) {
	if (!$topicInfo->tpid) return message('error', 'PARAMETER ERROR');
	if (!$topicInfo->right->edit) return message('error', 'Access Denied');

	$tpid = $topicInfo->tpid;

	$ret = '<header class="header -box"><nav class="nav -back"><a class="" href="'.url('paper/'.$tpid.'/edit').'"><i class="icon -material">arrow_back</i></a></nav><h3>ลำดับการแสดงผล</h3></header>';

	$form = new Form([
		'variable' => 'topic',
		'action' => url('api/paper/'.$tpid.'/detail.update'),
		'id' => 'edit-topic',
		'class' => 'sg-form',
		'rel' => 'notify',
		'done' => 'reload',
		'checkValid' => true,
		'children' => [
			'weight' => [
				'type' => 'select',
				'label' => 'ลำดับการแสดงผล',
				'require' => true,
				'class' => '-fill',
				'options' => '-10..10',
				'value' => $topicInfo->info->weight,
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