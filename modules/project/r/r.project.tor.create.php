<?php
/**
* Create Project TOR
* Created 2018-25-25
* Modify  2019-10-29
*
* @param Object $conditions
* @param Object $options
* @return Object Data Set
*/

$debug = true;

function r_project_tor_create($data, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	if (is_object($data)) {
		// Do nothing
	} else if (is_numeric($data)) {
		$tpid = $data;
		$data = new stdClass();
		$data->tpid = $tpid;
	} else {
		$data = (Object) $data;
	}


	$data->tpid = $tpid;
	$data->date1 = date('Y-m-d');
	$data->created = date('U');
	$data->uid = i()->uid;

	$stmt = 'INSERT INTO %project_tr%
		(`tpid`, `formid`, `part`, `date1`, `uid`, `created`)
		VALUES
		(:tpid, "tor", "title", :date1, :uid, :created)';

	mydb::query($stmt,$data);

	$querys[] = mydb()->_query;

	if (!mydb()->_error) {
		$trid = $data->trid = mydb()->insert_id;
	}

	if ($debug) {
		debugMsg($data,'$data');
		debugMsg($result,'$result');
	}
	
	return $result;
}
?>