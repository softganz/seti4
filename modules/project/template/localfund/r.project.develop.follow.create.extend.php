<?php
function r_project_develop_follow_create_extend($devInfo) {
	$tpid = $devInfo->tpid;
	$result = NULL;

	return $result;
	
	$stmt = 'SELECT * FROM %project_tr% WHERE `tpid` = :tpid AND `formid` = "develop" AND `part` = "target" ';
	$devTarget = mydb::select($stmt, ':tpid',$tpid)->items;

	$result->message[] = mydb()->_query;



	foreach ($devTarget as $rs) {
		unset($newRs);
		$stmt = 'INSERT INTO %project_target%
						(`tpid`, `tagname`, `tgtid`, `amount`)
						VALUES
						(:tpid, :tagname, :tgtid, :amount)
						ON DUPLICATE KEY UPDATE
						`amount` = :amount';
		$newRs->tpid = $tpid;
		$newRs->tagname = 'info';
		$newRs->tgtid = SG\getFirst($rs->refid,$rs->detail1);
		$newRs->amount = $rs->num1;

		mydb::query($stmt,$newRs);
		$result->message[] = mydb()->_query;
	}
	$result->message['$devTarget'] = mydb::printtable($devTarget);

	return $result;
}
?>