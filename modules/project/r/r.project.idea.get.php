<?php
function r_project_idea_get($tpid) {
	$debug=false;
	if (empty($tpid)) return false;

	$stmt='SELECT
				  i.`tpid`
				, t.`title`
				, i.*
				, d.`tpid` `proposalId`
				, p.`tpid` `followId`
				FROM %project_idea% i
					LEFT JOIN %topic% t USING(`tpid`)
					LEFT JOIN %project_dev% d USING(`tpid`)
					LEFT JOIN %project% p USING(`tpid`)
				WHERE i.`tpid`=:tpid
				LIMIT 1;';
	$rs=mydb::select($stmt,':tpid',$tpid);

	if ($rs->_empty) return false;
	return $rs;
}
?>