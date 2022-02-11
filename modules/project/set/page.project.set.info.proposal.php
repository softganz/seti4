<?php
/**
* Project :: Set Child Proposal
* Created 2022-02-01
* Modify  2022-02-01
*
* @param Object $projectInfo
* @return Widget
*
* @usage project/set/{id}/info.proposal
*/

import('model:project.proposal.php');

class ProjectSetInfoProposal extends Page {
	var $projectId;
	var $projectInfo;

	function __construct($projectInfo) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
		$this->right = (Object) [
			'admin' => $this->projectInfo->RIGHT & _IS_ADMIN,
			'edit' => $this->projectInfo->RIGHT & _IS_EDITABLE,
		];
	}

	function build() {
		if (!$this->projectId) return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลโครงการที่ระบุ']);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->projectInfo->title,
			]), // AppBar
			'body' => $this->projectInfo->info->ischild ? new Container([
				'id' => 'project-child',
				'class' => 'project-child',
				'children' => [
					new Table([
						'thead' => ['no' => '', 'ชื่อข้อเสนอโครงการ', 'budget -money' => 'งบประมาณ'],
						'children' => array_map(
							function($item) {
								return [
									++$no,
									'<a href="'.url('project/'.$item->projectId).'">'.$item->title.'</a>',
									number_format($item->budget,2),
									$item->date_approve ? sg_date($item->date_approve, 'ว ดด ปปปป') : '',
									$item->project_status,
								];
							},
							ProjectProposalModel::items(['childOf' => $this->projectId, 'projectType' => 'all', 'status' => 'all'], ['items' => '*'])->items
						),
					]), // Table

					// $this->right->edit && $this->projectInfo->info->ischild ? new Nav([
					// 	'mainAxisAlignment' => 'end',
					// 	'class' => '-sg-paddingnorm',
					// 	'children' => [
					// 		'<a class="btn -primary" href="'.url('project/proposal/new', ['parent'=>$this->projectId]).'"><i class="icon -addbig -white"></i><span>เพิ่มโครงการย่อย</span></a>',
					// 	],
					// ]) : NULL, // Nav
				], // children
			]) : NULL, // Container
		]);
	}
}
?>