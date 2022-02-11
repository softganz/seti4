<?php
/**
* Remove Project Money Back Transation
* Created 2016-12-07
* Modify  2019-10-01
*
* @param Int $tpid
* @param Int $trid
*/

$debug = true;

function r_project_moneyback_remove($tpid, $trid) {
	//debugMsg('Paiddoc::Remove() tpid='.$tpid.' trid='.$trid);
	if (empty($tpid) || empty($trid)) return false;

	$refcode = R::Model('project.moneyback.get',$tpid,$trid)->refcode;

	$stmt = 'DELETE FROM %project_tr%
		WHERE `tpid` = :tpid AND `trid` = :trid AND `formid` = "info" AND `part` = "moneyback"
		LIMIT 1';
	mydb::query($stmt, ':tpid',$tpid, ':trid',$trid);

	//debugMsg(mydb()->_query);

	if ($refcode) R::Model('project.gl.tran.delete',$refcode);
	
	//debugMsg(mydb()->_query);
	//debugMsg('refcode='.$refcode);

	R::Model('watchdog.log','project','Money Back Remove','Project id '.$tpid.' - Tran '.$trid.' was removed by '.i()->name.'('.i()->uid.')', NULL, $tpid);
}
?>