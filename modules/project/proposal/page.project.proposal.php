<?php
/**
* Project :: Proposal Controller
* Created 2021-06-28
* Modify 	2021-09-25
*
* @param Int $projectId
* @param String $action
* @return Widget
*
* @usage project/propoal[/{id}/{action}/{tranId}]
*/

$debug = true;

import('model:project.proposal.php');

class ProjectProposal extends Page {
	var $projectId;
	var $action;
	var $_args = [];

	function __construct($projectId = NULL, $action = NULL) {
		$this->projectId = $projectId;
		$this->action = $action;
		$this->_args = func_get_args();
	}

	function build() {
		// debugMsg('Id '.$this->projectId.' Action = '.$this->action.' TranId = '.$this->tranId);

		$proposalInfo = NULL;
		if (is_numeric($this->projectId)) {
			$proposalInfo = ProjectProposalModel::get($this->projectId, '{initTemplate: true, debug: true}');
			// debugMsg($this->projectId);
			// debugMsg($proposalInfo,'$proposalInfo');
			// if (!$proposalInfo->projectId) return message([
			// 	'code' => _HTTP_ERROR_NOT_FOUND,
			// 	// 'type' => 'error',
			// 	'text' => 'Proposal Not Found',
			// ]);
			if (!$proposalInfo->projectId) return new ErrorMessage([
				'code' => _HTTP_ERROR_NOT_FOUND,
				'type' => 'error',
				'text' => 'Proposal Not Found',
			]);
		}

		if (empty($this->projectId) && empty($this->action)) $this->action = 'home';
		else if ($this->projectId && empty($this->action)) $this->action = 'info.view';

		$argIndex = 2;

		// debugMsg('PAGE CONTROLLER Id = '.$groupId.' , Action = '.$this->action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.$this->_args[$argIndex]);
		//debugMsg($this->_args, '$args');

		return R::PageWidget(
			'project.proposal.'.$this->action,
			[-1 => $proposalInfo] + array_slice($this->_args, $argIndex)
		);
	}
}
?>