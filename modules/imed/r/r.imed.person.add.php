<?php
function r_imed_person_add($data) {
	if (empty($data->fullname)) return;

	$result['pid']=NULL;
	$result['name']=$data->fullname;
	$result['error']=false;
	$result['complete']=false;

	list($post->name,$post->lname)=sg::explode_name(' ',$data->fullname);

	// Update address
	if (preg_match('/(.*)(หมู่|หมู่ที่|ม\.)([0-9\s]+)\s+(.*)/',$address,$out) || preg_match('/(.*)(ตำบล|ต\.)(.*)/',$address,$out)) {
		$out[3]=trim($out[3]);
		$post->house=trim($out[1]);
		$post->village=(in_array($out[2],array('หมู่','หมู่ที่','ม.')) && is_numeric($out[3]))?$out[3]:'';
		$post->tambon=substr($tambon,4,2);
		$post->ampur=substr($tambon,2,2);
		$post->changwat=substr($tambon,0,2);
	}

	$address=SG\explode_address($address,$areacode);
	$post->house=$address['house'];
	$post->village=$address['village'];
	$post->house=$address['house'];
	$post->village=$address['village'];
	$post->tambon=$address['tambonCode'];
	$post->ampur=$address['ampurCode'];
	$post->changwat=$address['changwatCode'];
	$post->zip=$address['zip'];


	if (empty($post->name) || empty($post->lname)) {
		$result['error']='กรุณาป้อน ชื่อ และ นามสกุล โดยเว้นวรรค 1 เคาะ';
		return $result;
	} else if ($post->cid &&
		$dupcid=mydb::select('SELECT p.`psnid` FROM %db_person% p WHERE `cid`=:cid LIMIT 1',$post)->psnid) {
		$result['error']='หมายเลขบัตรประชาชน "'.$post->cid.'" มีอยู่ในฐานข้อมูลแล้ว';
		return $result;
	} else if ($post->name && $post->lname &&
		$dupid=mydb::select('SELECT p.`psnid` FROM %db_person% p WHERE name=:name AND lname=:lname AND `cid`=:cid LIMIT 1',$post)->psnid) {
		$result['error']='รายชื่อนี้มีอยู่ในฐานข้อมูลแล้ว';
		return $result;
	}
	$post->query[]=$dupid.mydb()->_query;
	//$post->cid=post('cid');
	$post->uid=SG\getFirst(i()->uid,'func.NULL');
	$post->created=date('U');

	/*
	$stmt='INSERT INTO %db_person% (
						  `uid`, `prename`, `name`, `lname`, `cid`
						, `house`, `village`, `tambon`, `ampur`, `changwat`, `zip`
						, `created`
					) VALUES (
						  :uid, :prename, :name, :lname, :cid
						, :house, :village, :tambon, :ampur, :changwat, :zip
						, :created
					)';
	mydb::query($stmt,$post);
	*/
	$post->query[]=$dupid.mydb()->_query;

	if (!mydb()->_error) {
		$result['pid']=$post->pid=$pid=mydb()->insert_id;

		$stmt='INSERT INTO %imed_patient% (`pid`, `uid`, `created`) VALUES (:pid, :uid, :created)';
		mydb::query($stmt,$post);
		$post->query[]=$dupid.mydb()->_query;
	}



	// $result['html']=$this->_individual($post->pid);

	// $result['html'].=print_o($post,'$post').print_o($_REQUEST,'$_REQUEST');


	//				$result['pid']=$post->pid;
	//				$result['post']=print_o($post,'$post');

	//				$stmt='INSERT INTO %imed_disabled% (`pid`, `uid`, `created`) VALUES (:pid, :uid, :created)';
	//				mydb::query($stmt,$post);

	//				location('imed/patient/individual/'.$pid);
	$result['error'].='บันทึกข้อมูลเรียบร้อย'.print_o($post,'$post');
	return $result;
}
?>