<?php
/**
* Paper Edit Photo
* Created 2019-06-01
* Modify  2019-06-01
*
* @param Object $self
* @param Object $topicInfo
* @param Int $fileId
* @return String
*/

$debug = true;

function paper_edit_photo_info($self, $topicInfo, $fileId = NULL) {
	if (!$topicInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $topicInfo->tpid;

	$ret = '<header class="header -box"><nav class="nav -back"><a class="sg-action" href="javascript:void(0)" data-rel="back" data-width="640" data-height="80%"><i class="icon -material">arrow_back</i></a></nav><h3>{tr:Photo information}</h3></header>';

	$info = $topicInfo->photos[$fileId];

	$form = new Form('photoinfo',url('api/paper/'.$tpid.'/detail.update/'.$info->fid), NULL, 'sg-form');
	$form->addData('rel', 'close');

	$form->addField('fid', array('type'=>'hidden', 'value'=>$fileId));

	$form->addField(
			'title',
			array(
				'type' => 'text',
				'label' => 'ชื่อภาพ',
				'class' => '-fill',
				'value' => $info->title,
			)
		);

	$form->addField(
			'description',
			array(
				'type' => 'textarea',
				'label' => 'บรรยายภาพ',
				'class' => '-fill',
				'rows' => '6',
				'value' => $info->description,
			)
		);

	$form->addField(
			'save',
			array(
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
				'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('paper/'.$tpid.'/photo/'.$fileId).'" data-rel="box" data-width="640" data-height="80%"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
				'container' => '{class: "-sg-text-right"}',
			)
		);

	$ret .= $form->build();

	return $ret;
}
?>