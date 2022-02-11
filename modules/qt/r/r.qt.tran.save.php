<?php
function r_qt_tran_save($data) {
	//debugMsg($data, '$data');

	if (empty($data->qtref) || empty($data->part)) return;

	// Create qttran item
	$stmt = 'SELECT * FROM %qttran% WHERE `qtref` = :qtref AND `part` = :part LIMIT 1';
	$oldRs = mydb::select($stmt, $data);
	$data->msg .= mydb()->_query.'<br />'._NL;

	if (empty($data->qtid)) $data->qtid = $oldRs->qtid;
	if (empty($data->rate)) $data->rate = NULL;
	if (empty($data->value)) $data->value = NULL;
	$data->ucreated = $data->umodify = date('U');
	$data->dcreated = $data->dmodify = date('U');

	$stmt = 'INSERT INTO %qttran%
		(`qtid`, `qtref`, `part`, `rate`, `value`, `ucreated`, `dcreated`)
		VALUES
		(:qtid, :qtref, :part, :rate, :value, :ucreated, :dcreated)
		ON DUPLICATE KEY UPDATE
		  `rate` = :rate
		, `value` = :value
		, `umodify` = :umodify
		, `dmodify` = :dmodify
		';

	mydb::query($stmt,$data);
	//debugMsg($data,'$data');

	$data->msg .= mydb()->_query.'<br />'._NL;

	if (empty($data->qtid)) $data->qtid = mydb()->insert_id;

	//debugMsg(mydb()->_query);

	return $data;
}
?>