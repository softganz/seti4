<?php
function r_project_gl_tran_add($data) {
	$data->uid = $data->uid ? $data->uid : i()->uid;
	if (empty($data->tpid)) $data->tpid=NULL;
	if (empty($data->actid)) $data->actid=NULL;
	$data->closed=0;
	$data->created=date('U');
	$data->modified=date('U');
	$data->modifyby=i()->uid;
	$items=$data->items;
	unset($data->items);

	$result=array();
	foreach ($items as $item) {
		$data->pglid = empty($item['pglid']) ? NULL : $item['pglid'];
		$data->glcode = $item['glcode'];
		$data->amount = $item['amount'];

		$stmt = 'INSERT INTO %project_gl% (
			  `pglid`, `uid`, `orgid`, `tpid`, `actid`
			, `refcode`, `refdate`, `glcode`, `amount`
			, `closed`, `created`
			) VALUES (
			  :pglid, :uid, :orgid, :tpid, :actid
			, :refcode, :refdate, :glcode, :amount
			, :closed, :created
			) ON DUPLICATE KEY UPDATE
				`refdate` = :refdate
			, `glcode` = :glcode
			, `amount` = :amount
			, `modified` = :modified
			, `modifyby` = :modifyby
			';

		mydb::query($stmt,$data);

		$result[] = empty($item['pglid']) ? mydb()->insert_id : $item['pglid'];

		//debugMsg(mydb()->_query);
		//if (mydb()->_error) debugMsg(mydb()->_error);
		//debugMsg($data,'$data');
	}
	//debugMsg($items,'$items');
	return $result;
}
?>