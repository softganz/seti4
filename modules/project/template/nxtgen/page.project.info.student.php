<?php
/**
* Project :: Follow Students List Information
* Created 2021-11-10
* Modify  2021-11-10
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/proposal/{id}/info.student
*/

$debug = true;

import('model:lms.student.php');
import('model:file.php');
import('widget:project.info.appbar.php');

class ProjectInfoStudent extends Page {
	var $projectId;
	var $serieNo;
	var $right;
	var $projectInfo;

	function __construct($projectInfo, $serieNo) {
		$this->projectId = $projectInfo->projectId;
		$this->serieNo = $serieNo;
		$this->projectInfo = $projectInfo;
		$this->right = (Object) ['edit' => $projectInfo->RIGHT & _IS_EDITABLE];
	}

	function build() {
		if ($this->right->edit && post('student') == 'new') {
			return $this->formStudent();
		} else if ($this->right->edit && is_numeric(post('student'))) {
			$studentInfo = StudentModel::get(post('student'));
			return $this->formStudent($studentInfo);
		}

		$attachFile = mydb::select(
			'SELECT * FROM %topic_files% WHERE `tpid` = :projectId AND `tagName` = :tagName LIMIT 1',
			[':projectId' => $this->projectId, ':tagName' => 'project,student']
		);

		$attachFile = FileModel::get(FileModel::items([
			'nodeId' => $this->projectId,
			'tagName' => 'project,student',
			'refId' => $this->serieNo,
		])->items[0]->id);
		// $attachFile = FileModel::get(1110);
		// debugMsg($attachFile, '$attachFile');
		return new Scaffold([
			'appBar' => new ProjectInfoAppBarWidget(
				$this->projectInfo,
				[
					'trailing' => $attachFile->fileId ? new DropBox([
						'children' => [
							'<a href="'.$attachFile->property->src.'" target="_blank"><i class="icon -material">download</i><span>ดาวน์โหลด</span></a>',
							'<sep>',
							'<a class="sg-action" href="'.url('project/info/api/'.$this->projectId.'/docs.delete/'.$attachFile->fileId).'" data-rel="notify" data-done="reload" data-title="ลบไฟล์รายชื่อนักศึกษา" data-confirm="ต้องการลบลบไฟล์รายชื่อนักศึกษา กรุณายืนยัน?"><i class="icon -material">delete</i><span>ลบไฟล์รายชื่อนักศึกษา</span></a>',
						], // children
					]) : NULL, // DropBox
				]
			),
			'body' => new Container([
				'children' => [
					$this->right->edit ? new Nav([
						'mainAxisAlignment' => 'end',
						'class' => '-page -sg-paddingnorm',
						'children' => [
							// $attachFile->fileId ? '<a class="btn -link" href=""><i class="icon -material">download</i><span>ดาวน์โหลด</span></a>' : NULL,
							'<form class="sg-form -upload" method="post" enctype="multipart/form-data" action="'.url('project/info/api/'.$this->projectId.'/docs.upload').'" data-rel="notify" data-done="reload">'
								. '<input type="hidden" name="removeOldFile" value="Yes">'
								. '<input type="hidden" name="document[fid]" value="'.$attachFile->fileId.'">'
								. '<input type="hidden" name="document[prename]" value="project_'.$this->projectId.'_student_">'
								. '<input type="hidden" name="document[tagname]" value="project,student">'
								. '<input type="hidden" name="document[refid]" value="'.$this->serieNo.'">'
								. '<span class="btn -link fileinput-button" style="padding: 6px;"><i class="icon -material">upload</i><span></span>'.($attachFile->fileId ? 'เปลี่ยนไฟล์รายชื่อนักศึกษา' : 'อัพโหลดรายชื่อนักศึกษา').'<input type="file" name="document" class="inline-upload" accept="image/*;capture=camcorder" onChange=\'$(this).closest(form).submit(); return false;\' /></span>'
								. '</form>',
							'<a class="btn -link" href="'.url('project/'.$this->projectId.'/info.form.getmoney/'.$this->serieNo).'"><i class="icon -material">print</i><span>แบบขอรับการสนับสนุน</span></a>',
							'<a class="sg-action btn -primary" href="'.url('project/'.$this->projectId.'/info.student/'.$this->serieNo,['student' => 'new']).'" data-rel="box" data-width="320"><i class="icon -material">group_add</i><span>เพิ่มนักศึกษา</span></a>',
							// $attachFile->fileId ? new DropBox([
							// 	'children' => [
							// 		'<a><i class="icon -material">delete</i><span>ลบไฟล์รายชื่อนักศึกษา</span></a>',
							// 	], // children
							// ]) : NULL, // DropBox
						],
					]) : NULL, // Nav

					new ScrollView([
						'child' => new Table([
							'thead' => [
								'no' => '',
								'code -center' => 'รหัสประจำตัวนักศึกษา',
								'name -nowrap' => 'ชื่อ - สกุล',
								'cid -center' => 'เลขประจำตัวประชาชน',
								'status -center' => 'สถานภาพ',
								'position -center' => 'ตำแหน่งงาน',
								'orgname -hover-parent' => 'หน่วยงานต้นสังกัด',
							],
							'children' => (function() {
								$dbs = mydb::select(
									'SELECT
									s.*, p.`prename`, p.`name`, p.`lname`, p.`cid`
									, p.`position`, p.`orgId`, o.`name` `orgName`
									FROM %lms_student% s
										LEFT JOIN %db_person% p ON p.`psnId` = s.`psnId`
										LEFT JOIN %db_org% o ON o.`orgId` = p.`orgId`
									WHERE s.`projectId` = :projectId AND `serieNo` = :serieNo
									ORDER BY CONVERT(p.`name` USING tis620) ASC, CONVERT(p.`lname` USING tis620) ASC',
									[':projectId' => $this->projectId, ':serieNo' => $this->serieNo]
								);
								$rows = [];
								$no = 0;
								foreach ($dbs->items as $item) {
									$menu = new Nav([
										'class' => 'nav -icons -hover',
										'children' => [
											$this->right->edit ? '<a class="sg-action" href="'.url('project/'.$this->projectId.'/info.student/'.$item->serieNo, ['student' => $item->studentId]).'" data-rel="box" data-width="320"><i class="icon -material">edit</i></a>' : NULL,
											$this->right->edit ? '<a class="sg-action" href="'.url('project/info/nxt/api/'.$this->projectId.'/student.remove/'.$item->studentId).'" data-rel="notify" data-title="ลบชื่อนักศึกษาออกจากระบบ" data-done="remove:parent tr" data-confirm="ต้องการลบชื่อนักศึกษา และข้อมูลส่วนบุคคลของนักศึกษา กรุณายืนยัน?"><i class="icon -material">cancel</i></a>' : NULL,
										], // children
									]);
									$rows[] = [
										++$no,
										$item->studentCode,
										$item->prename.$item->name.' '.$item->lname,
										$item->cid,
										$this->right->edit ? '<a class="sg-action btn -link" href="'.url('project/'.$this->projectId.'/info.student.status/'.$item->studentId).'" data-rel="box" data-width="320">'.$item->status.'</a>' : $item->status,
										$item->position,
										$item->orgName
										. $menu->build(),
									];
								}
								return $rows;
							})(), // children
						]), // Table
					]), // ScrollView

					// $this->formTemplate(),

				], // children
			]), // Container,
		]);
	}

		function formStudent($data = NULL) {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'นักศึกษา',
				'boxHeader' => true,
			]),
			'body' => new Form([
				'class' => 'sg-form',
				'action' => url('project/info/nxt/api/'.$this->projectId.'/student.save/'.$this->serieNo),
				'rel' => 'notify',
				'done' => 'load | close',
				'checkValid' => true,
				'children' => [
					'studentId' => ['type' => 'hidden', 'value' => $data->studentId],
					'psnId' => ['type' => 'hidden', 'value' => $data->info->psnId],
					'studentCode' => [
						'label' => 'รหัสประจำตัวนักศึกษา',
						'type' => 'text',
						'class' => '-fill',
						'maxlength' => 13,
						'value' => $data->info->studentCode,
					],
					'prename' => [
						'label' => 'คำนำหน้านาม',
						'type' => 'text',
						'class' => '-fill',
						'require' => true,
						'value' => $data->info->prename,
					],
					'name' => [
						'label' => 'ชื่อ นามสกุล',
						'type' => 'text',
						'class' => '-fill',
						'require' => true,
						'value' => trim($data->info->name.' '.$data->info->lname),
					],
					'cid' => [
						'label' => 'เลขประจำตัวประชาชน',
						'type' => 'text',
						'class' => '-fill',
						'require' => true,
						'maxlength' => 13,
						'value' => $data->info->cid,
					],
					// 'orgName' => [
					// 	'label' => 'หน่วยงานต้นสังกัด',
					// 	'class' => '-fill',
					// 	'type' => 'text',
					// ],
					'sector' => ['type' => 'hidden', 'value' => 6],
					'orgId' => ['type' => 'hidden', 'label' => 'orgName', 'require' => true, 'value' => $data->info->orgId],
					'orgName' => [
						'label' => 'หน่วยงานต้นสังกัด',
						'type' => 'text',
						'class' => 'sg-autocomplete -fill',
						'require' => true,
						'attr' => [
							'data-query' => url('org/api/org'),
							'data-altfld' => 'edit-orgid',
						],
						'value' => $data->info->orgName,
					],
					'position' => [
						'label' => 'ตำแหน่งงาน',
						'class' => '-fill',
						'type' => 'text',
						'value' => $data->info->position,
					],
					'phone' => [
						'label' => 'โทรศัพท์',
						'type' => 'text',
						'class' => '-fill',
						'value' => $data->info->phone,
					],
					'email' => [
						'label' => 'อีเมล์',
						'type' => 'text',
						'class' => '-fill',
						'value' => $data->info->email,
					],
					'save' => [
						'type' => 'button',
						'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
						'container' => '{class: "-sg-text-right"}',
					],
				], // children
			]), // Form
		]);
	}

	function formTemplate() {
		return new Container([
			'class' => '-hidden',
			'children' => [
				$this->right->edit ? new Container([
					'id' => 'form-student',
					'child' => new Form([
						'class' => 'sg-form',
						'action' => url('project/info/nxt/api/'.$this->projectId.'/student.save/'.$this->serieNo),
						'rel' => 'notify',
						'done' => 'load | close',
						'children' => [
							'studentCode' => [
								'label' => 'รหัสประจำตัวนักศึกษา',
								'type' => 'text',
								'class' => '-fill',
								'maxlength' => 13,
							],
							'prename' => [
								'label' => 'คำนำหน้านาม',
								'type' => 'text',
								'class' => '-fill',
								'require' => true,
							],
							'name' => [
								'label' => 'ชื่อ นามสกุล',
								'type' => 'text',
								'class' => '-fill',
								'require' => true,
							],
							'cid' => [
								'label' => 'เลขประจำตัวประชาชน',
								'type' => 'text',
								'class' => '-fill',
								'require' => true,
								'maxlength' => 13,
							],
							'position' => [
								'label' => 'ตำแหน่งงาน',
								'class' => '-fill',
								'type' => 'text',
							],
							// 'orgName' => [
							// 	'label' => 'หน่วยงานต้นสังกัด',
							// 	'class' => '-fill',
							// 	'type' => 'text',
							// ],
							'sector' => ['type' => 'hidden', 'value' => 6],
							'orgId' => ['type' => 'hidden', 'label' => 'orgName', 'require' => true],
							'orgName' => [
								'label' => 'หน่วยงานต้นสังกัด',
								'type' => 'text',
								'class' => 'sg-autocomplete -fill',
								'require' => true,
								'attr' => [
									'data-query' => url('org/api/org'),
									'data-altfld' => 'edit-orgid',
								],
							],
							'save' => [
								'type' => 'button',
								'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
								'container' => '{class: "-sg-text-right"}',
							],
						], // children
					]), // Form
				]) : NULL // Container
			], // children
		]);
	}
}
?>