<?php
/**
* Project :: View Proposal Information
* Created 2021-12-20
* Modify  2021-12-20
*
* @param Widget $projectInfo
* @return Widget
*
* @usage project/proposal/{id}
*/

class ProjectProposalInfoView extends Page {
	var $projectId;
	var $projectInfo;

	function __construct($projectInfo = NULL) {
		$this->projectId = $projectInfo->projectId;
		$this->projectInfo = $projectInfo;
	}

	function build() {
		location('project/develop/'.$this->projectId);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Title',
			]), // AppBar
			'body' => new Widget([
				'children' => [], // children
			]), // Widget
		]);
	}
}
?>