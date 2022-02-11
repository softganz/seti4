<?php
/**
* Project :: Next Gen Proposal Home Page
* Created 2021-09-24
* Modify  2021-09-24
*
* @return Widget
*
* @usage project/proposal
*/

$debug = true;

import('model:org.php');

class ProjectProposalHome extends Page {
	var $courseId;
	var $year;
	var $orgId;
	var $title;
	var $status = '1';
	var $right;

	function __construct() {
		$this->courseId = post('course');
		$this->year = post('year');
		$this->orgId = post('org');
		$this->title = post('title');
		$this->right = (Object) [
			'accessInfo' => is_admin('project'),
		];
	}

	function build() {
		$myOrgList = OrgModel::items(['userId' => 'memberShip']);
		$isCreateProposal = i()->ok && $myOrgList;

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'เสนอหลักสูตร',
				'leading' => '<i class="icon -material">tune</i>',
				'navigator' => new Form([
					'action' => url('project/proposal'),
					'method' => 'get',
					'class' => 'sg-form form-report',
					'rel' => '#main',
					'children' => [
						'course' => [
							'type' => 'select',
							'onChange' => '$(this).closest("form").submit()',
							'value' => $this->courseId,
							'options' => ['' => '=ทุกหลักสูตร=='] + mydb::select(
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
							'options' => ['' => '=ทุกปี=='] + mydb::select(
								'SELECT `pryear`, `pryear` + 543 `bcyear`
								FROM %project_dev%
								GROUP BY `pryear`
								ORDER BY `pryear` DESC;
								-- {key: "pryear", value: "bcyear"}'
							)->items,
						],
						'org' => [
							'type' => 'select',
							'onChange' => '$(this).closest("form").submit()',
							'value' => $this->orgId,
							'options' => ['' => '=ทุกสถาบัน=='] + mydb::select(
								'SELECT t.`orgId`, org.`name` `orgName`
								FROM %project_dev% dev
									LEFT JOIN %topic% t ON t.`tpid` = dev.`tpid`
									LEFT JOIN %db_org% org ON org.`orgId` = t.`orgId`
								WHERE dev.`status` IN ( :status )
								GROUP BY CONVERT(`orgName` USING `tis620`) ASC;
								-- {key: "orgId", value: "orgName"}',
								[':status' => $this->status]
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
								// 'status -center -nowrap' => 'สถานะ',
								'created -date' => 'วันที่เสนอ'
							],
							'children' => array_map(
								function($item) {
									$status = [1=>'กำลังพัฒนา',2=>'พิจารณา',3=>'ปรับแก้',5=>'ผ่าน',8=>'ไม่ผ่าน',9=>'ยกเลิก','10'=>'ดำเนินการ'];
									$rightToViewInfo = $this->right->accessInfo || (i()->ok && i()->uid == $item->userId);
									return [
											($rightToViewInfo ? '<a href="'.url('project/proposal/'.$item->tpid).'">'.SG\getFirst($item->title,'<em>ไม่มีชื่อ</em>').'</a>' : SG\getFirst($item->title,'<em>ไม่มีชื่อ</em>'))
											. '<br /><em>โดย '.$item->ownerName.'</em>',
											$rightToViewInfo ? '<a href="'.url('org/'.$item->orgId.'/info.proposal').'">'.$item->orgName.'</a>' : $item->orgName,
											$item->pryear + 543,
											$item->parentTitle,
											// $status[$item->status],
											sg_date($item->created, 'ว ดด ปปปป'),
										];
								},
								ProjectProposalModel::items(
									[
										'childOf' => cfg('project')->nxt->course,
										'status' => $this->status,
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
								// 	'SELECT t.`tpid`, t.`title`, t.`created`, t.`parent`, parent.`title` `parentTitle`, d.`status`, o.`name` `orgName`
								// 	FROM %project_dev% d
								// 		LEFT JOIN %topic% t USING(`tpid`)
								// 		LEFT JOIN %db_org% o ON o.`orgid` = t.`orgid`
								// 		LEFT JOIN %topic% parent ON parent.`tpid` = t.`parent`
								// 	WHERE d.`status` IN  (1,3)
								// 	ORDER BY t.`tpid` DESC'
								// )->items
							), // children
						]), // Table
					]),
					$isCreateProposal ? new FloatingActionButton([
						'style' => 'max-width: 220px;',
						'children' => [
							'<a class="sg-action btn -floating -fill" href="'.url('project/proposal/new/18').'" data-rel="box" data-width="480"><i class="icon -material">add</i><span>เสนอหลักสูตร Degree</span></a>',
							'<a class="sg-action btn -floating -fill" href="'.url('project/proposal/new/19').'" data-rel="box" data-width="480"><i class="icon -material">add</i><span>เสนอหลักสูตร Non-Degree</span></a>',
							// '<a class="sg-action btn -floating -fill" href="'.url('project/proposal/new/20').'" data-rel="box" data-width="480"><i class="icon -material">add</i><span>เสนอหลักสูตร อื่น ๆ</span></a>',
						],
					]) : NULL,
				],
			]),
		]);
	}
}
?>