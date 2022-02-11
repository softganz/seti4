<?php
/**
* Project Nxt :: Course List
* Created 2021-10-31
* Modify  2021-10-31
*
* @return Widget
*
* @usage project/nxt/course
*/

$debug = true;

import('model:project.follow.php');

class ProjectNxtCourse extends Page {
	var $courseId;
	var $year;
	var $orgId;
	var $title;
	var $status = 'proceed';

	function __construct() {
		$this->courseId = post('course');
		$this->year = post('year');
		$this->orgId = post('org');
		$this->title = post('title');
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'รายชื่อหลักสูตร',
				'leading' => '<i class="icon -material">directions_run</i>',
				'navigator' => new Form([
					'action' => url('project/nxt/course'),
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
							// 'onChange' => 'form.submit()',
							'options' => ['' => '=ทุกปี=='] + mydb::select(
								'SELECT `pryear`, `pryear` + 543 `bcyear`
								FROM %project% p
								WHERE p.`project_status` IN ( 1 )
								GROUP BY `pryear`
								ORDER BY `pryear` DESC;
								-- {key: "pryear", value: "bcyear"}',
								[':status' => 'SET:'.$this->status]
							)->items,
						],
						'org' => [
							'type' => 'select',
							'onChange' => '$(this).closest("form").submit()',
							'value' => $this->orgId,
							'options' => ['' => '=ทุกสถาบัน=='] + mydb::select(
								'SELECT t.`orgId`, org.`name` `orgName`
								FROM %project% p
									LEFT JOIN %topic% t ON t.`tpid` = p.`tpid`
									LEFT JOIN %db_org% org ON org.`orgId` = t.`orgId`
								WHERE p.`project_status` IN ( 1 ) AND t.`parent` IN ( :parent )
								GROUP BY CONVERT(`orgName` USING `tis620`) ASC;
								-- {key: "orgId", value: "orgName"}',
								[
									':status' => 'SET:'.$this->status,
									':parent' => 'SET:'.cfg('project')->nxt->course,
								]
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
					new Table([
						'thead' => [
							'หลักสูตร',
							'มหาวิทยาลัย',
							'year -date' => 'ปีงบประมาณ',
							'type -center' => 'ประเภท',
							'created -date' => 'วันที่อนุมัติ'
						],
						'children' => array_map(
							function($item) {
								return [
										'<a href="'.url('project/'.$item->projectId).'">'.$item->title.'</a>'
										. '<br /><em>โดย '.$item->ownerName.'</em>',
										$item->orgName,
										$item->pryear + 543,
										$item->parentTitle,
										$item->date_approve ? sg_date($item->date_approve, 'ว ดด ปปปป') : '',
									];
							},
							ProjectFollowModel::items(
								[
									'status' => 'process',
									'projectType' => 'โครงการ',
									'childOf' => SG\getFirst($this->courseId, cfg('project')->nxt->course),
									'budgetYear' => $this->year,
									'orgId' => $this->orgId,
									'title' => $this->title,
								],
								[
									'items' => '*',
									'sort' => 'DESC',
									'debug' => false,
								]
							)->items
						), // children
					]), // Table
				], // children
			]), // Widget
		]);
	}
}
?>