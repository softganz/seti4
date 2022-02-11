<?php

function on_project_develop_create($projectResult) {
	$tpid = $projectResult->tpid;

	if (empty($tpid)) return 'ERROR : No Project';

	$stmt = 'SELECT * FROM %project_tr% WHERE `tpid` = :tpid AND `formid` = "develop" AND `part` = "supportplan" LIMIT 1';

	$rs = mydb::select($stmt, ':tpid', $tpid);
		$onResult->querys[] = mydb()->_query;

	if ($rs->_empty) {
		$stmt = 'INSERT INTO %project_tr% (`tpid`, `refid`, `formid`, `part`, `uid`) VALUES (:tpid, 7, "develop", "supportplan", :uid)';
		mydb::query($stmt, ':tpid', $tpid, ':uid', i()->uid);
		$onResult->querys[] = mydb()->_query;
	}

	$onResult->status = 'SUCCESS';
	return $onResult;
}