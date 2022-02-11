<?php
function r_org_supplier_get($supplierId) {
	$result = R::Model('org.get',$supplierId);

	$qtref=mydb::select('SELECT `qtref` FROM %qtmast% WHERE `qtform`=6 AND `orgid`=:orgid LIMIT 1',':orgid',$supplierId)->qtref;
	//debugMsg(mydb()->_query);

	$result->qt=R::Model('org.gogreen.qt.get',$qtref);

	return $result;	
}
?>