<?php
function r_project_develop_get_data($tpid) {
	$data=array();
	$stmt='SELECT `fldname`,`flddata` FROM %bigdata% WHERE `keyid`=:tpid AND `keyname`="project.develop" ORDER BY `fldname` ASC';
	foreach (mydb::select($stmt,':tpid',$tpid)->items as $item) {
		$data[$item->fldname]=$item->flddata;
	}
	return $data;
}
?>