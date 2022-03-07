<?php
/**
* Module :: Page Controller
* Created 2021-09-30
* Modify 	2021-09-30
*
* @param Int $NodeId
* @param String $action
* @return Widget
*
* @usage module[/{id}/{action}/{tranId}]
*/

$debug = true;

import('model:node.php');

class Node extends Page {
	var $nodeId;
	var $action;
	var $_args = [];

	function __construct($nodeId = NULL, $action = NULL) {
		$this->nodeId = $nodeId;
		$this->action = $action;
		$this->_args = func_get_args();
	}


	function build() {
		// debugMsg('Id '.$this->nodeId.' Action = '.$this->action.' TranId = '.$this->tranId);

		// $isAccess = $nodeInfo->RIGHT & _IS_ACCESS;
		// $isEdit = $nodeInfo->RIGHT & _IS_EDITABLE;

		// if (!$isAccess) message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'access denied']);
		// else if (!$isEdit) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'access denied']);

		$nodeInfo = is_numeric($this->nodeId) ? NodeModel::get($this->nodeId, '{debug: false}') : NULL;

		if (empty($this->nodeId) && empty($this->action)) $this->action = 'home';
		else if ($this->nodeId && empty($this->action)) $this->action = 'view';

		$argIndex = 2;

		// debugMsg('PAGE CONTROLLER Id = '.$this->nodeId.' , Action = '.$this->action.' , Arg['.$argIndex.'] = '.$this->_args[$argIndex]);
		//debugMsg($this->_args, '$args');

		return R::PageWidget(
			'node.'.$this->action,
			[-1 => $nodeInfo] + array_slice($this->_args, $argIndex)
		);
	}
}
?>