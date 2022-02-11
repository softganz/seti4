<?php
/**
* Project :: My Action Form
* Created 2021-12-14
* Modify  2021-12-14
*
* @param Int $projectId
* @return Widget
*
* @usage project/my/action/form
*/

$debug = true;

class ProjectMyActionForm extends Page {
	var $projectId;
	var $actionId;

	function __construct($projectId = NULL, $actionId = NULL) {
		$this->projectId = $projectId;
		$this->actionId = $actionId;
	}

	function build() {
		if (!i()->ok) return $this->_signForm();
		if (empty($this->projectId)) return $this->_selectProject();

		$projectInfo = R::Model('project.get', $this->projectId);

		$isAdd = $projectInfo->right->isAdmin || $projectInfo->info->membershipType;
		if (!$isAdd) {
			return message('error', 'ขออภัย - โครงการนี้ท่านไม่สามารถเขียนบันทึกกิจกรรมได้');
		}

		$actionInfo = $this->actionId ? R::Model('project.action.get', ['projectId' => $this->projectId, 'actionId' => $this->actionId], '{debug: false}') : NULL;


		$actionOption = (Object) array(
			'rel' => 'none',
			'done' => R()->appAgent ? 'load->replace:.form.-action:'.url('project/my/action').' | moveto: 0,0' : 'load->replace:.form.-action:'.url('project/my/action').' | back | moveto: 0,0',
		);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'เขียนบันทึกกิจกรรม',
			]),
			'body' => new Widget([
				'children' => [
					R::View('project.action.form', $projectInfo, $actionInfo->activityId, $actionInfo, $actionOption),
				], // children
			]), // Widget
		]);
	}

	function _signForm() {
		$ret = '<header class="header -hidden">'._HEADER_BACK.'<h3>Sign In</h3></header>'
				. R::View(
					'signform',
					'{
						time:-1,
						rel: "none",
						signret: "project/app/action/form",
						done: "'.(R()->appAgent ? 'load:#project-activity-card:'.url(q()).' | ' : '').'load:#project-activity-card:'.url(q()).'",
						regRel: "'.(R()->appAgent ? '#main' : '#main').'"
					}'
				);
						//done: "'.(R()->appAgent ? 'load | ' : '').'load->clear:box:'.url(q()).'",

			$ret .= '<style type="text/css">
			.toolbar.-main.-imed h2 {text-align: center;}
			.form.signform .form-item {margin-bottom: 16px; position: relative;}
			.form.signform label {position: absolute; left: 8px; color: #666; font-style: italic; font-size: 0.9em; font-weight: normal;}
			.form.signform .form-text, .form.signform .form-password {padding-top: 24px;}
			.module-imed.-softganz-app .form-item.-edit-cookielength {display: none;}
			.login {border: none; background-color: transparent;}
			.login.-normal h3 {display: none;}
			.form-item.-edit-cookielength {display: none;}
			.form.signform .ui-action>a {display: block;}
			</style>';
			return $ret;
	}

	function _selectProject() {
		$selectProject = R::View('project.select', '{rel: "'.(R()->appAgent ? '#main' : '#project-activity-card').'", retUrl: "'.url('project/app/action/form/$id').'"}');

		if ($selectProject->error) {
			return message('error', 'ขออภัย '.$selectProject->error);
		} else if ($selectProject->projectId) {
			$this->projectId = $selectProject->projectId;
		} else {
			return '<header class="header -box -hidden">'._HEADER_BACK.'<h3>เลือกโครงการ</h3></header>'
				. $selectProject->build();
		}
	}
}
?>