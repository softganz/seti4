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

class ProjectInfoFollowup extends Page {
	var $projectId;
	var $action;
	var $tranId;
	var $degree;
	var $right;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
		$this->right = (Object) [
			'edit' => $projectInfo->RIGHT & _IS_EDITABLE
		];
		$this->degree = $projectInfo->parentId == cfg('project')->nxt->degreeId;
	}

	function build() {
		$projectConfig = cfg('project');
		if (!in_array($this->projectInfo->parentId, [$projectConfig->nxt->degreeId, $projectConfig->nxt->nonDegreeId])) {
			return message('error', 'ไม่ใช่หลักสูตร');
		}

		return new Scaffold([
			'appBar' => new ProjectInfoAppBarWidget($this->projectInfo),
			'body' => new Widget([
				'children' => [
					new Card([
						'children' => [
							new ListTile([
								'class' => '-sg-paddingnorm',
								'title' => 'ติดตามผล',
								'leading' => '<i class="icon -material">stars</i>',
							]),
							new Ui([
								'type' => 'menu',
								'children' => [
									'<a class="" href=""><i class="icon -material -'.($isInput ? 'green' : 'gray').'">check_circle</i><span>1. ติดตามการทำงานของผู้จบการศึกษา</span></a>',
									'<a class="" href=""><i class="icon -material -'.($isProcess ? 'green' : 'gray').'">check_circle</i><span>2. ประเมินความพึงพอใจของสถานประกอบการ</span></a>',
								], // children
							]), // Ui
						], // children
					]), // Card
				], // children
			]), // Container,
		]);
	}
}
?>