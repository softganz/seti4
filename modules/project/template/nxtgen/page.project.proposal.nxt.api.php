<?php
/**
* Project API :: Nxt Proposal API
* Created 2021-11-25
* Modify  2021-11-25
*
* @param Int $proposalId
* @param String $action
* @param Int $tranId
* @return Mixed
*
* @usage project/proposal/nxt/api/{id}/{action}[/{tranId}]
*/

$debug = true;

import('model:project.proposal.php');

class ProjectProposalNxtApi extends Page {
	var $proposalId;
	var $action;
	var $tranId;

	function __construct($proposalId, $action, $tranId = NULL) {
		$this->proposalId = $proposalId;
		$this->action = $action;
		$this->tranId = $tranId;
	}

	function build() {
		// debugMsg('proposalId '.$this->proposalId.' Action = '.$this->action.' TranId = '.$this->tranId);

		$proposalInfo = is_numeric($this->proposalId) ? ProposalModel::get($this->proposalId) : NULL;
		$this->proposalId = $projectId = $proposalInfo->projectId;
		$tranId = $this->tranId;

		// Public API
		$publicApi = [];
		$checkRight = ['refno.save'];

		$isRight = $proposalInfo->RIGHT & _IS_RIGHT;
		$isAdmin = $proposalInfo->RIGHT & _IS_ADMIN;
		$isEdit = $proposalInfo->RIGHT & _IS_EDITABLE;

		if (empty($this->proposalId)) {
			return message(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'PROCESS ERROR']);
		} else if (in_array($this->action, $publicApi)) {
			return $this->_publicApi();
		} else if (in_array($this->action, $checkRight)) {
			// Check right in each case
		} else if (!$isEdit) {
			return message(['code' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Access Denied']);
		}

		$ret = '';
		// $proposalInfo->info->refNo = NULL;
		// debugMsg($proposalInfo, '$proposalInfo');

		switch ($this->action) {

			default:
				$ret .= 'SORRY!!! NO ACTION';
				break;
		}

		return $ret;
	}

	function _publicApi() {
		$ret = NULL;
		switch ($this->action) {
		}
		return $ret;
	}
}
?>