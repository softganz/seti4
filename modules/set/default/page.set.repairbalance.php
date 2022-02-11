<?php
function set_repairbalance($self,$symbol) {
	if ($symbol) $ret.=R::Model('set.balance.repair',$symbol);
	return $ret;
}
?>