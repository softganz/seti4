<?php
/**
* Org Widget :: Navigator
* Created 2021-08-01
* Modify  2021-08-01
*
* @param Array $args
* @return Widget
*
* @usage new NameWidget([])
*/

class OrgNavWidget extends Widget {
	var $orgId;
	var $orgInfo;

	function __construct($orgInfo = []) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
	}

	function build() {
		$orgId = $this->orgId;
		$orgConfig = cfg('org');

		$children = [];
		$children['main'] = new Row([
			'class' => 'main',
			'children' => [
				'<a href="'.url('org').'"><i class="icon -material">home</i><span>หน้าหลัก</span></a>',
				'<a href="'.url('org/my').'"><i class="icon -material">person</i><span>จัดการองค์กร</span></a>',
			],
		]);

		if ($orgId) {
			$children['info'] = new Row([
				'tagName' => 'ul',
				'childTagName' => 'li',
				'class' => '-info',
				'children' => (function($orgConfig) {
					$childrens = [];

					// Show button in follow navigator config
					foreach (explode(',', $orgConfig->navigatorUse) as $navKey) {
						$menuItem = $orgConfig->navigator->{$navKey};
						if ($menuItem->access) {
							if (!defined($menuItem->access)) continue;
							else if (!($this->orgInfo->RIGHT & constant($menuItem->access))) continue;
						}
						$childrens[$navKey] = '<a href="'.url('org/'.$this->orgId.($menuItem->url ? '/'.$menuItem->url : '')).'" title="'.$menuItem->title.'" '.sg_implode_attr($menuItem->attribute).'><i class="icon -material">'.$menuItem->icon.'</i><span>'.$menuItem->label.'</span></a>';
					}

					// Show dashboard button
					// if ($this->right->access) {
					// 	$childrens['dashboard'] = '<a href="'.url('project/'.$orgId.'/info.dashboard').'" rel="nofollow" title="แผงควบคุมโครงการ"><i class="icon -material">dashboard</i><span>แผงควบคุม</span></a>';
					// }

					// Show print button
					if ($this->options->showPrint) {
						$childrens[] = '<sep>';
						$childrens['print'] = '<a href="javascript:window.print()"><i class="icon -material">print</i><span>พิมพ์</span></a>';
					}

					return $childrens;
				})($orgConfig),
			]);
		}

		// if ($orgId) {
		// 	$children['info'] = new Row([
		// 		'class' => 'info',
		// 		'children' => [
		// 			'<a href="'.url('org/'.$orgId.'/info.view').'"><i class="icon -material">account_balance</i><span>ข้อมูลองค์กร</span></a>',
		// 			'<a href="'.url('org/'.$this->orgId.'/info.planning').'"><i class="icon -material">device_hub</i><span>แผนงาน</span></a>',
		// 			'<a href="'.url('org/'.$this->orgId.'/info.follow').'"><i class="icon -material">directions_run</i><span>โครงการ</span></a>',
		// 			'<a href="'.url('org/'.$orgId.'/meeting').'"><i class="icon -material">assignment</i><span>กิจกรรม</span></a>',
		// 			'<a href="'.url('org/'.$orgId.'/docs.o').'"><i class="icon -material">assignment</i><span>หนังสือเข้า/ออก</span></a>',
		// 			'<a href="'.url('org/'.$orgId.'/mapping').'"><i class="icon -material">people</i><span>Mapping</span></a>',
		// 			'<a href="'.url('org/'.$orgId.'/member').'"><i class="icon -material">assessment</i><span>สมาชิก</span></a>',
		// 			'<a href="'.url('org/'.$orgId.'/report').'"><i class="icon -material">assessment</i><span>รายงาน</span></a>',
		// 		],
		// 	]);
		// } else {
		// 	$children['info'] = new Row([
		// 		'class' => 'info',
		// 		'children' => [
		// 			'<a href="'.url('org/meeting').'"><i class="icon -material">assignment</i><span>กิจกรรม</span></a>',
		// 			'<a href="'.url('org/meeting').'"><i class="icon -material">people</i><span>สมาชิก</span></a>',
		// 			'<a href="'.url('org/meeting').'"><i class="icon -material">assessment</i><span>รายงาน</span></a>',
		// 		],
		// 	]);
		// }

		if (user_access('administrator orgs')) {
			$children['admin'] = new Row([
				'class' => 'admin',
				'children' => [
					'<a href="'.url('org/admin').'"><i class="icon -material">settings</i><span>ผู้จัดการระบบ</span></a>',
				],
			]);
		}

		// '<a href="'.url('org/'.$this->orgId.'/planning').'"><i class="icon -material">device_hub</i><span>แผนงาน</span></a>',
		// '<a href="'.url('org/'.$this->orgId.'/proposal').'"><i class="icon -material">nature_people</i><span>ข้อเสนอโครงการ</span></a>',
		// '<a href="'.url('org/'.$this->orgId.'/project').'"><i class="icon -material">directions_run</i><span>ติดตามโครงการ</span></a>',

		return new Widget([
			'children' => [
				'main' => new Row([
					'class' => '-main',
					'children' => $children,
				]),
				// 'info' => new Row([
				// 	'class' => '-info',
				// 	'children' => [
				// 		'<a href="'.url('org/'.$this->orgId.'/project').'"><i class="icon -material">directions_run</i><span>ติดตามโครงการ 10</span></a>',
				// 		'<a href="'.url('org/'.$this->orgId.'/project').'"><i class="icon -material">directions_run</i><span>ติดตามโครงการ 11</span></a>',
				// 		'<a href="'.url('org/'.$this->orgId.'/project').'"><i class="icon -material">directions_run</i><span>ติดตามโครงการ 12</span></a>',
				// 		'<a href="'.url('org/'.$this->orgId.'/project').'"><i class="icon -material">directions_run</i><span>ติดตามโครงการ 13</span></a>',
				// 		'<a href="'.url('org/'.$this->orgId.'/project').'"><i class="icon -material">directions_run</i><span>ติดตามโครงการ 14</span></a>',
				// 	],
				// ]),
			],
			// 'navigator' => new Row([
			// 	'children' => [
			// 		'<a href="'.url('org/'.$this->orgId.'/project').'"><i class="icon -material">directions_run</i><span>ติดตามโครงการ 10</span></a>',
			// 		'<a href="'.url('org/'.$this->orgId.'/project').'"><i class="icon -material">directions_run</i><span>ติดตามโครงการ 11</span></a>',
			// 		'<a href="'.url('org/'.$this->orgId.'/project').'"><i class="icon -material">directions_run</i><span>ติดตามโครงการ 12</span></a>',
			// 		'<a href="'.url('org/'.$this->orgId.'/project').'"><i class="icon -material">directions_run</i><span>ติดตามโครงการ 13</span></a>',
			// 		'<a href="'.url('org/'.$this->orgId.'/project').'"><i class="icon -material">directions_run</i><span>ติดตามโครงการ 14</span></a>',
			// 	],
			// ]),
		]);
	}
}
?>