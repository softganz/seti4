<?php
/**
* Paper   :: Edit Main
* Created :: 2019-06-01
* Modify  :: 2025-06-25
* Version :: 3
*
* @param String $topicInfo
* @return Widget
*
* @usage paper/{Id}/edit.main
*/

class PaperEditMain extends Page {
	var $nodeId;
	var $right;
	var $topicInfo;

	function __construct($topicInfo = NULL) {
		parent::__construct([
			'nodeId' => $topicInfo->nodeId,
			'topicInfo' => $topicInfo,
			'right' => (Object) [
				'admin' => user_access('administer contents'),
				'edit' => $this->topicInfo->right->edit,
				'block' => user_access('administer contents,administer papers'),
				'lock' => user_access('administer contents,administer papers'),
			]
		]);
	}

	function rightToBuild() {
		if (empty($this->nodeId)) return error(_HTTP_ERROR_NOT_FOUND, 'ไม่มีหัวข้อตามที่ระบุ');
		if (!$this->topicright->edit) return message('error', 'Access Denied');
	}

	function build() {			
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'จัดการเอกสาร',
				'leading' => _HEADER_BACK,
				'boxHeader' => true,
			]), // AppBar
			'body' => new Form([
				'variable' => 'topic',
				'action' => url('api/paper/'.$this->nodeId.'/detail.update'),
				'id' => 'edit-topic',
				'class' => 'sg-form',
				'rel' => 'notify',
				'done' => 'close | reload',
				'children' => [
					'status' => [
						'label' => tr('Status').':',
						'type' => 'radio',
						'choice' => [
							_DRAFT => '<strong>Draft topic</strong> <em>(Save topic for edit later and not show on website)</em>',
							_PUBLISH => '<strong>Publish topic</strong> <em>(Show topic on website)</em>',
							_BLOCK => $this->right->block ?'<strong>Block topic</strong> <em>(General user cannot read topic)</em>' : NULL,
							_LOCK => $this->right->lock ? '<strong>Lock topic</strong> <em>(Cannot delete topic)</em>' : NULL,
						],
						'value' => $this->topicInfo->info->status,
						'container' => '{class: "-fieldset"}',
					],
					'sticky' => $this->right->admin ? [
						'label' => tr('Sticky').($this->topicInfo->sticky ? ' at '.$sticky[$this->topicInfo->info->sticky] : '').':',
						'type' => 'radio',
						'choice' => (function(){
							$stickyOptions = [0 => tr('None')];
							foreach (cfg('sticky') as $key => $value) {
								$stickyOptions[$key] = tr($value).' <a class="sg-action" href="'.url('paper/list', array('sticky'=>$key)).'" data-rel="box" data-width="640" title="List of topic in '.tr($value).'"><i class="icon -material -sg-16 -gray">info</i></a>';
							}
							return $stickyOptions;
						})(),
						'value' => $this->topicInfo->info->sticky,
						'posttext' => '<p>'.tr('Option').'</p><input type="checkbox" name="clear_sticky" /> '.tr('Clear all sticky of select section'),
						'container' => '{class: "-fieldset"}',
					] : NULL,
					'promote' => [
						'label' => 'Promoted to front page:',
						'type' => 'radio',
						'options' => array(1 => 'Yes', 0 => 'No'),
						'value' => $this->topicInfo->info->promote,
						'container' => '{class: "-fieldset"}',
					],
					'save' => [
						'type' => 'button',
						'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
						'pretext' => '<a class="btn -link -cancel" href="'.url('paper/'.$this->nodeId.'/edit').'"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
						'container' => '{class: "-sg-text-right"}',
					],
				], // children
			]), // Form
		]);
	}
}
?>