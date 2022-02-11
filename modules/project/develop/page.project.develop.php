<?php
/**
* Project owner
*
* @param Object $self
* @return String
*/
function project_develop($self, $tpid = NULL, $action = NULL, $tranId = NULL) {

	if (!is_numeric($tpid)) {$action = $tpid; unset($tpid);} // Action as tpid and clear

	if (empty($action) && empty($tpid)) return R::Page('project.develop.home',$self);
	if (empty($action) && $tpid) {
		return R::Page('project.develop.view',$self,$tpid);
	}


	if ($tpid) {
		$stmt = 'SELECT t.*, o.`shortname` FROM %topic% t LEFT JOIN %db_org% o USING(`orgid`) WHERE `tpid` = :tpid LIMIT 1';
		$rs = mydb::select($stmt,':tpid', $tpid);

		$projectInfo = R::Model('project.develop.get', $tpid, '{initTemplate: true}');
		//$orgInfo = $rs->orgid ? R::Model('project.org.get',$rs->orgid) : NULL;

		$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;
	}

	//$ret .= 'Action = '.$action. ' Is edit = '.($isEdit ? 'YES' : 'NO').'<br />';
	//$ret .= print_o($projectInfo, '$projectInfo');
	//$ret .= print_o($orgInfo, '$orgInfo');

	switch ($action) {

		default:
			/*
			// Bug on action/action/action
			$funcName = array();
			foreach (array_slice(func_get_args(),2) as $value) {
				if (is_numeric($value)) break;
				else if (is_string($value)) {
					$funcName[] = $value;
				}
			}
			$argIndex = count($funcName)+2; // Start argument
			*/

			$argIndex = 3; // Start argument
			$args = func_get_args();

			//debugMsg('PAGE PROJECT Topic = '.$tpid.' , Action = '.$action.' , ArgIndex = '.$argIndex.' , Arg 1 = '.func_get_arg($argIndex));
			//$ret .= print_o(func_get_args(), '$args');

			$ret = R::Page(
				'project.develop.'.$action,
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

			//$ret .= R::Page('project.'.$action, $self, $tpid);
			//$ret .= print_o($projectInfo,'$projectInfo');
			//$ret .= message('error', 'Action incorrect');
			break;
	}

	return $ret;
}
?>