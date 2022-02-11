<?php
/**
 * Process & status log
 *
 * @param Object $data
 * @param Object $options
 * @return Object
 */
function r_saveup_transfer_add($data, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = new stdClass;
	$result->success = false;

	$data = para(func_get_args(),'keyword=','kid=0','status=-1','created='.date('U'),'detail=รายละเอียด','process=-1','amt=func.NULL');

	unset($data->_src);

	$data->poster = preg_replace('/\s+/', ' ', trim($data->poster));
	$data->amt = sg_strip_money($data->amt);
	$data->uid = i()->uid;

	if (is_array($data->time)) {
		$data->transtime = $data->time['hour'].':'.$data->time['min'];
	} else {
		$data->transtime = $data->time;
	}
	$result->log = 'ชื่อผู้โอนเงิน '.$data->poster._NL.'โอนเงินทาง '.$data->payacc._NL.'เมื่อวันที่ '.$data->date['date'].'-'.$data->date['month'].'-'.$data->date['year'].' เวลา '.$data->transtime.' น.'._NL.'จำนวนเงิน '.number_format($data->amt,2).' บาท'._NL.'รายละเอียด'._NL.$data->remark;

	$data->detail = $result->log;

	$stmt='INSERT INTO %saveup_log%
						(`uid`, `keyword`, `kid`, `status`, `poster`, `amt`, `process`, `created`, `detail`)
					VALUES
						(:uid, :keyword, :kid, :status, :poster, :amt, :process, :created, :detail)';
	mydb::query($stmt, $data);
	if (empty($data->lid) && !mydb()->_error)
		$data->lid = mydb()->insert_id;

	$result->success = true;

	$result->data = $data;

	if ($debug) debugMsg($_FILES, '$_FILES');
	if ($debug) debugMsg($data, '$data');
	if ($debug) debugMsg(mydb()->_query);

	return $result;
}
?>