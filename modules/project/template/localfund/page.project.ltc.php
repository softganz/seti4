<?php
/**
* Project :: LTC Controller
* Created 2021-10-04
* Modify 	2021-10-04
*
* @param Int $orgId
* @param String $action
* @return Widget
*
* @usage project/ltc[/{id}/{action}/{tranId}]
*/

$debug = true;

class ProjectLtc extends Page {
	var $orgId;
	var $action;
	var $_args = [];

	function __construct($orgId = NULL, $action = NULL) {
		$this->orgId = $orgId;
		$this->action = $action;
		$this->_args = func_get_args();
	}

	function build() {
		// debugMsg('Id '.$this->orgId.' Action = '.$this->action.' TranId = '.$this->tranId);

		// $isAccess = $fundInfo->RIGHT & _IS_ACCESS;
		// $isEdit = $fundInfo->RIGHT & _IS_EDITABLE;

		// if (!$isAccess) message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'access denied']);
		// else if (!$isEdit) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'access denied']);

		// $fundInfo = is_numeric($this->orgId) ? Model::get($this->orgId, '{debug: false}') : NULL;
		$this->orgId = ($fundInfo = R::Model('project.fund.get',$this->orgId)) ? $fundInfo->orgId : die('PROCESS ERROR');

		if (empty($this->orgId) && empty($this->action)) $this->action = 'home';
		else if ($this->orgId && empty($this->action)) $this->action = 'view';

		$argIndex = 2;

		// debugMsg('PAGE CONTROLLER Id = '.$this->orgId.' , Action = '.$this->action.' , Arg['.$argIndex.'] = '.$this->_args[$argIndex]);
		//debugMsg($this->_args, '$args');

		return R::PageWidget(
			'project.ltc.'.$this->action,
			[-1 => $fundInfo] + array_slice($this->_args, $argIndex)
		);
	}
}
?>
<?php
/**
* Project LTC Page Model
*
* @param Object $self
* @param Int $orgId
* @return String
*/
function project_ltc($self, $orgId = NULL, $action = NULL, $tranId = NULL) {
	if (empty($action) && empty($orgId)) return R::Page('project.ltc.home',$self);

	$orgId = ($fundInfo = R::Model('project.fund.get',$orgId)) ? $fundInfo->orgid : die('PROCESS ERROR');

	switch ($action) {

		default:
			if (empty($action)) $action = 'view';

			$argIndex = 3; // Start argument

			//debugMsg('PAGE PROJECT Topic = '.$tpid.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
			//$ret .= print_o(func_get_args(), '$args');

			$ret = R::Page(
				'project.ltc.'.$action,
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

			//$ret .= R::Page('project.'.$action, $self, $tpid);
			//$ret .= print_o($projectInfo,'$projectInfo');
			//$ret .= message('error', 'Action incorrect');
			break;
	}

	return $ret;
}
?>