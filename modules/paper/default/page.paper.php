<?php
/**
* Paper Page Controller
* Created 2018-06-04
* Modify  2019-06-01
*
* @param Int $tpid
* @param String $action
* @return String
*
* @usage paper[/{id}/{action}]
*/

$debug = true;

class Paper extends Page {
	var $tpid;
	var $action;
	var $_args = [];

	function __construct($tpid = NULL, $action = NULL) {
		parent::__construct();
		$this->tpid = $tpid;
		$this->action = $action;
		$this->_args = func_get_args();
	}

	function build() {
		if (empty($this->action) && empty($this->tpid)) $this->action = 'home';
		if ($this->tpid && empty($this->action)) $this->action = 'view';

		$topicInfo = $this->tpid && is_numeric($this->tpid) ? R::Model('paper.get', $this->tpid, '{initTemplate: true}') : NULL;
		if (empty($topicInfo)) $topicInfo = $this->tpid;
		$argIndex = 2; // Start argument

		// debugMsg('PAGE PAPER Topic = '.$this->tpid.' , Action = '.$this->action.' , ArgIndex = '.$argIndex.' , Arg = '.$this->args[$argIndex]);
		// debugMsg($this->args, '$args');

		return R::PageWidget(
			'paper.'.$this->action,
			[-1 => $topicInfo] + array_slice($this->_args, $argIndex)
		);
	}
}
?>