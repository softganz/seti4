<?php
function school_create($self) {
	R::View('school.toolbar',$self,'Create New School');

	$data=(object)post('data');
	if ($data->name) {
		$ret.='Create ';
		$data->uid=i()->uid;
		$data->created=date('U');
		$data->sector=9;

		$stmt='INSERT INTO %db_org% (`name`,`uid`,`address`,`created`) VALUES (:name,:uid,:address,:created)';
		mydb::query($stmt,$data);
		//$ret.=mydb()->_query.'<br />';

		$data->orgid=mydb()->insert_id;

		$stmt='INSERT INTO %school% (`orgid`,`uid`,`created`) VALUES (:orgid,:uid,:created)';
		mydb::query($stmt,$data);
		//$ret.=mydb()->_query.'<br />';
		location('school/my');
	} else {
		$ret.=R::View('school.register.form');
	}
	//$ret.=print_o($data,'$data');
	return $ret;
}
?>