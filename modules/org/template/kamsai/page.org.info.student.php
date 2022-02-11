<?php
/**
* Org :: Student Dashboard
* Created 2021-12-05
* Modify  2021-12-05
*
* @param Object $orgInfo
* @return Widget
*
* @usage org/{id}/info.student
*/

import('model:lms.php');
import('widget:org.nav.php');

class OrgInfoStudent extends Page {
	var $orgId;
	var $right;
	var $orgInfo;

	function __construct($orgInfo) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
		$this->right = (Object) [
			'edit' => is_admin(), //$this->orgInfo->RIGHT & _IS_EDITABLE,
		];
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'นักเรียน : '.$this->orgInfo->name,
				'navigator' => new OrgNavWidget($this->orgInfo),
				$this->_script(),
			]),
			'sideBar' => new Column([
				'crossAxisAlignment' => 'center',
				'children' => [
					// new Row([
					// 	'children' => [
					// 		'<a class="btn -link" href="">ชั้น</a>',
					// 		'<a class="btn -link" href="">รุ่น</a>'
					// 	]
					// ]), // Row

					// Class Level Name
					'<h3><i class="icon -material">school</i><span>ชั้นเรียน</span></h3>',
					new Ui([
						'type' => 'menu',
						'children' => array_map(
							function($item) {
								return '<a class="sg-action" href="'.url('org/'.$this->orgId.'/info.student.serie', ['level' => $item->classLevel, 'class' => $item->classNo]).'" data-rel="#main"><span>'.$item->classLevelName.'/'.$item->classNo.'</span></a>';
							},
							LmsModel::serieClassLevelItems(['orgId' => $this->orgId])->items
						), // children
					]), // Ui

					// Serie No
					'<h3><i class="icon -material">group</i><span>รุ่นนักเรียน</span></h3>',
					new Ui([
						'type' => 'menu',
						'children' => array_map(
							function($item) {
								return '<a class="sg-action" href="'.url('org/'.$this->orgId.'/info.student.serie/'.$item->serieNo).'" data-rel="#main">รุ่น '.$item->serieNo.'</a>';
							},
							LmsModel::serieItems(['orgId' => $this->orgId], ['sort' => 'DESC'])->items
						),
					]), // Ui

					// Create new serie
					new Ui([
						'type' => 'menu',
						'children' => [
							$this->right->edit ? '<a class="sg-action" href="'.url('org/'.$this->orgId.'/info.student.newserie').'" data-rel="box" data-width="320"><i class="icon -material">add_circle_outline</i><span>รุ่นใหม่</span></a>' : NULL,
						], // children
					]), // Ui
				], // children
			]), // Container
			'body' => new Container([
				'id' => 'org-info-student',
				'class' => 'org-info-student',
				'children' => [
					new ScrollView([
						'child' => new Table([
							'thead' => ['no' => '', 'name -nowrap' => 'ชื่อ นามสกุล', 'level -center -nowrap' => 'ชั้น'],
							'children' => array_map(
								function($item) {
									static $no = 0;
									return [
										++$no,
										$item->preName.$item->name.' '.$item->lname,
										$item->classLevelName.'/'.$item->classNo,
									];
								},
								LmsModel::getStudentItems(['orgId' => $this->orgId])->items
							), // children
						]), // Table
					]), // ScrollView
				], // children
			]), // Container
		]);
	}

	function _script() {
		head(
			'<style type="text/css">
			.module.-module-has-sidebar .page.-primary {display: flex; flex-wrap: wrap;}
			.page.-sidebar {flex: 0 0 140px !important; background-color: #fff;}
			.page.-main {flex: 1; overflow: scroll;}
			.package-footer {display: none;}
			</style>'
		);
	}
}
?>