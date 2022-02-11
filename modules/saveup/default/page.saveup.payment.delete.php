<?php
function saveup_payment_delete($self,$id) {
	$isAdmin=is_admin('saveup');
	if ($isAdmin && $id && SG\confirm()) {
		$stmt='DELETE FROM %saveup_log% WHERE `lid`=:id LIMIT 1';
		mydb::query($stmt,':id',$id);
	}
	return $ret;
}
?>