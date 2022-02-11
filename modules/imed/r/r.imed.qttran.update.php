<?php
function r_imed_qttran_update($data) {
	$result=NULL;
	$stmt='INSERT INTO %qttran%
					(`qtid`, `qtref`, `part`, `value`, `ucreated`, `dcreated`)
					VALUES
					(:qtid, :qtref, :part, :value, :ucreated, :dcreated)
					ON DUPLICATE KEY UPDATE
					`value`=:value,
					`umodify`=:umodify,
					`dmodify`=:dmodify
					';
	mydb::query($stmt,$data);
	$result->data=$data;
	$result->query[]=mydb()->_query;
	return $result;
}
?>