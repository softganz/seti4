<?php
/**
* Project Join Save Recieve
*
* @param Object $data
* @return Object $options
*/

function r_org_dopaid_save($data, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = (Object) [
		'success' => true,
		'data' => NULL,
		'tr' => [],
		'updatetotal' => NULL,
		'query' => [],
	];

	if (empty($data->dopid)) $data->dopid = NULL;
	$data->formid = SG\getFirst($data->formid);
	$data->paiddate = sg_date($data->paiddate, 'Y-m-d');
	$data->uid = i()->uid;
	$data->created = date('U');

	// Create or Update DoPaid Master
	$stmt = 'INSERT INTO %org_dopaid%
					(`dopid`, `doid`, `psnid`, `uid`, `formid`, `paiddate`, `agrno`, `address`, `paidname`, `created`)
				VALUES
					(:dopid, :doid, :psnid, :uid, :formid, :paiddate, :agrno, :address, :paidname, :created)
				ON DUPLICATE KEY UPDATE
					`paiddate` = :paiddate
				, `formid` = :formid
				, `paidname` = :paidname
				, `address` = :address
				';

	mydb::query($stmt, $data);
	if ($debug) $result->query[] = mydb()->_query;

	if (mydb()->_error) {
		$result->success = false;
	} elseif (empty($data->dopid)) {
		$data->dopid = mydb()->insert_id;
	}

	if (empty($data->dopid)) return $result;


	$result->data = $data;

	// Insert or Update DoPaid Transaction
	if ($data->tr) {
		foreach ($data->tr as $key => $value) {
			if ($value['amt'] <= 0) continue;
			$value['dopid'] = $data->dopid;
			$value['amt'] = sg_strip_money($value['amt']);
			$stmt = 'INSERT INTO %org_dopaidtr%
							(`dopid`, `catid`, `detail`, `amt`)
							VALUES
							(:dopid, :catid, :detail, :amt)';
			mydb::query($stmt, $value);
			if ($debug) $result->query[] = mydb()->_query;
			$result->tr[] = $value;
		}
	}

	$result->updatetotal = R::Model('org.dopaid.update.total', $data->dopid);

	unset($result->data->tr);
	return $result;
}
?>