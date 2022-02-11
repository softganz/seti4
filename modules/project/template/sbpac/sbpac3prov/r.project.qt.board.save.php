<?php
/**
* Project People Qt Save data
*
* @param Object $data
* @return Object
*/

function r_project_qt_board_save($data) {
	$result = NULL;
	$result-> _error = false;

	foreach ($data as $key => $value) if (!is_array($value)) $data->{$key}=trim($value);

	if (empty($data->tpid)) return $result;

	$tranData = is_object($data) ? clone($data) : NULL;

	if (empty($data->qtref)) $data->qtref = NULL;
	$data->qtgroup = _PROJECT_QTGROUP;
	$data->qtform = _PROJECT_QTFORM_BOARD;
	$data->qtdate = sg_date($data->qtdate,'Y-m-d');
	$data->qtstatus = 1;
	$data->uid = i()->uid;
	if (empty($data->collectname)) $data->collectname=NULL;
	$data->created = date('U');

	$stmt = 'INSERT INTO %qtmast% (
					`qtref`
					, `qtgroup`
					, `qtform`
					, `tpid`
					, `qtdate`
					, `qtstatus`
					, `uid`
					, `collectname`
					, `created`
					) VALUES (
					:qtref
					, :qtgroup
					, :qtform
					, :tpid
					, :qtdate
					, :qtstatus
					, :uid
					, :collectname
					, :created
					)
					ON DUPLICATE KEY UPDATE
					`qtref` = :qtref
					, `collectname` = :collectname
					, `qtdate` = :qtdate
					';

	mydb::query($stmt, $data);
	if (empty($data->qtref)) $data->qtref = mydb()->insert_id;

	$result->_query[] = mydb()->_query;

	$notInclude = array('uid', 'qtref', 'tpid', 'qtdate', 'qtstatus', 'qtform', 'qtgroup', 'collectname', 'created', 'umodify', 'dmodify', 'dcreated', 'ucreated');

	if ($tranData->{"POSITION-OTHER"} != '') {
		$tranData->POSITION=$tranData->{"POSITION-OTHER"};
	}
	unset($tranData->{"POSITION-OTHER"});

	if ($tranData->{"RELIGION-OTHER"} != '') {
		$tranData->RELIGION=$tranData->{"RELIGION-OTHER"};
	}
	unset($tranData->{"RELIGION-OTHER"});

	if ($tranData->{"COMMUPOS-OTHER"} != '') {
		$tranData->COMMUPOS=$tranData->{"COMMUPOS-OTHER"};
	}
	unset($tranData->{"COMMUPOS-OTHER"});

	foreach ($tranData as $key => $value) {
		if (in_array($key, $notInclude)) unset($tranData->{$key});
	}

	foreach ($tranData->OCCUPA as $k => $v) $tranData->{'OCCUPA.'.$k} = $v;
	unset($tranData->OCCUPA);

	$oldData = R::Model('project.qt.board.get', $data->tpid, $data->qtref);

	foreach ($tranData as $key => $value) {
		$trData = NULL;
		$trData->qtid = $oldData->trans[$key]->qtid;
		$trData->qtref = $data->qtref;
		$trData->part = $key;
		$trData->value = $value;
		$trData->ucreated = $trData->umodify = i()->uid;
		$trData->dcreated = $trData->dmodify = date('U');

		$stmt = 'INSERT INTO %qttran% (
							`qtid`, `qtref`, `part`, `value`, `ucreated`, `dcreated`
						) VALUE (
							:qtid, :qtref, :part, :value, :ucreated, :dcreated
						)
						ON DUPLICATE KEY UPDATE
						`value` = :value
						, `umodify` = :umodify
						, `dmodify` = :dmodify
						';
		mydb::query($stmt, $trData);
		$result->_query[] = mydb()->_query;
	}

	//debugMsg($tranData,'$tranData');
	return $result;
}
?>