<?php
/**
* Widget  :: Description
* Created :: 2019-01-01
* Modify  :: 2023-11-25
* Version :: 2
*
* @param Array $args
* @return Widget
*
* @usage import('widget:module.widgetname.php')
* @usage new PaperEditMenuWidget([])
*/

class PaperEditMenuWidget extends Widget {
	var $nodeId;
	var $right;

	function __construct($args = []) {
		parent::__construct($args);
		$this->right = (Object) [
			'editPhoto' => user_access('upload photo'),
			'editDoc' => user_access('upload document'),
			'adminPaper' => user_access('administer contents,administer papers'),
			'makePoll' => mydb::table_exists('%poll%'),
			'archive' => mydb::table_exists('%archive_topic%'),
			'delete' => user_access('administer contents,administer papers','edit own paper', $topic->uid),
		];
	}

	function build() {
		$ui = new Ui(NULL, 'ui-menu');

		$ui->add('<a href="'.url('paper/'.$this->nodeId.'/edit').'"><i class="icon -material">home</i><span>สถานะ</span></a>');
		$ui->add('<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/edit.main').'" data-rel="#main"><i class="icon -material">settings</i><span>จัดการเอกสาร</span></a>');
		$ui->add('<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/edit.detail').'" data-rel="box" data-width="full"><i class="icon -material">description</i><span>รายละเอียด</span></a>');
		if (user_access('upload photo')) $ui->add('<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/edit.photo').'" data-rel="#main"><i class="icon -material">photo_library</i><span>ภาพประกอบ</span></a>');
		if (user_access('upload document')) $ui->add('<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/edit.docs').'" data-rel="#main"><i class="icon -material">attachment</i><span>เอกสารประกอบ</span></a>');
		$ui->add('<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/edit.prop').'" data-rel="#main"><i class="icon -material">text_format</i><span>รูปแบบการแสดงผล</span></a>');

		if (user_access('administer contents,administer papers,administer paper tags')) $ui->add('<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/edit.tag').'" data-rel="#main"><i class="icon -material">category</i><span>จัดการหมวด</span></a>');

		$ui->add('<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/edit.etc').'" data-rel="#main"><i class="icon -material">all_out</i><span>ข้อมูลอื่น ๆ</span></a>');

		if (user_access('administer contents,administer papers')) {
			$ui->add('<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/edit.owner').'" data-rel="#main"><i class="icon -material">person</i><span>Change owner</span></a>');
			$ui->add('<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/edit.weight').'" data-rel="#main"><i class="icon -material">swap_vert</i><span>Weight</span></a>');
			$ui->add('<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/edit.repaircomment').'" data-rel="#main"><i class="icon -material">bug_report</i><span>Repair Comment</span></a>');

			if (mydb::table_exists('%poll%')) {
				$ui->add('<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/edit.makepoll').'" data-rel="#main"><i class="icon -material">poll</i><span>Make Poll</span></a>');
			}

			if (mydb::table_exists('%archive_topic%')) {
				$ui->add('<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/edit.archive').'" data-rel="#main"><i class="icon -material">archive</i><span>Move to Archive</span></a>');
			}
		}

		if ($topic->status != _LOCK && user_access('administer contents,administer papers','edit own paper',$topic->uid)) {
			$ui->add('<sep>');
			$ui->add('<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/delete').'" data-rel="#main" title="ลบหัวข้อนี้"><i class="icon -material">delete</i><span>'.tr('Delete topic').'</span></a>');
		}

		return new Widget([
			'children' => [
				'<nav class="nav" style="padding: 4px;"><a class="btn -fill" href="'.url('paper/'.$this->nodeId).'"><i class="icon -material">arrow_back</i> '.tr('Back to topic').'</a></nav>',
				new Nav([
					'direction' => 'vertical',
					'children' => [
						'<a href="'.url('paper/'.$this->nodeId.'/edit').'"><i class="icon -material">home</i><span>สถานะ</span></a>',
						'<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/edit.main').'" data-rel="#main"><i class="icon -material">settings</i><span>จัดการเอกสาร</span></a>',

						'<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/edit.detail').'" data-rel="box" data-width="full"><i class="icon -material">description</i><span>รายละเอียด</span></a>',
						$this->right->editPhoto ? '<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/edit.photo').'" data-rel="#main"><i class="icon -material">photo_library</i><span>ภาพประกอบ</span></a>' : NULL,
						$this->right->editDoc ? '<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/edit.docs').'" data-rel="#main"><i class="icon -material">attachment</i><span>เอกสารประกอบ</span></a>' : NULL,
						'<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/edit.prop').'" data-rel="#main"><i class="icon -material">text_format</i><span>รูปแบบการแสดงผล</span></a>',

						$this->right->adminPaper ? '<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/edit.tag').'" data-rel="#main"><i class="icon -material">category</i><span>จัดการหมวด</span></a>' : NULL,

						'<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/edit.etc').'" data-rel="#main"><i class="icon -material">all_out</i><span>ข้อมูลอื่น ๆ</span></a>',

						$this->right->adminPaper ? '<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/edit.owner').'" data-rel="#main"><i class="icon -material">person</i><span>Change owner</span></a>' : NULL,
						$this->right->adminPaper ? '<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/edit.weight').'" data-rel="#main"><i class="icon -material">swap_vert</i><span>Weight</span></a>' : NULL,
						$this->right->adminPaper ? '<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/edit.repaircomment').'" data-rel="#main"><i class="icon -material">bug_report</i><span>Repair Comment</span></a>' : NULL,

						$this->right->makePoll ? '<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/edit.makepoll').'" data-rel="#main"><i class="icon -material">poll</i><span>Make Poll</span></a>' : NULL,

						$this->right->archive ? '<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/edit.archive').'" data-rel="#main"><i class="icon -material">archive</i><span>Move to Archive</span></a>' : NULL,

						$this->right->adminPaper ? '<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/edit.duplicate').'" data-rel="#main"><i class="icon -material">content_copy</i><span>Duplicate Topic</span></a>' : NULL,

						// if ($topic->status != _LOCK && user_access('administer contents,administer papers','edit own paper',$topic->uid)) {
						$this->right->delete ? '<sep>' : NULL,
						$this->right->delete ? '<a class="sg-action" href="'.url('paper/'.$this->nodeId.'/delete').'" data-rel="#main" title="ลบหัวข้อนี้"><i class="icon -material">delete</i><span>'.tr('Delete topic').'</span></a>' : NULL,
						// }
					], // children
				]), // Nav
				// $ui,
			]
		]);
	}
}
?>