<?php
/**
 * Get all status
 *
 * @return Array/Ajax
 */
function ibuy_status_get($self) {
	$ret['currentOnline']=SG\getFirst($GLOBALS['online']->count,0);
	$ret['currentMember']=SG\getFirst($GLOBALS['online']->members,0);
	$ret['newMember']=mydb::select('SELECT COUNT(*) total FROM %users% WHERE DATE(`datein`)=CURDATE() LIMIT 1' )->total;
	$ret['newMoney']=mydb::select('SELECT COUNT(*) total FROM %ibuy_log% l WHERE keyword="order" AND status=20 AND process=-1 LIMIT 1')->total;
	$ret['newOrder']=mydb::select('SELECT COUNT(*) total FROM %ibuy_order% WHERE status=0 LIMIT 1')->total;
	return $ret;
}
?>