<?php
function r_school_summary_weight_duplicate($orgid,$trid,$year,$termperiod) {
	list($term,$period)=explode(':',$termperiod);
	$stmt='SELECT * FROM %project_tr% WHERE `tpid`=:tpid AND `formid`=:formid AND `part`="title" AND `detail1`=:year AND `detail2`=:term AND `period`=:period '.($trid ? 'AND `trid`!=:trid':'').' LIMIT 1';
	//$rs=mydb::select($stmt,':tpid',$tpid, ':trid',$trid, ':formid',_KAMSAIINDICATOR, ':area',$area, ':year',$year, ':term',$term, ':period',$period);
	$isDup=$rs->trid?$rs->trid:false;
	return $isDup;
}
?>