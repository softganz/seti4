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
		$children['main'] = new Row([
			'class' => 'main',
			'children' => [
				'<a href="'.url('codi').'"><i class="icon -material">home</i><span>หน้าหลัก</span></a>',
			],
		]);

		if ($orgId) {
			$children['info'] = new Row([
				'class' => 'info',
				'children' => [
					'<a href="'.url('org/'.$this->orgId.'/info.planning').'"><i class="icon -material">device_hub</i><span>แผนงาน</span></a>',
					'<a href="'.url('org/'.$this->orgId.'/info.follow').'"><i class="icon -material">directions_run</i><span>ติดตามโครงการ</span></a>',
					'<a class="sg-action" href="'.url('org/'.$this->orgId.'/info.officer').'" data-rel="box" data-width="480"><i class="icon -material">group_add</i><span>สมาชิก</span></a>',
				],
			]);
		} else {
			$children['info'] = new Row([
				'class' => 'info',
				'children' => [
					// '<a href="'.url('org/meeting').'"><i class="icon -material">assignment</i><span>กิจกรรม</span></a>',
					// '<a href="'.url('org/meeting').'"><i class="icon -material">people</i><span>สมาชิก</span></a>',
					// '<a href="'.url('org/meeting').'"><i class="icon -material">assessment</i><span>รายงาน</span></a>',
				],
			]);
		}

		if (user_access('administrator orgs')) {
			$children['info']->children[] = '<a href="'.url('org/admin').'"><i class="icon -material">settings</i><span>ผู้จัดการระบบ</span></a>';
		}

		return new Widget([
			'children' => [
				'main' => new Row([
					'class' => '-main',
					'children' => $children,
				]),
			],
		]);



		// return new Widget([
		// 	'children' => [
		// 		'main' => new Row([
		// 			'class' => '-main',
		// 			'children' => [
		// 				'<a href="'.url('org/'.$this->orgId).'"><i class="icon -material">home</i><span>หน้าหลัก</span></a>',
		// 				'<a href="'.url('org/'.$this->orgId.'/planning').'"><i class="icon -material">device_hub</i><span>แผนงาน</span></a>',
		// 				'<a href="'.url('org/'.$this->orgId.'/project').'"><i class="icon -material">directions_run</i><span>ติดตามโครงการ</span></a>',
		// 				'<a class="sg-action" href="'.url('org/'.$this->orgId.'/info.officer').'" data-rel="box" data-width="480"><i class="icon -material">group_add</i><span>สมาชิก</span></a>',
		// 			],
		// 		]),
		// 		// 'info' => new Row([
		// 		// 	'class' => '-info',
		// 		// 	'children' => [
		// 		// 		'<a href="'.url('org/'.$this->orgId.'/project').'"><i class="icon -material">directions_run</i><span>ติดตามโครงการ 10</span></a>',
		// 		// 		'<a href="'.url('org/'.$this->orgId.'/project').'"><i class="icon -material">directions_run</i><span>ติดตามโครงการ 11</span></a>',
		// 		// 		'<a href="'.url('org/'.$this->orgId.'/project').'"><i class="icon -material">directions_run</i><span>ติดตามโครงการ 12</span></a>',
		// 		// 		'<a href="'.url('org/'.$this->orgId.'/project').'"><i class="icon -material">directions_run</i><span>ติดตามโครงการ 13</span></a>',
		// 		// 		'<a href="'.url('org/'.$this->orgId.'/project').'"><i class="icon -material">directions_run</i><span>ติดตามโครงการ 14</span></a>',
		// 		// 	],
		// 		// ]),
		// 	],
		// 	// 'navigator' => new Row([
		// 	// 	'children' => [
		// 	// 		'<a href="'.url('org/'.$this->orgId.'/project').'"><i class="icon -material">directions_run</i><span>ติดตามโครงการ 10</span></a>',
		// 	// 		'<a href="'.url('org/'.$this->orgId.'/project').'"><i class="icon -material">directions_run</i><span>ติดตามโครงการ 11</span></a>',
		// 	// 		'<a href="'.url('org/'.$this->orgId.'/project').'"><i class="icon -material">directions_run</i><span>ติดตามโครงการ 12</span></a>',
		// 	// 		'<a href="'.url('org/'.$this->orgId.'/project').'"><i class="icon -material">directions_run</i><span>ติดตามโครงการ 13</span></a>',
		// 	// 		'<a href="'.url('org/'.$this->orgId.'/project').'"><i class="icon -material">directions_run</i><span>ติดตามโครงการ 14</span></a>',
		// 	// 	],
		// 	// ]),
		// ]);
	}
}
?>