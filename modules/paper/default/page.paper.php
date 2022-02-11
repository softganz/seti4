<?php
/**
* Paper Page Controller
* Created 2018-06-04
* Modify  2019-06-01
*
* @param Object $self
* @param Int $tpid
* @param String $action
* @param Int $tranId
* @return String
*/

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
		// return R::Page('paper.view',$this, $tpid,$action,$tranId,func_get_arg(4),func_get_arg(5));


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

function paper($self, $tpid = NULL, $action = NULL, $tranId = NULL) {
	$args = func_get_args();
	// debugMsg($args, '$args');
	$topicInfo = NULL;
	if (is_numeric($tpid)) {
		$topicInfo = R::Model('paper.get', $tpid, '{initTemplate: true}');
	}
	// if ($tpid) return R::Page('paper.view',$self, $topicInfo,$action,$tranId,$arg[4],$args[5]);

	if (empty($action) && empty($tpid)) $action = 'home';
	if ($tpid && empty($action)) $action = 'view';

	if (empty($topicInfo)) $topicInfo = $tpid;
	$argIndex = 2; // Start argument

	debugMsg('PAGE PAPER Topic = '.$tpid.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
	// debugMsg(func_get_args(), '$args');

	$ret = R::Page(
		'paper.'.$action,
		$self,
		$topicInfo,
		$args[$argIndex],
		$args[$argIndex+1],
		$args[$argIndex+2],
		$args[$argIndex+3],
		$args[$argIndex+4]
	);

	//debugMsg('TYPE = '.gettype($ret));
	if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';

	//$ret .= print_o($topicInfo,'$topicInfo');

	return $ret;

}
?>