<?php
/**
* Project :: Set Page Controller
* Created 2022-02-01
* Modify 	2022-02-01
*
* @param Int $projectId
* @param String $action
* @return Widget
*
* @usage project/set[/{id}[/{action}[/{tranId}]]]
*/

import('model:project.php');

class ProjectSet extends Page {
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

		$projectInfo = is_numeric($this->projectId) ? ProjectModel::get($this->projectId, '{initTemplate: true, debug: false'.($isProjectAllType ? ', type: "*"' : '').'}') : NULL;

		if (empty($this->projectId) && empty($this->action)) $this->action = 'home';
		else if ($this->projectId && empty($this->action)) $this->action = 'info.view';

		$argIndex = 2;

		// debugMsg('PAGE CONTROLLER Id = '.$this->projectId.' , Action = '.$this->action.' Arg['.$argIndex.'] = '.$this->_args[$argIndex]);
		// debugMsg($this->_args, '$args');
		// debugMsg([-1 => $projectInfo] + array_slice($this->_args, $argIndex), '_args');

		return R::PageWidget(
			'project.set.'.$this->action,
			[-1 => $projectInfo] + array_slice($this->_args, $argIndex)
		);
	}
}
?>