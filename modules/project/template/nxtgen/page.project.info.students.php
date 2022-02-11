<?php
/**
* Project :: Follow Student Information
* Created 2021-11-10
* Modify  2021-11-10
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/proposal/{id}/info.student
*/

$debug = true;

import('widget:project.info.appbar.php');
import('model:lms.php');

class ProjectInfoStudents extends Page {
	var $projectId;
	var $action;
	var $tranId;
	var $degree;
	var $right;
	var $projectInfo;

	function __construct($projectInfo, $action = NULL, $tranId = NULL) {
		$this->projectId = $projectInfo->projectId;
		$this->action = $action;
		$this->tranId = $tranId;
		$this->projectInfo = $projectInfo;
		$this->right = (Object) ['edit' => $projectInfo->RIGHT & _IS_EDITABLE];
		$this->degree = $projectInfo->parentId == cfg('project')->nxt->degreeId;
	}

	function build() {
		$projectConfig = cfg('project');
		if (!in_array($this->projectInfo->parentId, [$projectConfig->nxt->degreeId, $projectConfig->nxt->nonDegreeId])) {
			return message('error', 'ไม่ใช่หลักสูตร');
		}

		if ($this->action) return $this->_action($this->action);

		return new Scaffold([
			'appBar' => new ProjectInfoAppBarWidget($this->projectInfo),
			'body' => new Container([
				'id' => 'propoject-info-student',
				'class' => 'propoject-info-student'.($this->right->edit ? ' sg-inline-edit' : ''),
				'attribute' => $this->right->edit ? [
					'data-update-url' => url('project/info/nxt/api/'.$this->projectId.'/serie.save'),
					'data-tpid' => $this->projectId,
					'data-debug' => debug('inline') ? 'inline' : '',
				] : NULL,
				'children' => [
					$this->projectInfo->parentId == $projectConfig->nxt->degreeId ? $this->studentDegree() : $this->studentNonDegree(),
					$this->right->edit ? new Nav([
						'mainAxisAlignment' => 'end',
						'class' => '-sg-paddingnorm',
						'child' => '<a class="sg-action btn -primary" href="'.url('project/'.$this->projectId.'/info.students/newserie').'" data-rel="box" data-width="320"><i class="icon -material">group_add</i><span>เปิดรับรุ่นใหม่</span></a>',
					]) : NULL, // Nav

					// $this->formTemplate(),

					// new DebugMsg($this->projectInfo),
				], // children
			]), // Container,
		]);
	}

	function studentDegree() {
		return new Table([
			'thead' => [
				'serie -amt' => 'รุ่น',
				'start -date' => 'วันที่เริ่ม',
				'end -date' => 'วันที่จบ',
				'in -amt' => 'รับ(คน)',
				'active -amt' => 'ศึกษา(คน)',
				'out -amt' => 'จบ(คน)',
				'menu -center' => '',
			],
			'children' => (function() {
				$rows = [];
				foreach (mydb::select(
					'SELECT
					s.*
					, COUNT(st.`studentId`) `amt`
					, COUNT(IF(st.`status` IN ("Active", "First Probation","Second Probation","Third Probation"),1,NULL)) `active`
					, COUNT(IF(st.`status` = "Graduate",1,NULL)) `graduate`
					FROM %lms_serie% s
						LEFT JOIN %lms_student% st ON st.`projectId` = s.`projectId` AND st.`serieNo` = s.`serieNo`
					WHERE s.`projectId` = :projectId
					GROUP BY s.`serieNo`
					ORDER BY s.`serieNo` ASC', [
					':projectId' => $this->projectId]
				)->items as $item) {
					$menu = new Row([
						'mainAxisAlignment' => 'center',
						'children' => [
							$this->right->edit ? '<a href="'.url('project/'.$this->projectId.'/info.student/'.$item->serieNo).'"><i class="icon -material">group</i></a>' : NULL,
						], // children
					]);
					$rows[] = [
						$item->serieNo,
						$this->right->edit ? '<a class="sg-action btn -link" href="'.url('project/'.$this->projectId.'/info.students/newserie/'.$item->serieId).'" data-rel="box" data-width="320">'.sg_date($item->dateStart,'ว ดด ปปปป').'</a>' : sg_date($item->dateStart,'ว ดด ปปปป'),
						$this->right->edit ? '<a class="sg-action btn -link" href="'.url('project/'.$this->projectId.'/info.students/newserie/'.$item->serieId).'" data-rel="box" data-width="320">'.sg_date($item->dateEnd,'ว ดด ปปปป').'</a>' : sg_date($item->dateEnd,'ว ดด ปปปป'),
						// sg_date($item->dateStart,'ว ดด ปปปป'),
						// sg_date($item->dateEnd,'ว ดด ปปปป'),
						$item->amt,
						$item->active,
						$item->graduate,
						$menu->build(),
					];
				}
				// debugMsg(mydb()->_query);
				return $rows;
			})(),
		]); // Table
		// return new Table([
		// 	'thead' => ['รุ่น', 'วันที่เริ่ม','วันที่จบ','ปี ...(คน)','จบ(คน)'],
		// ]); // Table
	}

	function studentNonDegree() {
		return new Table([
			'thead' => [
				'serie -amt' => 'รุ่น',
				'start -date' => 'วันที่เริ่ม',
				'end -date' => 'วันที่จบ',
				'in -amt' => 'รับ(คน)',
				'active -amt' => 'ศึกษา(คน)',
				'out -amt' => 'จบ(คน)',
				'menu -center' => '',
			],
			'children' => (function() {
				$rows = [];
				foreach (mydb::select(
					'SELECT
					s.*
					, COUNT(st.`studentId`) `amt`
					, COUNT(IF(st.`status` IN ("Active", "First Probation","Second Probation","Third Probation"),1,NULL)) `active`
					, COUNT(IF(st.`status` = "Graduate",1,NULL)) `graduate`
					FROM %lms_serie% s
						LEFT JOIN %lms_student% st ON st.`projectId` = s.`projectId` AND st.`serieNo` = s.`serieNo`
					WHERE s.`projectId` = :projectId
					GROUP BY s.`serieNo`
					ORDER BY s.`serieNo` ASC', [
					':projectId' => $this->projectId]
				)->items as $item) {
					$menu = new Row([
						'mainAxisAlignment' => 'center',
						'children' => [
							$this->right->edit ? '<a href="'.url('project/'.$this->projectId.'/info.student/'.$item->serieNo).'"><i class="icon -material">group</i></a>' : NULL,
						], // children
					]);
					$rows[] = [
						$item->serieNo,
						// view::inlineedit(
						// 	[
						// 		'group' => 'student',
						// 		'fld' => 'startDate',
						// 		'ret' => 'date:ว ดดด ปปปป',
						// 		'value' => sg_date($item->dateStart,'d/m/Y'),
						// 		'serieId' => $item->serieId,
						// 		'dateEnd' => sg_date($item->dateEnd,'d/m/Y'),
						// 	],
						// 	sg_date($item->dateStart,'ว ดด ปปปป'),
						// 	$this->right->edit,
						// 	'datepicker'
						// ),
						// view::inlineedit(
						// 	[
						// 		'group' => 'project',
						// 		'fld' => 'date_approve',
						// 		'ret' => 'date:ว ดดด ปปปป',
						// 		'value' => $this->projectInfo->info->date_approve ? sg_date($this->projectInfo->info->date_approve,'d/m/Y') : ''
						// 	],
						// 	$this->projectInfo->info->date_approve,
						// 	isInEditMode,
						// 	'datepicker'
						// )

						$this->right->edit ? '<a class="sg-action btn -link" href="'.url('project/'.$this->projectId.'/info.students/newserie/'.$item->serieId).'" data-rel="box" data-width="320">'.sg_date($item->dateStart,'ว ดด ปปปป').'</a>' : sg_date($item->dateStart,'ว ดด ปปปป'),
						$this->right->edit ? '<a class="sg-action btn -link" href="'.url('project/'.$this->projectId.'/info.students/newserie/'.$item->serieId).'" data-rel="box" data-width="320">'.sg_date($item->dateEnd,'ว ดด ปปปป').'</a>' : sg_date($item->dateEnd,'ว ดด ปปปป'),
						// sg_date($item->dateStart,'ว ดด ปปปป'),
						// sg_date($item->dateEnd,'ว ดด ปปปป'),
						$item->amt,
						$item->active,
						$item->graduate,
						$menu->build(),
					];
				}
				// debugMsg(mydb()->_query);
				return $rows;
			})(),
		]); // Table
	}

	function _action($action) {
		switch ($action) {
			case 'newserie':
				return $this->right->edit ? $this->_formNewSerie($this->tranId) : NULL;
				break;
		}
	}

	function _formNewSerie($serieId) {
		if ($serieId) {
			$data = LmsModel::getSerie($serieId)->info;
		} else {
			$data = (Object) [
				'serieNo' => mydb::select('SELECT MAX(`serieNo`) `lastSerie` FROM %lms_serie% WHERE `projectId` = :projectId LIMIT 1', [':projectId' => $this->projectId])->lastSerie + 1,
				'dateStart' => sg_date('Y-m-d'),
			];
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'เปิดรับรุ่นใหม่',
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
			]),
			'body' => new Form([
				'id' => 'new-serie',
				'class' => 'sg-form',
				'action' => url('project/info/nxt/api/'.$this->projectId.'/serie.save'),
				'rel' => 'notify',
				'done' => 'load | close',
				'children' => [
					'serieId' => ['type' => 'hidden', 'value' => $data->serieId],
					'orgId' => ['type' => 'hidden', 'value' => $this->projectInfo->orgId],
					'serieNo' => [
						'label' => 'รุ่นที่',
						'type' => 'text',
						'class' => '-numeric',
						'readonly' => true,
						'value' => $data->serieNo,
					],
					// 'amount' => [
					// 	'label' => 'จำนวนนักศึกษาที่จะรับ (คน)',
					// 	'type' => 'text',
					// 	'readonly' => true,
					// ],
					'year' => $this->degree ? [
						'label' => 'จำนวนปี:',
						'type' => 'select',
						'require' => true,
					] : NULL,
					'dateStart' => [
						'label' => 'วันที่เริ่ม',
						'type' => 'text',
						'class' => 'sg-datepicker -date',
						'require' => true,
						'value' => sg_date($data->dateStart, 'd/m/Y'),
					],
					'dateEnd' => [
						'label' => 'วันที่จบ',
						'type' => 'text',
						'class' => 'sg-datepicker -date',
						'require' => true,
						'value' => $data->dateEnd ? sg_date($data->dateEnd, 'd/m/Y') :NULL,
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
			$this->right->edit ? new Container([
				'id' => 'new-serie',
				'child' => new Form([
					'id' => 'new-serie',
					'action' => url(),
					'children' => [
						'serieNo' => [
							'label' => 'รุ่นที่',
							'type' => 'text',
							'class' => '-numeric',
							'readonly' => true,
							'value' => mydb::select('SELECT MAX(`serieNo`) `lastSerie` FROM %lms_serie% WHERE `projectId` = :projectId LIMIT 1', [':projectId', $this->projectId])->lastSerie + 1,
						],
						// 'amount' => [
						// 	'label' => 'จำนวนนักศึกษาที่จะรับ (คน)',
						// 	'type' => 'text',
						// 	'readonly' => true,
						// ],
						'year' => $this->degree ? [
							'label' => 'จำนวนปี:',
							'type' => 'select',
							'require' => true,
						] : NULL,
						'dateStart' => [
							'label' => 'วันที่เริ่ม',
							'type' => 'text',
							'class' => 'sg-datepicker -date',
							'require' => true,
							'value' => sg_date('d/m/Y'),
						],
						'dateEnd' => [
							'label' => 'วันที่จบ',
							'type' => 'text',
							'class' => 'sg-datepicker -date',
							'require' => true,
						],
						'save' => [
							'type' => 'button',
							'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
							'container' => '{class: "-sg-text-right"}',
						],
					], // children
				]), // Form
			]) : NULL // Container
		]);
	}
}
?>