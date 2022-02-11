<?php
function r_imed_person_create($data) {
	if (empty($data->fullname)) return;

	$result['pid']=NULL;
	$result['name']=$data->fullname;
	$result['error']=false;
	$result['complete']=false;

	list($data->name,$data->lname)=sg::explode_name(' ',$data->fullname);

	// Update address
	$address=SG\explode_address($data->address,$data->areacode);
	$data->house=$address['house'];
	$data->village=$address['village'];
	$data->village=$address['village'];
	$data->tambon=$address['tambonCode'];
	$data->ampur=$address['ampurCode'];
	$data->changwat=$address['changwatCode'];
	$data->zip=$address['zip'];

	$data->rhouse=$address['house'];
	$data->rvillage=$address['village'];
	$data->rvillage=$address['village'];
	$data->rtambon=$address['tambonCode'];
	$data->rampur=$address['ampurCode'];
	$data->rchangwat=$address['changwatCode'];
	$data->rzip=$address['zip'];


	if (empty($data->name) || empty($data->lname)) {
		$result['error']='กรุณาป้อน ชื่อ และ นามสกุล โดยเว้นวรรค 1 เคาะ';
		return $result;
	} else if ($data->cid &&
		$dupcid=mydb::select('SELECT p.`psnid` FROM %db_person% p WHERE `cid`=:cid LIMIT 1',$data)->psnid) {
		$result['error']='หมายเลขบัตรประชาชน "'.$data->cid.'" มีอยู่ในฐานข้อมูลแล้ว';
		$result['query'][]=mydb()->_query;
		return $result;
	} else if ($data->name && $data->lname &&
		$dupid=mydb::select('SELECT p.`psnid` FROM %db_person% p WHERE name=:name AND lname=:lname AND `cid`=:cid LIMIT 1',$data)->psnid) {
		$result['error']='รายชื่อนี้มีอยู่ในฐานข้อมูลแล้ว';
		$result['query'][]=mydb()->_query;
		return $result;
	}
	//$data->cid=post('cid');
	$data->uid=i()->uid;
	$data->created=date('U');

	$stmt='INSERT INTO %db_person% (
			  `uid`, `prename`, `name`, `lname`, `cid`
			, `house`, `village`, `tambon`, `ampur`, `changwat`, `zip`
			, `rhouse`, `rvillage`, `rtambon`, `rampur`, `rchangwat`, `rzip`
			, `created`
		) VALUES (
			  :uid, :prename, :name, :lname, :cid
			, :house, :village, :tambon, :ampur, :changwat, :zip
			, :rhouse, :rvillage, :rtambon, :rampur, :rchangwat, :rzip
			, :created
		)';

	mydb::query($stmt,$data);

	$result['query'][]=mydb()->_query;

	if (!mydb()->_error) {
		$result['pid']=$data->pid=$pid=mydb()->insert_id;

		$stmt='INSERT INTO %imed_patient% (`pid`, `uid`, `created`) VALUES (:pid, :uid, :created)';
		mydb::query($stmt,$data);
		$result['query'][]=mydb()->_query;
	}

	$result['complete']='บันทึกข้อมูลเรียบร้อย';
	return $result;
}
?>