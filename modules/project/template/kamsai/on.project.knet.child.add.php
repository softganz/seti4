<?php
/**
* Model Name
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function on_project_knet_child_add($data, $result) {
	if ($result->orgid) {
		$data->classlevel = $data->classlevel ? implode(',',$data->classlevel) : NULL;

		// Crete school
		$stmt = 'INSERT INTO %school%
				(`orgid`, `uid`, `networktype`, `studentamt`, `classlevel`, `created`)
				VALUES
				(:orgid, :uid, :networktype, :studentamt, :classlevel, :created)';
		mydb::query($stmt, $data);
		//$ret .= mydb()->_query.'<br />';
	}
	//print_o($data,'$data',1);
	//print_o($result,'$result',1);
	return $ret;
}
?>