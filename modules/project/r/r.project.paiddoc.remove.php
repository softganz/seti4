<?php
function r_project_paiddoc_remove($tpid,$trid) {
	//debugMsg('Paiddoc::Remove() tpid='.$tpid.' trid='.$trid);
	if (empty($tpid) || empty($trid)) return false;

	$paiddoc = R::Model('project.paiddoc.get',$tpid,$trid);

	if (empty($paiddoc->paidid)) return false;

	// Delete paiddoc transaction
	$stmt='DELETE FROM %project_paiddoc% WHERE `tpid`=:tpid AND `paidid`=:trid';
	mydb::query($stmt, ':tpid',$tpid, ':trid',$trid);
	//debugMsg(mydb()->_query);

	// Delete file gallery
	if ($paiddoc->gallery) R::Model('gallery.remove',$paiddoc->gallery);

	// Delete GL Transaction
	if ($paiddoc->refcode) R::Model('project.gl.tran.delete',$paiddoc->refcode);
	
	//debugMsg(mydb()->_query);
	//debugMsg($paiddoc,'$paiddoc');
	//debugMsg('refcode='.$refcode);

	R::Model('watchdog.log','project','Paid Doc Remove','Project id '.$tpid.' - Tran '.$trid.'/'.$paiddoc->refcode.' Amount '.$paiddoc->amount.' by '.i()->name.'('.i()->uid.')', NULL, $tpid);

}
?>