<?php
/**
* iMed :: Home Visit Page Controller
* Created 2019-03-11
* Modify  2021-05-28
*
* @param Int $psnId
* @param String $action
* @param Int $seqId
* @return Widget
*
* @usage imed/visit/{psnId}/{action}/{seqId}
*/

import('model:imed.visit');

class ImedVisit extends Page {
	var $psnId;
	var $action;
	var $_args = [];

	function __construct($psnId = NULL, $action = NULL, $seqId = NULL) {
		$this->psnId = $psnId;
		$this->action = $action;
		$this->seqId = $seqId;
		$this->_args = func_get_args();
	}

	function build() {
		// debugMsg('Id '.$this->psnId.' Action = '.$this->action.' seqId = '.$this->seqId);

		if (empty($this->action) && empty($this->psnId)) $this->action = 'home';
		else if (empty($this->action) && $this->psnId) $this->action = 'info';

		$psnInfo = NULL;
		$visitInfo = (Object)['seqId' => $seqId];

		if (is_numeric($this->psnId)) {
			$uid = i()->uid;
			$psnInfo = R::Model('imed.patient.get',$this->psnId);

			$visitInfo = $this->seqId > 0 ? ImedVisitModel::get($this->psnId, $this->seqId) : (Object)['seqId' => $this->seqId];
			$isAccess = $psnInfo->RIGHT & _IS_ACCESS || $visitInfo->uid == $uid;
			$isEdit = is_admin('imed') || $visitInfo->uid == $uid;

			// debugMsg($psnInfo, '$psnInfo');
			// debugMsg($visitInfo, '$visitInfo');

			if (empty($psnInfo)) {
				return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลบุคคลตามที่ระบุ']);
			} else if ($this->seqId > 0 && empty($visitInfo)) {
				return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'ไม่มีข้อมูลเยี่ยมบ้านตามที่ระบุ']);
			} else if ($this->seqId > 0 && !$isAccess) {
				return new ErrorMessage(['code' => _HTTP_ERROR_BAD_REQUEST, 'text' => $psnInfo->error]);
			}
		}

		// if (!$isAccess) message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'access denied']);
		// else if (!$isEdit) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'access denied']);

		// $mainInfo = is_numeric($this->mainId) ? Model::get($this->mainId, '{debug: false}') : NULL;

		// if (empty($this->mainId) && empty($this->action)) $this->action = 'home';
		// else if ($this->mainId && empty($this->action)) $this->action = 'info.home';

		$argIndex = 3;

		// debugMsg('PAGE CONTROLLER Id = '.$this->mainId.' , Action = '.$this->action.' , Arg['.$argIndex.'] = '.$this->_args[$argIndex]);
		// debugMsg($this->_args, '$args');
		// debugMsg(array_slice($this->_args, $argIndex), '$arg');

		return R::PageWidget(
			'imed.visit.'.$this->action,
			[-2 => $psnInfo, -1 => $visitInfo] + array_slice($this->_args, $argIndex)
		);
	}
}
?>
<?php
/**
* iMed :: Home Visit Page Controller
* Created 2019-03-11
* Modify  2021-05-28
*
* @param Integer $psnId
* @param String $action
* @param Integer $seqId
* @return String/Widget
*
* @usage imed/visit/{psnId}/{action}/{seqId}
*/


// TODO:: Remove all switch action, Accept only page controller
function imed_visit($self, $psnId = NULL, $action = NULL, $seqId = NULL) {
	if (empty($action) && empty($psnId)) return R::Page('imed.visit.home');
	else if (empty($action) && $psnId) return R::Page('imed.visit.info', $psnId);


	$uid = i()->uid;
	$psnInfo = R::Model('imed.patient.get',$psnId);

	$visitInfo = $psnId && $seqId>0 ? ImedVisitModel::get($psnId, $seqId) : (Object)['seqId' => $seqId];
	$isAccess = $psnInfo->RIGHT & _IS_ACCESS || $visitInfo->uid == $uid;
	$isEdit = is_admin('imed') || $visitInfo->uid == $uid;

	// debugMsg('psnId = '.$psnId.' Action = '.$action);
	// debugMsg($visitInfo,'$visitInfo');

	if (empty($psnInfo)) return message('error','ไม่มีข้อมูลบุคคลตามที่ระบุ');
	else if ($seqId>0 && empty($visitInfo)) return message('error', 'ไม่มีข้อมูลเยี่ยมบ้านตามที่ระบุ');
	else if ($seqId>0 && !$isAccess) return message('error',$psnInfo->error);

	$argIndex = 3; // Start argument

	//$ret .= 'PAGE IMED psnId = '.$psnId.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex).'<br />';
	//$ret .= print_o(func_get_args(), '$args');


	$ret = R::Page(
		'imed.visit.'.$action,
		$psnInfo,
		$visitInfo,
		func_get_arg($argIndex+1),
		func_get_arg($argIndex+2),
		func_get_arg($argIndex+3),
		func_get_arg($argIndex+4)
	);
	if (is_string($ret) && trim($ret) == '') $ret = 'ERROR : PAGE NOT FOUND';

	return $ret;
}
?>