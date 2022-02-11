<?php
/**
* Project :: Next Gen Proposal Wait for Approve
* Created 2021-10-25
* Modify  2021-10-25
*
* @return Widget
*
* @usage project/nxt/proove
*/

$debug = true;

import('model:project.proposal.php');

class ProjectNxtProove extends Page {
	var $courseId;
	var $year;
	var $orgId;
	var $title;
	var $status;
	var $showStatus = '2,3,5';
	VAR $right;

	function __construct() {
		$this->courseId = post('course');
		$this->year = post('year');
		$this->orgId = post('org');
		$this->title = post('title');
		$this->status = post('status');
		$this->right = (Object) [
			'accessProposalInfo' => is_admin('project'),
		];
	}

	function build() {
		$isCreateProposal = i()->ok;

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'พิจารณาหลักสูตร',
				'leading' => '<i class="icon -material">rule</i>',
				'navigator' => new Form([
					'action' => url('project/nxt/proove'),
					'method' => 'get',
					'class' => 'sg-form form-report',
					'rel' => '#main',
					'children' => [
						'status' => [
							'type' => 'select',
							'value' => $this->status,
							'onChange' => '$(this).closest("form").submit()',
							'options' => ['' => '=ทุกสถานนะ=', 2 => 'กำลังพิจารณา', 3 => 'แก้ไข', 5 => 'ผ่าน'],
						],
						'course' => [
							'type' => 'select',
							'onChange' => '$(this).closest("form").submit()',
							'value' => $this->courseId,
							'options' => ['' => '=ทุกหลักสูตร='] + mydb::select(
								'SELECT p.`tpid` `projectId`, t.`title` `projectTitle`
								FROM %project% p
									LEFT JOIN %topic% t ON t.`tpid` = p.`tpid`
								WHERE p.`tpid` IN ( :projectId )
								GROUP BY CONVERT(`projectTitle` USING `tis620`) ASC;
								-- {key: "projectId", value: "projectTitle"}',
								[':projectId' => 'SET:'.cfg('project')->nxt->course]
							)->items,
						],
						'year' => [
							'type' => 'select',
							'value' => $this->year,
							'onChange' => '$(this).closest("form").submit()',
							'options' => ['' => '=ทุกปี='] + mydb::select(
								'SELECT `pryear`, `pryear` + 543 `bcyear`
								FROM %project_dev% dev
								WHERE dev.`status` IN ( :status )
								GROUP BY `pryear`
								ORDER BY `pryear` DESC;
								-- {key: "pryear", value: "bcyear"}',
								[':status' => 'SET:'.$this->showStatus]
							)->items,
						],
						'org' => [
							'type' => 'select',
							'onChange' => '$(this).closest("form").submit()',
							'value' => $this->orgId,
							'options' => ['' => '=ทุกสถาบัน='] + mydb::select(
								'SELECT t.`orgId`, org.`name` `orgName`
								FROM %project_dev% dev
									LEFT JOIN %topic% t ON t.`tpid` = dev.`tpid`
									LEFT JOIN %db_org% org ON org.`orgId` = t.`orgId`
								WHERE dev.`status` IN ( :status )
								GROUP BY CONVERT(`orgName` USING `tis620`) ASC;
								-- {key: "orgId", value: "orgName"}',
								[':status' => 'SET:'.$this->showStatus]
							)->items,
						],
						'title' => [
							'type' => 'text',
							'placeholder' => 'ระบุชื่อหลักสูตร',
						],
						'go' => ['type' => 'button', 'value' => '<i class="icon -material">search</i>'],
						// new DebugMsg(mydb()->_query),
						// date('@H:i:s')
					],
				]), // Form
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new ScrollView([
						'child' => new Table([
							'thead' => [
								'หลักสูตร',
								'มหาวิทยาลัย',
								'year -date' => 'ปีงบประมาณ',
								'type -center' => 'ประเภท',
								'code -center' => 'รหัส',
								'status -center -nowrap' => 'สถานะ',
								'created -date' => 'วันที่เสนอ'
							],
							'children' => array_map(
								function($item) {
									$status = [1=>'กำลังพัฒนา',2=>'พิจารณา',3=>'ปรับแก้',5=>'ผ่าน',8=>'ไม่ผ่าน',9=>'ยกเลิก','10'=>'ดำเนินการ'];
									$rightToViewInfo = $this->right->accessProposalInfo || (i()->ok && i()->uid == $item->userId);
									return [
											($rightToViewInfo ? '<a href="'.url('project/proposal/'.$item->tpid).'">'.$item->title.'</a>' : $item->title)
											. '<br /><em>'.$item->ownerName.'</em>',
											$item->orgName,
											$item->pryear + 543,
											$item->parentTitle,
											$item->refNo,
											$status[$item->status],
											sg_date($item->created, 'ว ดด ปปปป'),
										];
								},
								ProjectProposalModel::items(
									[
										'childOf' => cfg('project')->nxt->course,
										'status' => SG\getFirst($this->status,'2,3'),
										'budgetYear' => $this->year,
										'orgId' => $this->orgId,
										'childOf' => $this->courseId,
										'title' => $this->title,
									],
									[
										'items' => '*',
										'sort' => 'DESC',
										'debug' => false,
									]
								)->items
								// mydb::select(
								// 	'SELECT t.`tpid`, t.`title`, t.`created`, t.`parent`, parent.`title` `parentTitle`, d.`status`, o.`name` `orgName`, d.`refNo`
								// 	FROM %project_dev% d
								// 		LEFT JOIN %topic% t USING(`tpid`)
								// 		LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
								// 		LEFT JOIN %topic% parent ON parent.`tpid` = t.`parent`
								// 	WHERE d.`status` IN (2,3)
								// 	ORDER BY t.`tpid` DESC'
								// )->items
							), // children
						]), // Table
					]), // ScrollView
				], // children
			]), // Widget
		]);
	}
}
?>