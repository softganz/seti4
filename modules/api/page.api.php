<?php
/**
* Module  :: Page Controller
* Created :: 2022-09-10
* Modify  :: 2022-09-10
* Version :: 1
*
* @param Int $mainId
* @param String $action
* @return Widget
*
* @usage module[/{id}/{action}/{tranId}]
*/

class Api extends Page {
	var $package;
	var $action;
	var $_args = [];

	function __construct($package = NULL, $action = NULL) {
		$this->package = $package;
		$this->action = $action;
		$this->_args = func_get_args();
	}

	function build() {
		// debugMsg('package '.$this->package.' Action = '.$this->action.' TranId = '.$this->tranId);

		$argIndex = 2;

		// debugMsg('PAGE CONTROLLER Id = '.$this->mainId.' , Action = '.$this->action.' , Arg['.$argIndex.'] = '.$this->_args[$argIndex]);
		// debugMsg($this->_args, '$args');
		// debugMsg($this->package.'.api.'.$this->action);

		// return R::PageWidget(
		// 	$this->package.'.api.'.$this->action,
		// 	array_slice($this->_args, $argIndex)
		// );
	}
}
?>