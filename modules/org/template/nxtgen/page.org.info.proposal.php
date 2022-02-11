<?php
/**
* Org :: Proposal
* Created 2021-11-17
* Modify  2021-11-17
*
* @param Object $orgInfo
* @return Widget
*
* @usage org/{id}/info.proposal
*/

$debug = true;

import('widget:org.nav.php');
import('model:project.proposal.php');

class OrgInfoProposal extends Page {
	var $orgId;
	var $right;
	var $orgInfo;

	function __construct($orgInfo) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
		$this->right = (Object) [
			'accessProposalInfo' => is_admin('project'),
		];
	}

	function build() {
		$isAdmin = $this->orgInfo->is->orgadmin;
		$isEdit = $isAdmin || $this->orgInfo->RIGHT & _IS_OFFICER;

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ข้อเสนอหลักสูตร : '.$this->orgInfo->name,
				'navigator' => new OrgNavWidget($this->orgInfo),
			]), // AppBar
			'children' => [
				new Table([
					'thead' => [
						'',
						'title -fill' => 'ชื่อหลักสูตร',
						'year -date' => 'ปีงบประมาณ',
						'type -center -nowrap' => 'ประเภท',
						// 'budget -money -nowrap' => 'งบประมาณ',
						'status -center -nowrap' => 'สถานะ'
					],
					'rows' => array_map(
						function($item) {
							$status = [1=>'กำลังพัฒนา',2=>'พิจารณา',3=>'ปรับแก้',5=>'ผ่าน',8=>'ไม่ผ่าน',9=>'ยกเลิก','10'=>'ดำเนินการ'];
							$rightToViewInfo = $this->right->accessProposalInfo
								|| (i()->ok && (i()->uid == $item->userId) || in_array(i()->uid, explode(',', $item->topicUsers)));
							return [
								'<img class="profile-photo" src="'.model::user_photo($item->username).'" />',
								($rightToViewInfo ? '<a href="'.url('project/proposal/'.$item->projectId).'">'.SG\getFirst($item->title, '<em>ไม่มีชื่อ</em>').'</a>' : SG\getFirst($item->title, '<em>ไม่มีชื่อ</em>'))
								. '<br /><em>โดย '.$item->ownerName.'</em>',
								$item->pryear+543,
								$item->parentTitle,
								// number_format($item->budget,2),
								$status[$item->status],
							];
						},
						ProjectProposalModel::items(
							['orgId' => $this->orgId],
							['debug' => false, 'includeChildOrg' => true, 'order' => 'd.`pryear` DESC, d.`tpid`', 'sort' => 'DESC']
						)->items
					), // rows
				]), // Table
				// $isEdit ? new FloatingActionButton([
				// 	'children' => [
				// 		'<a class="sg-action btn -floating -circle" href="'.url('project/create/',array('orgid' => $this->orgId)).'" data-rel="box" data-width="480"><i class="icon -material">add</i><span>สร้างโครงการ</span></a>'
				// 	],
				// ]) : NULL,
			],
		]);
	}
}
?>