<?php
/**
* Paper Edit Main
* Created 2019-06-01
* Modify  2019-06-01
*
* @param Object $self
* @param Object $topicInfo
* @return String
*/

$debug = true;

function paper_edit_main($self, $topicInfo) {
	if (!$topicInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $topicInfo->tpid;

	$ret = '<header class="header -box"><nav class="nav -back"><a class="sg-action" href="javascript:void(0)" data-rel="back"><i class="icon -material">arrow_back</i></a></nav><h3>จัดการเอกสาร</h3></header>';

	$form = new Form('topic', url('api/paper/'.$tpid.'/detail.update'), 'edit-topic', 'sg-form');
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | reload');

	$statusOptions = array(
		_DRAFT => '<strong>Draft topic</strong> <em>(Save topic for edit later and not show on website)</em>',
		_PUBLISH => '<strong>Publish topic</strong> <em>(Show topic on website)</em>',
	);
	if (user_access('administer contents,administer papers')) {
		$statusOptions[_BLOCK] = '<strong>Block topic</strong> <em>(General user cannot read topic)</em>';
		$statusOptions[_LOCK] = '<strong>Lock topic</strong> <em>(Cannot delete topic)</em>';
	}

	$form->addField(
			'status',
			array(
				'label' => tr('Status').':',
				'type' => 'radio',
				'options' => $statusOptions,
				'value' => $topicInfo->info->status,
				'container' => '{class: "-fieldset"}',
			)
		);


	if (user_access('administer contents')) {
		$sticky = cfg('sticky');

		$stickyOptions = array(0 => tr('None'));
		foreach (cfg('sticky') as $key => $value) {
			$stickyOptions[$key] = tr($value).' <a class="sg-action" href="'.url('paper/list', array('sticky'=>$key)).'" data-rel="box" data-width="640" title="List of topic in '.tr($value).'"><i class="icon -material -sg-16 -gray">info</i></a>';
		}

		$form->addField(
				'sticky',
				array(
					'label' => tr('Sticky').($topicInfo->sticky ? ' at '.$sticky[$topicInfo->info->sticky] : '').':',
					'type' => 'radio',
					'options' => $stickyOptions,
					'value' => $topicInfo->info->sticky,
					'posttext' => '<p>'.tr('Option').'</p><input type="checkbox" name="clear_sticky" /> '.tr('Clear all sticky of select section'),
					'container' => '{class: "-fieldset"}',
				)
			);
	}

	$form->addField(
			'promote',
			array(
				'label' => 'Promoted to front page:',
				'type' => 'radio',
				'options' => array(1 => 'Yes', 0 => 'No'),
				'value' => $topicInfo->info->promote,
				'container' => '{class: "-fieldset"}',
			)
		);

	$form->addField(
			'save',
			array(
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
				'pretext' => '<a class="btn -link -cancel" href="'.url('paper/'.$tpid.'/edit').'"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
				'container' => '{class: "-sg-text-right"}',
			)
		);

	$ret .= $form->build();

	return $ret;
}
?>