<?php
function r_qt_save($data) {
	//debugMsg($data,'$data');

	if (empty($data->qtgroup) || empty($data->qtform)) return;

	// Create qt_mast
	$data->qtRef = SG\getFirst($data->qtRef, $data->qtref);
	$data->psnId = SG\getFirst($data->psnId, $data->psnid);
	if (empty($data->tpid)) $data->tpid = NULL;
	$data->orgId = SG\getFirst($data->orgId,$data->orgid);
	$data->seqId = SG\getFirst($data->seqId,$data->seqid,$data->seq);
	if (empty($data->qtstatus)) $data->qtstatus = 1;
	if (isset($data->collectname)) {
		$data->collectname = $data->updateCollectname = SG\getFirst($data->collectname);
	} else {
		$data->collectname = NULL;
		$data->updateCollectname = 'func.`collectname`';
	}
	if (isset($data->value)) {
		$data->value = $data->updateValue = SG\getFirst($data->value);
	} else {
		$data->value = NULL;
		$data->updateValue = 'func.`value`';
	}
	if (isset($data->data)) {
		if (is_array($data->data) || is_object($data->data)) {
			$data->data = SG\json_encode($data->data);
		} else {
			$data->data = SG\getFirst($data->data);
		}
		$data->updateData = $data->data;
	} else {
		$data->data = NULL;
		$data->updateData = 'func.`data`';
	}
	$data->uid = i()->uid;
	if (empty($data->qtdate)) $data->qtdate = date('Y-m-d');
	$data->created = SG\getFirst($data->created, date('U'));

	$stmt = 'INSERT INTO %qtmast%
		(`qtRef`, `qtgroup`, `qtform`, `psnId`, `tpid`, `orgId`, `uid`, `seq`, `qtdate`, `qtstatus`, `collectname`, `value`, `data`, `created`)
		VALUES
		(:qtRef, :qtgroup, :qtform, :psnId, :tpid, :orgId, :uid, :seqId, :qtdate, :qtstatus, :collectname, :value, :data, :created)
		ON DUPLICATE KEY UPDATE
		`collectname` = :updateCollectname
		, `value` = :updateValue
		, `data` = :updateData
		, `qtdate` = :qtdate
		';

	mydb::query($stmt,$data);

	$data->msg .= mydb()->_query.'<br />'._NL;

	if (empty($data->qtRef)) $data->qtRef = mydb()->insert_id;

	//debugMsg(mydb()->_query);

	return $data;
}
?>