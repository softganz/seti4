<?php
/**
* Project :: Planning Page Controller
* Created 2021-07-28
* Modify  2021-09-21
*
* @param Int $projectId
* @param String $action
* @param Int $tranId
* @return Widget
*
* @usage project/planning/{id}/{action}/{tranId}
*/

$debug = true;

import('model:project.planning.php');

class ProjectPlanning extends Page {
	var $projectId;
	var $action;
	var $_args;

	function __construct($projectId = NULL, $action = NULL, $tranId = NULL) {
		// parent::__construct();
		$this->projectId = $projectId;
		$this->action = $action;
		if (!is_numeric($this->projectId)) {$this->action = $projectId; unset($this->projectId);} // Action as tpid and clear
		$this->_args = func_get_args();
	}

	function build() {
		$planningInfo = $this->projectId ? ProjectPlanningModel::get($this->projectId, '{initTemplate: true}') : NULL;
		$this->projectId = $planningInfo->projectId;

		// Has action but no planning info
		if ($this->action && empty($this->projectId)) return message('error', 'NO PLANNING INFORMATION');

		if (empty($this->action) && $this->projectId) $this->action = 'info.view';
		else if (empty($this->action) && empty($this->projectId)) $this->action = 'home';

		$argIndex = 2; // Start argument

		// debugMsg('PAGE PROJECT Topic = '.$this->projectId.' , Action = '.$this->action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.$this->_args[$argIndex]);
		// debugMsg($this->_args, '$args');
		// debugMsg(['A1']+array_slice($this->_args,$argIndex,NULL,true),'$args');
		// debugMsg(array_push($planningInfo,array_slice($this->_args,$argIndex,NULL,true)),'$args1');

		return R::PageWidget(
			'project.planning.'.$this->action,
			[-1 => $planningInfo] + array_slice($this->_args,$argIndex,NULL,true)
		);
	}
}
?>