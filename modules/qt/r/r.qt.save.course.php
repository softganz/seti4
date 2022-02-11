<?php
function r_qt_save_course($data) {
	//debugMsg($data,'$data');

	if (empty($data->part) || empty($data->qtref)) return;

	if (empty($data->qtid)) $data->qtid=NULL;
	if (empty($data->rate)) $data->rate=NULL;
	if (is_null($data->value)) $data->value=NULL;
	$data->ucreated=$data->umodify=i()->uid;
	$data->dcreated=$data->dmodify=date('U');

	$stmt='INSERT INTO %qttran%
					(`qtid`, `qtref`, `part`, `rate`, `value`, `ucreated`, `dcreated`)
					VALUES
					(:qtid, :qtref, :part, :rate, :value, :ucreated, :dcreated)
					ON DUPLICATE KEY UPDATE
					  `rate`=:rate
					, `value`=:value
					, `umodify`=:umodify
					, `dmodify`=:dmodify
					';
	mydb::query($stmt,$data);
	//debugMsg(mydb()->_query);
}
?>