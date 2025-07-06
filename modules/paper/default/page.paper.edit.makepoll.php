<?php
/**
 * Paper   :: Make Paper as Poll
 * Created :: 2019-06-02
 * Modify  :: 2025-06-25
 * Version :: 4
 *
 * @param String $topicInfo
 * @return Widget
 *
 * @usage paper/{Id}/edit.makepoll
 */

use Softganz\DB;

class PaperEditMakepoll extends Page {
	var $nodeId;
	var $right;
	var $topicInfo;

	function __construct($topicInfo = NULL) {
		parent::__construct([
			'nodeId' => $topicInfo->nodeId,
			'topicInfo' => $topicInfo,
			'right' => (Object) [
				'edit' => $topicInfo->right->edit
			]
		]);
	}

	function rightToBuild() {
		if (empty($this->nodeId)) return error(_HTTP_ERROR_NOT_FOUND, 'ไม่มีข้อมูลตามที่ระบุ');
		if (!$this->right->edit) return error(_HTTP_ERROR_FORBIDDEN, 'Access Denied');
		return true;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'สร้างแบบสำรวจความคิดเห็น',
				'leading' => _HEADER_BACK,
				'boxHeader' => true,
			]), // AppBar
			'body' => new Form([
				'vaiable' => 'poll',
				'action' => url('api/paper/'.$this->nodeId.'/poll.update'),
				'id' => 'edit-topic',
				'children' => [
					new Children([
						'children' => (function() {
							$polls = [];
							$pollAnswer = DB::select([
								'SELECT * FROM %poll_choice% WHERE `tpid` = :tpid ORDER BY `choice` ASC',
								'var' => [':tpid' => $this->nodeId],
								'options' => ['key' => 'choice']
							])->items;

							for ($i = 1; $i <= 10; $i++) {
								$polls[$i] = [
									'type' => 'text',
									'label' => 'คำตอบที่ '.$i,
									'class' => '-fill',
									'name' => 'poll['.$i.']',
									'value' => $pollAnswer[$i]->detail,
									'placeholder' => 'ระบุคำตอบ'
								];
							}
							// debugMsg($polls, '$polls');
							return $polls;
						})()
					]),
					'save' => [
						'type' => 'button',
						'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
						'pretext' => '<a class="btn -link -cancel" href="'.url('paper/'.$this->nodeId.'/edit').'"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
						'container' => '{class: "-sg-text-right"}',
					]
				], // children
			]), // Form
		]);
	}
}
?>