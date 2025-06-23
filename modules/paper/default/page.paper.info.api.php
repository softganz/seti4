<?php
/**
* Paper   :: Information API
* Created :: 2021-11-22
* Modify  :: 2025-06-23
* Version :: 8
*
* @param Int $nodeId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage api/paper/{nodeId}/{action}[/{tranId}]
*/

use Paper\Model\PaperModel;

class PaperInfoApi extends Page {
	var $topicId;
	var $action;
	var $tranId;
	var $topicInfo;

	function __construct($topicId, $action, $tranId = NULL) {
		$this->topicId = $topicId;
		$this->action = $action;
		$this->tranId = $tranId;
		$this->topicInfo = PaperModel::get($this->topicId, '{initTemplate: true}');
	}

	function build() {
		// debugMsg('topicId '.$this->topicId.' Action = '.$this->action.' TranId = '.$this->tranId);

		$topicInfo = $this->topicInfo;
		$tpid = $topicInfo->tpid;
		$tranId = $this->tranId;

		if (empty($this->topicId)) return message(['responseCode' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'PROCESS ERROR']);

		// $isAccess = $topicInfo->RIGHT & _IS_ACCESS;
		// $isEdit = $topicInfo->RIGHT & _IS_EDITABLE;

		// if (!$isAccess) return message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'Access Denied']);
		// else if (!$isEdit) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Access Denied']);

		$ret = '';

		$isAddmin = user_access('administer contents,administer papers');
		$isEdit = $topicInfo->RIGHT & _IS_EDITABLE;

		if (!$topicInfo->tpid) return message('error', 'PARAMETER ERROR');
		else if (!$topicInfo) return message('error', 'TOPIC NOT FOUND.');
		else if (!$isEdit) return message('error', 'Access Denied');

		$ret = '';
		switch ($this->action) {





			default:
				return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Action not found!!!']);
				break;
		}

		return $ret;
	}
}
?>