<?php
/**
 * Paper   :: Edit Menu
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2019-01-01
 * Modify  :: 2026-05-29
 * Version :: 5
 *
 * @param Array $args
 * @return Widget
 *
 * @example import('widget:module.widgetname.php')
 * @example new PaperEditMenuWidget([])
 */

use Softganz\DB;

class PaperEditMenuWidget extends Sidebar {
	var $nodeId;
	var $right;

	function __construct($args = []) {
		parent::__construct($args);
		$this->right = (Object) [
			'editPhoto' => user_access('upload photo'),
			'editDoc' => user_access('upload document'),
			'adminPaper' => user_access('administer contents,administer papers'),
			'makePoll' => DB::tableExists('%poll%'),
			'archive' => DB::tableExists('%archive_topic%'),
			'delete' => user_access('administer contents,administer papers', 'edit own paper', $topic->uid),
		];
	}

	function build() {
		return new Nav([
			'direction' => 'vertical',
			'children' => [
				new Button([
					'type' => 'link',
					'href' => Url::link('paper/' . $this->nodeId),
					'icon' => new Icon('arrow_back'),
					'text' => tr('Back to topic')
				]),
				new Button([
					'type' => 'link',
					'href' => Url::link('paper/' . $this->nodeId . '/edit'),
					'icon' => new Icon('home'),
					'text' => 'สถานะ'
				]),
				new Button([
					'type' => 'link',
					'class' => 'sg-action',
					'href' => Url::link('paper/' . $this->nodeId . '/edit.main'),
					'icon' => new Icon('settings'),
					'text' => 'จัดการเอกสาร',
					'rel' => '#main',
				]),
				new Button([
					'type' => 'link',
					'class' => 'sg-action',
					'href' => Url::link('paper/' . $this->nodeId . '/edit.detail'),
					'icon' => new Icon('description'),
					'text' => 'รายละเอียด',
					'rel' => '#main',
					'boxWidth' => 'full',
				]),
				$this->right->editPhoto ? new Button([
					'type' => 'link',
					'class' => 'sg-action',
					'href' => Url::link('paper/' . $this->nodeId . '/edit.photo'),
					'rel' => '#main',
					'icon' => new Icon('photo_library'),
					'text' => 'ภาพประกอบ'
				]) : NULL,
				$this->right->editDoc ? new Button([
					'type' => 'link',
					'class' => 'sg-action',
					'href' => Url::link('paper/' . $this->nodeId . '/edit.docs'),
					'rel' => '#main',
					'icon' => new Icon('attachment'),
					'text' => 'เอกสารประกอบ'
				]) : NULL,
				new Button([
					'type' => 'link',
					'class' => 'sg-action',
					'href' => Url::link('paper/' . $this->nodeId . '/edit.prop'),
					'rel' => '#main',
					'icon' => new Icon('text_format'),
					'text' => 'รูปแบบการแสดงผล'
				]),

				$this->right->adminPaper ? new Button([
					'type' => 'link',
					'class' => 'sg-action',
					'href' => Url::link('paper/' . $this->nodeId . '/edit.tag'),
					'rel' => '#main',
					'icon' => new Icon('category'),
					'text' => 'จัดการหมวด'
				]) : NULL,

				new Button([
					'type' => 'link',
					'class' => 'sg-action',
					'href' => Url::link('paper/' . $this->nodeId . '/edit.etc'),
					'rel' => '#main',
					'icon' => new Icon('all_out'),
					'text' => 'ข้อมูลอื่น ๆ'
				]),

				$this->right->adminPaper ? new Button([
					'type' => 'link',
					'class' => 'sg-action',
					'href' => Url::link('paper/' . $this->nodeId . '/edit.owner'),
					'rel' => '#main',
					'icon' => new Icon('person'),
					'text' => 'Change owner'
				]) : NULL,
				$this->right->adminPaper ? new Button([
					'type' => 'link',
					'class' => 'sg-action',
					'href' => Url::link('paper/' . $this->nodeId . '/edit.weight'),
					'rel' => '#main',
					'icon' => new Icon('swap_vert'),
					'text' => 'Weight'
				]) : NULL,
				$this->right->adminPaper ? new Button([
					'type' => 'link',
					'class' => 'sg-action',
					'href' => Url::link('paper/' . $this->nodeId . '/edit.repaircomment'),
					'rel' => '#main',
					'icon' => new Icon('bug_report'),
					'text' => 'Repair Comment'
				]) : NULL,

				$this->right->makePoll ? new Button([
					'type' => 'link',
					'class' => 'sg-action',
					'href' => Url::link('paper/' . $this->nodeId . '/edit.makepoll'),
					'rel' => '#main',
					'icon' => new Icon('poll'),
					'text' => 'Make Poll'
				]) : NULL,

				$this->right->archive ? new Button([
					'type' => 'link',
					'class' => 'sg-action',
					'href' => Url::link('paper/' . $this->nodeId . '/edit.archive'),
					'rel' => '#main',
					'icon' => new Icon('archive'),
					'text' => 'Move to Archive'
				]) : NULL,

				$this->right->adminPaper ? new Button([
					'type' => 'link',
					'class' => 'sg-action',
					'href' => Url::link('paper/' . $this->nodeId . '/edit.duplicate'),
					'rel' => '#main',
					'icon' => new Icon('content_copy'),
					'text' => 'Duplicate Topic'
			]) : NULL,

				$this->right->delete ? '<sep>' : NULL,
				$this->right->delete ? new Button([
					'type' => 'link',
					'class' => 'sg-action',
					'href' => Url::link('paper/' . $this->nodeId . '/delete'),
					'rel' => '#main',
					'icon' => new Icon('delete'),
					'text' => 'Delete topic'
				]) : NULL,
			], // children
		]);
	}
}
?>