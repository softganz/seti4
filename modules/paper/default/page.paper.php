<?php
/**
* Paper   :: Page Controller
* Created :: 2018-06-04
* Modify  :: 2023-04-06
* Version :: 2
*
* @param Int $nodeId
* @param String $action
* @return Widget
*
* @usage paper[/{id}/{action}]
*/

import('model:node.php');

class Paper extends PageController {
	var $nodeId;
	var $action;

	function __construct($nodeId = NULL, $action = NULL) {
		if (empty($nodeId) && empty($action)) $action = 'home';
		else if ($nodeId && empty($action)) $action = 'view';

		parent::__construct([
			'nodeId' => $nodeId,
			'action' => 'paper.'.$action,
			'args' => func_get_args(),
			'info' => is_numeric($nodeId) ? PaperModel::get($nodeId, '{initTemplate: true}') : NULL,
		]);
	}

	function build() {
		// debugMsg('Id '.$this->nodeId.' Action = '.$this->action.' TranId = '.$this->tranId);

		// $isAccess = $mainInfo->RIGHT & _IS_ACCESS;

		// if (!$isAccess) {
		// return new ErrorMessage(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'access denied']);
		// }

		return parent::build();
	}
}
?>