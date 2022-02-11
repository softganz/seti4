<?php
function imed_app_poorman_form_approve($self,$qtref) {
	$stmt = 'UPDATE %qtmast% SET `qtstatus` = :approve WHERE `qtref` = :qtref LIMIT 1';
	mydb::query($stmt,':qtref',$qtref, ':approve',post('approve'));
	//$ret.=mydb()->_query;

	$data->qtid = post('qtid');
	$data->qtref = $qtref;
	$data->part = 'APPROVE.REMARK';
	$data->value = post('approveremark');
	$data->ucreated = $data->umodify = i()->uid;
	$data->dcreated = $data->dmodify = date('U');

	$result = R::Model('imed.qttran.update',$data);

	//$ret=print_o(post(),'post()');
	//$ret.=print_o($result,'$result');
	//location('imed/app/poorman/list');
	return $ret;
}
?>