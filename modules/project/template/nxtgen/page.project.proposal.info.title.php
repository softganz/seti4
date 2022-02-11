<?php
/**
* Project :: Proposal Title Information
* Created 2021-11-03
* Modify  2021-11-15
*
* @param Object $proposalInfo
* @return Widget
*
* @usage project/proposal/{id}/info.title
*/

$debug = true;

class ProjectProposalInfoTitle extends Page {
	var $projectId;
	var $right;
	var $proposalInfo;

	function __construct($proposalInfo) {
		$this->projectId = $proposalInfo->projectId;
		$this->proposalInfo = $proposalInfo;
		$this->right = (Object) [
			'editMode' => ($this->proposalInfo->RIGHT & _IS_EDITABLE) && (SG\getFirst($this->proposalInfo->editMode, post('mode') === 'edit')),
			'viewPhone' => ($this->proposalInfo->RIGHT & _IS_ADMIN) || ($this->proposalInfo->RIGHT & _IS_OFFICER) || ($this->proposalInfo->RIGHT & _IS_EDITABLE),

		];
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->proposalInfo->title,
			]),
			'body' => new Container([
				'id' => 'propoject-proposal-info-title',
				'class' => 'section -box',
				'children' => [
					view::inlineedit(
						[
							'group' => 'topic',
							'fld' => 'title',
							'class' => '-fill',
							'label' => '1.1 ชื่อหลักสูตร (ภาษาไทย) : ',
							// 'desc' => '<em>ควรสั้น กระชับ เข้าใจง่าย และสื่อสาระของสิ่งที่จะทำอย่างชัดเจน เพิ่มความสะดวกในการค้นหา</em>',
						],
						$this->proposalInfo->title,
						$this->right->editMode
					),

					view::inlineedit(['label' => '1.2 ชื่อมหาวิทยาลัย : '.$this->proposalInfo->info->orgName]).'<br />',
					view::inlineedit(
						[
							'label' => '1.2.1 ปีงบประมาณ: ',
							'group' => 'dev:develop:pryear',
							'fld' => 'pryear',
							'options' => [
								'placeholder' => '?',
							],
							'value' => $this->proposalInfo->info->pryear,
						],
						$this->proposalInfo->info->pryear+543,
						$this->right->editMode,
						'select',
						[2022 => 2565, 2021 => 2564, 2020 => 2563, 2019 => 2562]
					),

					view::inlineedit([
						'label' => '1.2.2 รูปแบบการศึกษา',
						'options' => ['container' => (Object) ['class' => '-fill']]
					]),

					new Widget([
						'children' => (function() {
							$list = [
								cfg('project')->nxt->degreeId => [
									1 => 'ปริญญาตรี 4 ปี',
									2 => 'ปริญญาตรี เข้าร่วมปี 3 – 4',
									3 => 'ปริญญาตรี เทียบโอน',
									4 => 'ปริญญาตรี ต่อเนื่อง',
									5 => 'ปริญญาโท',
									6 => 'ปริญญาเอก',
								],
								cfg('project')->nxt->nonDegreeId => [
									11 => 'ประกาศนียบัตร',
								],
							];
							$widgets = [
								'<abbr class="checkbox -block"><label><b>'.$this->proposalInfo->parentTitle.'</b></label></abbr>',
							];
							foreach ($list[$this->proposalInfo->parentId] as $key => $value) {
								$widgets[] = '<abbr class="checkbox -block"><label>'.view::inlineedit(
									[
										'group' => 'bigdata:project.develop:degreeType',
										'fld' => 'degreeType',
										'fldref' => $key,
										'value' => $this->proposalInfo->data['degreeType'],
										'optionDupField' => 'keyName,keyId,fldName',
									],
									$value.':'.$value,
									$this->right->editMode,
									'radio'
								).'</label></abbr>';
							}
							return $widgets;
						})(), // Widget
					]), // Widget

					new Widget([
						'children' => (function() {
							$list = [
								1 => 'เรียนในสถานประกอบการ',
								2 => 'เรียนโดยการจัดทำโครงการ',
								3 => 'เรียนในห้องเรียนของมหาวิทยาลัย',
								4 => 'เรียนผ่านระบบออนไลน์',
							];
							$widgets = [
								'<abbr class="checkbox -block"><label><b>ลักษณะการเรียน</b></label></abbr>',
							];
							foreach ($list as $key => $value) {
								$widgets[] = '<abbr class="checkbox -block"><label>'.view::inlineedit(
									[
										'group' => 'bigdata:project.develop:classroomType-'.$key,
										'fld' => 'classroomType-'.$key,
										'fldref' => $key,
										'value' => $this->proposalInfo->data['classroomType-'.$key],
										'removeempty' => 'yes',
									],
									$value.':'.$value,
									$this->right->editMode,
									'checkbox'
								).'</label></abbr>';
							}
							return $widgets;
						})(), // Widget
					]), // Widget

					view::inlineedit(['label' => '1.3 กลุ่มอุตสาหกรรม<!-- (เลือกได้เพียง 1 กลุ่มอุตสาหกรรม) -->']),
					new Container([
						'style' => 'padding-left: 24px;',
						'children' => (function() {
							$widgets = [];
							$type = R::Model('category.get', 'project:industry', 'catid', '{result: "group", fullValue: true, order: "catid", debug: false}');
							foreach ($type as $groupKey => $groupValue) {
								$widgets[] = '<div><b>'.$groupKey.':</b></div>';
								foreach ($groupValue as $key => $value) {
									$widgets[] = '<abbr class="checkbox -block '.$value->listclass.'"><label>'
									. (preg_match('/^\-header/', $value->listclass) ? '<b>'.$value->name.':</b>' : view::inlineedit(
											[
												'group'=>'bigdata:project.develop:industryId',
												'fld' => 'industryId',
												'fldref' => $key,
												'optionDupField' => 'keyName,keyId,fldName',
												'value' => $this->proposalInfo->data['industryId'],
												'removeempty' => 'yes',
											],
											$value->name.':'.$value->name,
											$this->right->editMode,
											'radio'
										)
									)
									. '</label>'
									. '</abbr>';
								}
							}
							return $widgets;
						})(),
					]), // Container

					$this->coCompany(),

					$this->teacher(),

					'<style type="text/css">
					.checkbox.-block.-level-2 {padding-left: 24px;}
					.org-info>.-info-other,
					.org-info .row.-th-short-name,
					.org-info .row.-en-short-name,
					.org-info .row.-sector {display: none;}
					</style>',
				], // children
			]), // Container,
		]);
	}

	function coCompany() {
		return new Card([
			'children' => [
				view::inlineedit(['label' => '1.4 ชื่อหน่วยงานหรือสถานประกอบการที่ร่วมการจัดการเรียนการสอน']),
				new ScrollView([
					'child' => new Table([
						'thead' => ['ชื่อสถานประกอบการ', 'address -hover-parent' => 'ที่อยู่', 'mou -center -noprint' => 'MOU'],
						'children' => (function() {
							$rows = [];
							$companyList = mydb::select(
								'SELECT j.`trid`, j.`refId` `orgId`, o.`uid`
								, o.`name` `orgName`
								, o.`house`, o.`areacode`
								, cop.`provname` `changwatName`
								, cod.`distname` `ampurName`
								, cos.`subdistname` `tambonName`
								, f.`fid` `fileId`
								, f.`file` `fileName`
								FROM %project_tr% j
									LEFT JOIN %db_org% o ON o.`orgId` = j.`refId`
									LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(o.`areacode`, 2)
									LEFT JOIN %co_district% cod ON cod.`distid` = LEFT(o.`areacode`, 4)
									LEFT JOIN %co_subdistrict% cos ON cos.`subdistid` = LEFT(o.`areacode`, 6)
									LEFT JOIN %topic_files% f ON f.`tpid` = j.`tpid` AND f.`tagname` = "project-proposal-docs" AND f.`refid` = j.`trid`
								WHERE j.`tpid` = :projectId AND j.`formid` = "develop" AND j.`part` = "coorg"',
								[':projectId' => $this->projectId]
							);
							foreach ($companyList->items as $item) {
								$address = SG\implode_address($item, 'short');
								$menu = new Nav([
									'class' => '-icons',
									'children' => [
										$item->fileName ? '<a class="btn -link" href="'.cfg('url').'upload/forum/'.$item->fileName.'" target="_blank"><i class="icon -material">cloud_download</i></a>' : '<a class="btn -link"><i class="icon -material"></i></a>',
										$this->right->editMode ? '<form class="sg-form -upload" method="post" enctype="multipart/form-data" action="'.url('project/proposal/api/'.$this->projectId.'/docs.upload').'" data-rel="notify" data-done="load->replace:#propoject-proposal-info-title:'.url('project/proposal/'.$this->projectId.'/info.title', ['mode' => 'edit']).'">'
											. '<input type="hidden" name="removeOldFile" value="Yes">'
											. '<input type="hidden" name="document[fid]" value="'.$item->fileId.'">'
											. '<input type="hidden" name="document[prename]" value="project_'.$this->projectId.'_">'
											. '<input type="hidden" name="document[tagname]" value="project-proposal-docs">'
											. '<input type="hidden" name="document[refid]" value="'.$item->trid.'">'
											. '<span class="btn -link fileinput-button" style="padding: 6px;"><i class="icon -material">attach_file</i><input type="file" name="document" class="inline-upload" accept="image/*;capture=camcorder" onChange=\'$(this).closest(form).submit(); return false;\' /></span>'
											. '</form>' : NULL,
										'<a class="sg-action btn -link" href="'.url('org/'.$item->orgId.'/info.view').'" data-rel="box" data-width="full"><i class="icon -material">find_in_page</i></a>',
										$this->right->editMode ? '<a class="sg-action btn -link" href="'.url('project/proposal/api/'.$this->projectId.'/coorg.remove/'.$item->trid).'" data-rel="notify" data-done="remove:parent tr" data-title="ลบหน่วยงานหรือสถานประกอบการ" data-confirm="ต้องการลบหน่วยงานหรือสถานประกอบการนี้ออกจากข้อเสนอหลักสูตร กรุณายืนยัน?<br /><em>(ข้อมูลของหน่วยงานหรือสถานประกอบการจะไม่ถูกลบทิ้ง</em>)"><i class="icon -material -gray">cancel</i></a>' : NULL,
									], // children
								]);
								$rows[] = [
									$item->orgName,
									$address,
									$menu->build(),
								];
							}
							return $rows;
						})(),
					]), // Table
				]),
				$this->right->editMode ? '<nav class="nav -sg-text-right" style="padding-right: 16px;"><a class="sg-action btn" href="#form-company" data-rel="box" data-width="480"><i class="icon -material">add_circle</i><span>เพิ่มสถานประกอบการ</span></a></nav>' : NULL,
				$this->right->editMode ? new Container([
					'id' => 'form-company',
					'class' => 'template -hidden',
					'child' => new Form([
						'action' => url('project/proposal/api/'.$this->projectId.'/coorg.add'),
						'variable' => 'data',
						'class' => 'sg-form',
						'rel' => 'notify',
						'done' => 'load->replace:#propoject-proposal-info-title:'.url('project/proposal/'.$this->projectId.'/info.title', ['mode' => 'edit']).' | close',
						'children' => [
							'<header class="header -box"><h3>สถานประกอบการ</h4></header>',
							'projectId' => ['type' => 'hidden', 'value' => $this->projectId],
							'sector' => ['type' => 'hidden', 'value' => 6],
							'orgId' => ['type' => 'hidden', 'label' => 'มหาวิทยาลัย/สถาบันการศึกษา', 'require' => true],
							'name' => [
								'label' => 'ชื่อสถานประกอบการ',
								'type' => 'text',
								'class' => 'sg-autocomplete -fill',
								'require' => true,
								'attr' => [
									'data-query' => url('org/api/org'),
									'data-altfld' => 'edit-data-orgid',
								],
								'placeholder' => 'ระบุชื่อสถานประกอบการ หรือ ค้นหาจากรายชื่อ',
							],
							'areacode' => ['type' => 'hidden'],
							'address' => [
								'label' => 'ที่อยู่',
								'type' => 'text',
								'class' => 'sg-address -fill',
								'maxlength' => 100,
								'attr' => ['data-altfld' => 'edit-data-areacode'],
								'placeholder' => 'เลขที่ ถนน หมู่ที่ ตำบล ตามลำดับ แล้วเลือกจากรายการที่แสดง หรือ เลือกจากช่องเลือกด้านล่าง',
							],
							'phone' => [
								'label' => 'โทรศัพท์',
								'type' => 'text',
								'class' => '-fill',
								'maxlength' => 50,
								'placeholder' => 'หมายเลขโทรศัพท์',
							],
							'email' => [
								'label' => 'อีเมล์',
								'type' => 'text',
								'class' => '-fill',
								'maxlength' => 100,
								'placeholder' => 'อีเมล์',
							],
							'contactname' => [
								'label' => 'ชื่อผู้ประสานงาน',
								'type' => 'text',
								'class' => '-fill',
								'maxlength' => 100,
								'placeholder' => 'ชื่อผู้ประสานงาน',
							],
							'save' => [
								'type' => 'button',
								'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
								'container' => '{class: "-sg-text-right"}',
							]
						], // children
					]), // Form
				]) : NULL, // Container
			], // children
		]);
	}

	function teacher() {
		return new Card([
			'children' => [
				view::inlineedit(['label' => '1.5 ชื่ออาจารย์ผู้รับผิดชอบหลักสูตร (ชื่อ เบอร์โทรศัพท์และ e-mail)']),
				new ScrollView([
					'child' => new Table([
						'thead' => ['ชื่อ-นามสกุล', 'phone -center' => 'โทรศัพท์', 'email -center -hover-parent' => 'อีเมล์'],
						'children' => (function() {
							$rows = [];
							$teacherList = mydb::select(
								'SELECT t.`trid`, t.`detail1` `name`, t.`detail2` `phone`, t.`detail3` `email`
								FROM %project_tr% t
								WHERE t.`tpid` = :projectId AND t.`formid` = "develop" AND t.`part` = "owner"
								ORDER BY t.`trid` ASC',
								[':projectId' => $this->projectId]
							);
							foreach ($teacherList->items as $item) {
								$address = SG\implode_address($item);
								$rows[] = [
									$item->name,
									$this->right->viewPhone ? $item->phone : '-',
									($this->right->viewPhone ? $item->email : '-')
									. ($this->right->editMode ? '<nav class="nav -icons -hover"><a class="sg-action" href="'.url('project/proposal/api/'.$this->projectId.'/tran.remove/'.$item->trid).'" data-rel="notify" data-done="remove:parent tr" data-title="ลบอาจารย์ผู้รับผิดชอบ" data-confirm="ต้องการลบอาจารย์ผู้รับผิดชอบหลักสูตร กรุณายืนยัน?"><i class="icon -material">cancel</i></a></nav>' : NULL),
								];
							}
							return $rows;
						})(),
					]), // Table
				]),
				$this->right->editMode ? '<nav class="nav -sg-text-right" style="padding-right: 16px;"><a class="sg-action btn" href="#form-teacher" data-rel="box" data-width="480"><i class="icon -material">add_circle</i><span>เพิ่มชื่ออาจารย์</span></a></nav>' : NULL,
				$this->right->editMode ? new Container([
					'id' => 'form-teacher',
					'class' => 'template -hidden',
					'child' => new Form([
						'action' => url('project/proposal/api/'.$this->projectId.'/owner.add'),
						'class' => 'sg-form',
						'rel' => 'notify',
						'done' => 'load->replace:#propoject-proposal-info-title:'.url('project/proposal/'.$this->projectId.'/info.title', ['mode' => 'edit']).' | close',
						'children' => [
							'name' => [
								'label' => 'ชื่ออาจารย์ผู้รับผิดชอบหลักสูตร',
								'type' => 'text',
								'class' => '-fill',
								'require' => true,
							],
							'phone' => [
								'label' => 'โทรศัพท์',
								'type' => 'text',
								'class' => '-fill',
								'placeholder' => 'หมายเลขโทรศัพท์',
							],
							'email' => [
								'label' => 'อีเมล์',
								'type' => 'text',
								'class' => '-fill',
								'placeholder' => 'อีเมล์',
							],
							'save' => [
								'type' => 'button',
								'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
								'container' => '{class: "-sg-text-right"}',
							]
						], // children
					]), // Form
				]) : NULL, // Container
			], // children
		]);
	}
}
?>