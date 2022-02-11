<?php
function project_money_dopaiddeltr($self,$projectInfo,$doptrid) {
	$tpid=$projectInfo->tpid;

	$isEdit=true;

	$stmt='SELECT `dopid` FROM %org_dopaidtr% WHERE `doptrid`=:doptrid LIMIT 1';
	$dopid=mydb::select($stmt, ':doptrid',$doptrid)->dopid;

	$stmt='DELETE FROM %org_dopaidtr% WHERE `doptrid`=:doptrid LIMIT 1';
	mydb::query($stmt, ':doptrid',$doptrid);

	$stmt='UPDATE %org_dopaid% d
				INNER JOIN (SELECT `dopid`, SUM(`amt`) `totalAmt` FROM %org_dopaidtr% tr WHERE tr.`dopid`=:dopid) b ON b.`dopid`=d.`dopid`
				SET d.`total`=b.`totalAmt`
				WHERE d.`dopid`=:dopid';
	mydb::query($stmt, ':dopid',$dopid);


	//$ret.=mydb()->_query;

	$ret.='ลบรายการเรียบร้อย';
	return $ret;
}
?>