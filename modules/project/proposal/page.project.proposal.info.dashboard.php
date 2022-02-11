<?php
/**
* Project :: Proposal Title Information
* Created 2021-11-03
* Modify  2021-11-03
*
* @param Object $proposalInfo
* @return Widget
*
* @usage project/proposal/{id}/info.title
*/

$debug = true;

import('widget:project.proposal.nav.php');

class ProjectProposalInfoDashboard extends Page {
	var $projectId;
	var $proposalInfo;

	function __construct($proposalInfo) {
		$this->projectId = $proposalInfo->projectId;
		$this->proposalInfo = $proposalInfo;
		$this->right = (Object) [
			'right' => $this->proposalInfo->RIGHT & _IS_RIGHT,
			'admin' => $this->proposalInfo->RIGHT & _IS_ADMIN,
			'creater' => i()->ok && $this->proposalInfo->uid == i()->uid,
		];
	}

	function build() {
		if (!$this->right->right) return message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'Access Denied']);

		$isDeleteable = ($this->right->admin || $this->right->creater) && in_array($this->proposalInfo->info->status, [1]);
		// debugMsg($this->right,'$right');
		// debugMsg($this->proposalInfo, '$this->proposalInfo');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->proposalInfo->title,
				'navigator' => new ProjectProposalNavWidget($this->proposalInfo, ['showPrint' => true]),
			]),
			'body' => new Widget([
				'children' => [
					'<header class="header"><h3>แผงควบคุม (Dashboard)</h3></header>',
					new Nav([
						'class' => 'project-dashboard nav -app-menu',
						'header' => '<h3>Admin</h3>',
						'children' => [
							// '<a class="sg-action" href="'.url('project/'.$projectId.'/page.setting').'" data-rel="box" data-width="full"><i class="icon -material">settings</i><span>กำหนดค่า</span></a>',
							$isDeleteable ? '<a class="sg-action" href="'.url('project/proposal/'.$this->projectId.'/info.delete').'" data-rel="box" data-width="640"><i class="icon -material">delete</i><span>ลบข้อเสนอโครงการ</span></a>' : '',
						], // children
					]),
					// new DebugMsg($this,'$this'),
					'<style type="text/css">
					.project-dashboard.nav.-app-menu>ul {flex: 0 0 100%; justify-content: flex-start;}
					</style>',
				], // children
			]), // Container,
		]);
	}
}
?>