<?php
/**
* Org :: App Bar Navigator Widget
* Created 2021-10-13
* Modify  2021-10-13
*
* @param Array $orgInfo
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
				// '<a href="'.url('codi').'"><i class="icon -material">home</i><span>หน้าหลัก</span></a>',
			],
		]);

		if ($orgId) {
			$children['info'] = new Row([
				'class' => 'info',
				'children' => [
					'<a href="'.url('org/'.$this->orgId.'/info.home').'"><i class="icon -material">home</i><span>หน้าหลัก</span></a>',
					'<a href="'.url('org/'.$this->orgId.'/info.action').'"><i class="icon -material">directions_run</i><span>กิจกรรม</span></a>',
					'<a href="'.url('org/'.$this->orgId.'/info.student').'"><i class="icon -material">school</i><span>นักเรียน</span></a>',
					// '<a class="-disabled" href="'.url('org/'.$this->orgId).'"><i class="icon -material">directions_run</i><span>รายงานผล</span></a>',
					'<a href="'.url('org/'.$this->orgId.'/info.health').'"><i class="icon -material">family_restroom</i><span>ภาวะโภชนาการ</span></a>',
					'<a href="'.url('org/'.$this->orgId.'/info.report').'"><i class="icon -material">assessment</i><span>รายงาน</span></a>',
					// '<a class="-disabled" href="'.url('org/'.$this->orgId).'"><i class="icon -material">assessment</i><span>แบบประเมิน</span></a>',
					// '<a class="sg-action" href="'.url('org/'.$this->orgId.'/info.officer').'" data-rel="box" data-width="480"><i class="icon -material">group_add</i><span>สมาชิก</span></a>',
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
	}
}
?>