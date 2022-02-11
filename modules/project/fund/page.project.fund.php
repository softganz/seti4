<?php
/**
* Project :: Fund Controller
* Created 2021-10-04
* Modify 	2021-10-04
*
* @param Int $fundid
* @param String $action
* @return Widget
*
* @usage project/fund[/$orgId][/$action]
*/

$debug = true;

class ProjectFund extends Page {
	var $orgId;
	var $action;
	var $_args = [];

	function __construct($orgId = NULL, $action = NULL) {
		$this->orgId = SG\getFirst(post('orgid'), $orgId);
		$this->action = $action;
		$this->_args = func_get_args();
	}

	function build() {
		// debugMsg('Id '.$this->orgId.' Action = '.$this->action.' TranId = '.$this->tranId);

		$fundInfo = $this->orgId ? R::Model('project.fund.get', $this->orgId) : NULL;
		$this->orgId = $fundInfo->orgId;

		// $isAccess = $mainInfo->RIGHT & _IS_ACCESS;
		// $isEdit = $mainInfo->RIGHT & _IS_EDITABLE;

		// if (!$isAccess) message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'access denied']);
		// else if (!$isEdit) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'access denied']);

		if (empty($this->orgId) && empty($this->action)) $this->action = 'home';
		else if ($this->orgId && empty($this->action)) $this->action = 'info.view';

		$argIndex = 2;

		// debugMsg('PAGE CONTROLLER Id = '.$this->orgId.' , Action = '.$this->action.' , Arg['.$argIndex.'] = '.$this->_args[$argIndex]);
		//debugMsg($this->_args, '$args');

		return R::PageWidget(
			'project.fund.'.$this->action,
			[-1 => $fundInfo] + array_slice($this->_args, $argIndex)
		);
	}
}
?>
<?php
/**
* Module Method
*
* @param Object $self
* @param Int $fundid
* @param String $action
* @return String
*
* @usage project/fund[/$orgId][/$action]
*/

function project_fund($self, $orgId = NULL, $action = NULL) {
	$orgId = SG\getFirst(post('orgid'), $orgId);
	$fundInfo = $orgId ? R::Model('project.fund.get', $orgId) : NULL;

	$orgId = $fundInfo->orgid;

	R::view('project.toolbar', $self, 'ระบบบริหารกองทุนสุขภาพตำบล', 'fund', $fundInfo);
	if ($fundInfo) $self->theme->title = $fundInfo->name;

	if (empty($orgId)) $action = 'home';
	else if (empty($action)) $action = 'info.view';

	if (empty($fundInfo)) $fundInfo = $orgId;
	$argIndex = 3; // Start argument

	//debugMsg('PAGE PROJECT Topic = '.$tpid.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
	//$ret .= print_o(func_get_args(), '$args');
	//debugMsg($fundInfo, '$fundInfo');
	//debugMsg(R::Model('project.right.fund', $fundInfo), '$right');
	//debugMsg(R::Model('org.get',$orgId), '$fundInfo');

	$ret = R::Page(
		'project.fund.'.$action,
		$self,
		$fundInfo,
		func_get_arg($argIndex),
		func_get_arg($argIndex+1),
		func_get_arg($argIndex+2),
		func_get_arg($argIndex+3),
		func_get_arg($argIndex+4)
	);

	//debugMsg('TYPE = '.gettype($ret));
	if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';

	return $ret;
}

?>