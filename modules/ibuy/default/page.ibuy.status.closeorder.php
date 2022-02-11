<?php
function ibuy_status_closeorder($self,$oid) {
	$ret.='Close order of '.$oid;
	mydb::query('UPDATE %ibuy_order% SET `status`=50 WHERE `oid`=:oid LIMIT 1',':oid',$oid);
//		$ret.=mydb()->_query;
	return $ret;
}
?>