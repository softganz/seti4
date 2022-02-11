<?php
/**
* Module :: Description
* Created 2021-08-01
* Modify  2021-08-01
*
* @param Array $args
* @return Widget
*
* @usage new NameWidget([])
*/

$debug = true;

class OrgNavWidget extends Widget {
	var $orgId;
	var $orgInfo;

	function __construct($orgInfo = []) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
	}

	function build() {
		$orgId = $this->orgId;

		$children = [];
		// $children['main'] = new Row([
		// 	'class' => 'main',
		// 	'children' => [
		// 		'<a href="'.url('org/'.$this->orgId).'"><i class="icon -material">home</i><span>หน้าหลัก</span></a>',
		// 		// '<a href="'.url('nxt').'"><i class="icon -material">home</i><span>หน้าหลัก</span></a>',
		// 	],
		// ]);

		if ($orgId) {
			$children = [
				// '<a href="'.url('org/'.$this->orgId).'"><i class="icon -material">home</i><span>หน้าหลัก</span></a>',
				'<a href="'.url('org/'.$this->orgId.'/info.proposal').'"><i class="icon -material">school</i><span>ข้อเสนอหลักสูตร</span></a>',
				'<a href="'.url('org/'.$this->orgId.'/info.follow').'"><i class="icon -material">school</i><span>ติดตามหลักสูตร</span></a>',
				// '<a class="sg-action" href="'.url('org/'.$this->orgId.'/info.officer').'" data-rel="box" data-width="480"><i class="icon -material">group_add</i><span>สมาชิก</span></a>',
			];
		} else {
			// $children['info'] = new Row([
			// 	'class' => 'info',
			// 	'children' => [
			// 	],
			// ]);
		}

		if (user_access('administrator orgs')) {
			// $children['info']->children[] = '<a href="'.url('org/admin').'"><i class="icon -material">settings</i><span>ผู้จัดการระบบ</span></a>';
		}

		return new Widget([
			'children' => [
				'main' => new Row([
					'class' => '-main',
					'children' => [
						'<a href="'.url('org/'.$this->orgId).'"><i class="icon -material">home</i><span>หน้าหลัก</span></a>',
						// '<a href="'.url('nxt').'"><i class="icon -material">home</i><span>หน้าหลัก</span></a>',
					], // children
				]), // Row
				'info' => new Row([
					'class' => '-info',
					'children' => $children,
				]),
			],
		]);
	}
}
?>