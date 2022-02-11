<?php
/**
* Project :: Page Controller
* Created 2021-09-27
* Modify 	2021-09-27
*
* @param Int $projectId
* @param String $action
* @return Widget
*
* @usage project[/{id}[/{action}[/{tranId}]]]
*/

$debug = true;

import('model:project.php');

class Project extends Page {
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
		if (substr($this->projectId, -1) == '*') list($this->projectId, $isProjectAllType) = array(substr($this->projectId,0,-1),true);

		// $isAccess = $projectInfo->RIGHT & _IS_ACCESS;
		// $isEdit = $projectInfo->RIGHT & _IS_EDITABLE;

		// if (!$isAccess) message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'access denied']);
		// else if (!$isEdit) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'access denied']);

		$projectInfo = is_numeric($this->projectId) ? ProjectModel::get($this->projectId, '{initTemplate: true, debug: false'.($isProjectAllType ? ', type: "*"' : '').'}') : NULL;

		if (empty($this->projectId) && empty($this->action)) $this->action = 'home';
		else if ($this->projectId && empty($this->action)) {
			$projectType = mydb::select('SELECT `prtype` FROM %project% WHERE `tpid` = :tpid LIMIT 1', ':tpid', $this->projectId)->prtype;
			if ($projectType == 'แผนงาน') location('project/planning/'.$this->projectId);
			if ($projectType == 'ชุดโครงการ') location('project/set/'.$this->projectId);
			else $this->action = 'info.view';
		}

		$argIndex = 2;

		// debugMsg('PAGE CONTROLLER Id = '.$this->projectId.' , Action = '.$this->action.' Arg['.$argIndex.'] = '.$this->_args[$argIndex]);
		// debugMsg($this->_args, '$args');
		// debugMsg([-1 => $projectInfo] + array_slice($this->_args, $argIndex), '_args');

		return R::PageWidget(
			'project.'.$this->action,
			[-1 => $projectInfo] + array_slice($this->_args, $argIndex)
		);
	}
}
?>
<?php
// NOT USED :: Please remove
function project($self, $projectId = NULL, $action = NULL, $tranId = NULL) {
	if (substr($projectId, -1) == '*') list($projectId, $isProjectAllType) = array(substr($projectId,0,-1),true);

	if (!is_numeric($projectId)) {$action = $projectId; unset($projectId);} // Action as tpid and clear

	if (empty($action) && empty($projectId)) return R::Page('project.home',$self);
	if (empty($action) && $projectId) {
		$projectType = mydb::select('SELECT `prtype` FROM %project% WHERE `tpid` = :tpid LIMIT 1', ':tpid', $projectId)->prtype;
		if ($projectType == 'แผนงาน') location('project/planning/'.$projectId);
		if ($projectType == 'ชุดโครงการ') location('project/set/'.$projectId);
		else {
			R::Module('project.template',$self,$projectId);
			return R::Page('project.view',$self,$projectId);
		}
	}

	if ($projectId) {
		$stmt = 'SELECT t.*, o.`shortname` FROM %topic% t LEFT JOIN %db_org% o USING(`orgid`) WHERE `tpid` = :tpid LIMIT 1';
		$rs = mydb::select($stmt,':tpid', $projectId);

		$projectInfo = R::Model('project.get', $projectId, '{initTemplate: true, type: "'.($isProjectAllType ? '*' : '').'"}');
		$orgInfo = $rs->orgid ? R::Model('project.org.get',$rs->orgid) : NULL;

		$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;
	}

	$isCreatable = user_access('create project');

	//$ret .= 'Action = '.$action. ' Is edit = '.($isEdit ? 'YES' : 'NO').'<br />';
	//$ret .= print_o($planningInfo, '$planningInfo');
	//$ret .= print_o($orgInfo, '$orgInfo');

	switch ($action) {
		case 'create':
			$data = (object) post('data');
			if ($isCreatable && $data->title) {
				$data->prtype = 'โครงการ';
				$data->ischild = 0;

				$result = R::Model('project.create', $data);
				$projectId = $result->tpid;

				//$ret .= print_o($result,'$result');

				if ($result->tpid) {
					location('project/'.$result->tpid);
				} else {
					$ret .= message('error', 'Error on create project');
				}

				// Create planning group
				//$stmt='INSERT INTO %project_tr% (`tpid`,`refid`,`formid`,`part`,`uid`,`created`) VALUES (:tpid,:refid,"planning","title",:uid,:created)';
				//mydb::query($stmt,':tpid',$projectId, ':refid',$data->group, ':uid',i()->uid, ':created',date('U'));

				//$ret.=print_o($data,'$data');
				//$ret.=print_o($fundInfo);
				//$ret.=R::View('project.planning.view',$fundInfo,$data);
			} else {
				$ret .= message('error', 'Call create project but invalid Data');
			}
			break;

		default:
			if (empty($projectInfo)) $projectInfo = $projectId;
			$args = func_get_args();
			$argIndex = 3; // Start argument

			//debugMsg('PAGE PROJECT Topic = '.$projectId.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
			//debugMsg($args, '$args');

			$ret = R::Page(
				'project.'.$action,
				$self,
				$projectInfo,
				$args[$argIndex],
				$args[$argIndex+1],
				$args[$argIndex+2],
				$args[$argIndex+3],
				$args[$argIndex+4]
			);

			//debugMsg('TYPE = '.gettype($ret));
			if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';

			//$ret .= R::Page('project.'.$action, $self, $projectId);
			//$ret .= print_o($projectInfo,'$projectInfo');
			//$ret .= message('error', 'Action incorrect');
			break;
	}

	return $ret;
}
?>
<?php
/**
* Module :: Page Controller
* Created 2021-01-01
* Modify 	2021-01-01
*
* @param Int $mainId
* @param String $action
* @return Widget
*
* @usage module[/{id}/{action}/{tranId}]
*/

class PageController extends Page {
	var $mainId;
	var $action;
	var $_args = [];

	function __construct($mainId = NULL, $action = NULL) {
		$this->mainId = $mainId;
		$this->action = $action;
		$this->_args = func_get_args();
	}

	function build() {
		debugMsg('Id '.$this->mainId.' Action = '.$this->action.' TranId = '.$this->tranId);

		// $isAccess = $mainInfo->RIGHT & _IS_ACCESS;
		// $isEdit = $mainInfo->RIGHT & _IS_EDITABLE;

		// if (!$isAccess) message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'access denied']);
		// else if (!$isEdit) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'access denied']);

		$mainInfo = is_numeric($this->mainId) ? Model::get($this->mainId, '{debug: false}') : NULL;

		if (empty($this->mainId) && empty($this->action)) $this->action = 'home';
		else if ($this->mainId && empty($this->action)) $this->action = 'info.home';

		$argIndex = 2;

		// debugMsg('PAGE CONTROLLER Id = '.$this->mainId.' , Action = '.$this->action.' , Arg['.$argIndex.'] = '.$this->_args[$argIndex]);
		//debugMsg($this->_args, '$args');

		return R::PageWidget(
			'page.sub.'.$this->action,
			[-1 => $mainInfo] + array_slice($this->_args, $argIndex)
		);
	}
}
?>