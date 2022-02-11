<?php
/**
* Project :: Article/Research Information
* Created 2022-01-24
* Modify  2022-01-24
*
* @param Object $projectInfo
* @return Widget
*
* @usage proejct/{id}/info.article
*/

import('widget:project.follow.nav.php');

class ProjectInfoResearch extends Page {
	var $projectId;
	var $articleId;
	var $right;
	var $projectInfo;

	function __construct($projectInfo, $articleId = NULL) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
		$this->articleId = $articleId;
		$this->right = (Object) [
			'editable' => $projectInfo->info->isRight,
			'edit' => $projectInfo->info->isRight,
		];
		if (empty($articleId)) location('project/'.$this->projectId.'/info.articles');
	}

	function build() {
		if (!$this->projectId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลโครงการที่ระบุ']);

		$articleInfo = mydb::select(
			'SELECT
			a.`tpid` `projectId`
			, a.`detail1` `title`
			, a.`detail2` `enTitle`
			, a.`detail3` `researcherName`
			, a.`detail4` `enResearcherName`
			, a.`text1` `address`
			, a.`text2` `phone`
			, a.`text3` `email`
			, a.`text4` `orgName`
			, a.`text5` `year`
			, a.`text6` `keyword`
			, a.`text7` `web`
			, a.`text8` `description`
			FROM %project_tr% a
			WHERE a.`tpid` = :projectId AND a.`trid` = :articleId
			LIMIT 1
			',
			[
				':projectId' => $this->projectId,
				':articleId' => $this->articleId,
			]
		);

		$inlineAttr = [];
		if ($this->right->edit) {
			$inlineAttr['data-tpid'] = $this->projectId;
			$inlineAttr['data-update-url'] = url('project/edit/tr/'.$this->projectId);
			$inlineAttr['data-refresh-url'] = url('project/'.$this->projectId, ['debug' => post('debug')]);
			if (debug('inline')) $inlineAttr['data-debug'] = 'inline';
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->projectInfo->title,
				'leading' => '<i class="icon -material">checklist</i>',
				'navigator' => new ProjectFollowNavWidget($this->projectInfo),
			]), // AppBar
			'body' => new Container([
				'class' => $this->right->edit ? 'sg-inline-edit' : NULL,
				'attribute' => $inlineAttr,
				'children' => [
					new ListTile([
						'title' => 'ชื่องานวิจัย',
						'leading' => '<i class="icon -material">checklist</i>',
					]), // ListTile

					view::inlineedit(
						[
							'label' => 'ชื่อผลงาน/โครงการ (ภาษาไทย)',
							'group' => 'tr',
							'fld' => 'detail1',
							'tr' => $this->articleId,
							'value' => $articleInfo->title,
							'options' => ['class' => '-fill']
						],
						$articleInfo->title,
						$this->right->edit
					),

					view::inlineedit(
						[
							'label' => 'ชื่อผลงาน/โครงการ (ภาษาอังกฤษ)',
							'group' => 'tr',
							'fld' => 'detail2',
							'tr' => $this->articleId,
							'value' => $articleInfo->enTitle,
							'options' => ['class' => '-fill']
						],
						$articleInfo->enTitle,
						$this->right->edit
					),

					view::inlineedit(
						[
							'label' => 'ชื่อ - นามสกุล นักวิจัย (ภาษาไทย)',
							'group' => 'tr',
							'fld' => 'detail3',
							'tr' => $this->articleId,
							'value' => $articleInfo->researcherName,
							'options' => ['class' => '-fill']
						],
						$articleInfo->researcherName,
						$this->right->edit
					),

					view::inlineedit(
						[
							'label' => 'ชื่อ - นามสกุล นักวิจัย (ภาษาอังกฤษ)',
							'group' => 'tr',
							'fld' => 'detail4',
							'tr' => $this->articleId,
							'value' => $articleInfo->enResearcherName,
							'options' => ['class' => '-fill']
						],
						$articleInfo->enResearcherName,
						$this->right->edit
					),

					view::inlineedit(
						[
							'label' => 'ที่อยู่ที่ติดต่อได้',
							'group' => 'tr',
							'fld' => 'text1',
							'tr' => $this->articleId,
							'value' => $articleInfo->address,
							'options' => ['class' => '-fill']
						],
						$articleInfo->address,
						$this->right->edit
					),

					view::inlineedit(
						[
							'label' => 'เบอร์โทรศัพท์',
							'group' => 'tr',
							'fld' => 'text2',
							'tr' => $this->articleId,
							'value' => $articleInfo->phone,
							'options' => ['class' => '-fill']
						],
						$articleInfo->phone,
						$this->right->edit
					),

					view::inlineedit(
						[
							'label' => 'E-mail',
							'group' => 'tr',
							'fld' => 'text3',
							'tr' => $this->articleId,
							'value' => $articleInfo->email,
							'options' => ['class' => '-fill']
						],
						$articleInfo->email,
						$this->right->edit
					),

					view::inlineedit(
						[
							'label' => 'ชื่อหน่วยงาน',
							'group' => 'tr',
							'fld' => 'text4',
							'tr' => $this->articleId,
							'value' => $articleInfo->orgName,
							'options' => ['class' => '-fill']
						],
						$articleInfo->orgName,
						$this->right->edit
					),

					view::inlineedit(
						[
							'label' => 'ปี พ.ศ. ที่ดำเนินการเสร็จ',
							'group' => 'tr',
							'fld' => 'text5',
							'tr' => $this->articleId,
							'value' => $articleInfo->year,
							'options' => ['class' => '-fill']
						],
						$articleInfo->year,
						$this->right->edit
					),

					view::inlineedit(
						[
							'label' => 'คำค้น (Keyword)',
							'group' => 'tr',
							'fld' => 'text6',
							'tr' => $this->articleId,
							'value' => $articleInfo->keyword,
							'options' => ['class' => '-fill']
						],
						$articleInfo->keyword,
						$this->right->edit
					),

					view::inlineedit(
						[
							'label' => 'อ้างอิง (ใส่ URL ที่สามารถเข้าถึงเอกสารได้กรณีเผยแพร่ผลงานฉบับเต็มทางอินเตอร์เน็ต ถ้าไม่มีให้เว้นว่างไว้)',
							'group' => 'tr',
							'fld' => 'text7',
							'tr' => $this->articleId,
							'value' => $articleInfo->web,
							'options' => ['class' => '-fill']
						],
						$articleInfo->web,
						$this->right->edit
					),

					view::inlineedit(
						[
							'label' => 'คำอธิบาย 1 หน้ากระดาษ A4',
							'group' => 'tr',
							'fld' => 'text8',
							'tr' => $this->articleId,
							'value' => $articleInfo->description,
							'ret' => 'nl2br',
							'options' => ['class' => '-fill']
						],
						nl2br($articleInfo->description),
						$this->right->edit,
						'textarea'
					),
					'(สรุปรายละเอียดผลงานวิจัย/โครงการวิจัย มีความยาว 1 หน้ากระดาษ A4 เนื้อหาครอบคลุมถึง ความสำคัญของงานวิจัยชิ้นนี้ วัตถุประสงค์ วิธีการดำเนินงาน ผลการดำเนินงาน และประโยชน์ของผลงาน'
				], // children
			]), // Widget
		]);
	}
}
?>