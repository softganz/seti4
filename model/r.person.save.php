<?php
/**
* Person Save
*
* @param Object $data
* @return Object
*/

function r_person_save($data, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;
	$result->psnid = NULL;

	if (empty($data->psnid)) $data->psnid = NULL;

	if(property_exists($data, 'prename')) $updateFields[] = '`prename` = :prename';
	if(property_exists($data, 'firstname')) $updateFields[] = '`name` = :firstname';
	if(property_exists($data, 'lastname')) $updateFields[] = '`lname` = :lastname';
	if(property_exists($data, 'cid')) $updateFields[] = '`cid` = :cid';
	if(property_exists($data, 'sex')) $updateFields[] = '`sex` = :sex';
	if(property_exists($data, 'birth')) $updateFields[] = '`birth` = :birth';
	if(property_exists($data, 'religion')) $updateFields[] = '`religion` = :religion';
	if(property_exists($data, 'zip')) $updateFields[] = '`zip` = :zip';
	if(property_exists($data, 'phone')) $updateFields[] = '`phone` = :phone';
	if(property_exists($data, 'graduated')) $updateFields[] = '`graduated` = :graduated';
	if(property_exists($data, 'faculty')) $updateFields[] = '`faculty` = :faculty';
	if(property_exists($data, 'address')) {
		$updateFields[] = '`areacode` = :areacode';
		$updateFields[] = '`house` = :house';
		$updateFields[] = '`village` = :village';
		$updateFields[] = '`tambon` = :tambon';
		$updateFields[] = '`ampur` = :ampur';
		$updateFields[] = '`changwat` = :changwat';
	}

	if (empty($data->cid)) $data->cid = NULL;

	if (empty($data->religion)) $data->religion = NULL;

	$data->email = SG\getFirst($data->email);
	$data->areacode = SG\getFirst($data->areacode);
	$data->hrareacode = SG\getFirst($data->hrareacode);

	if ($data->address) {
		$addrList = SG\explode_address($data->address,$data->areacode);
		$result->address = $addrList;
		if ($addrList['house']) $data->house = $addrList['house'];
		if ($addrList['village']) $data->village = $addrList['village'];
		if (strlen($data->areacode) == 6 && $data->village) $data->areacode .= sprintf('%02d', $data->village);
	}

	if ($data->birth && is_array($data->birth)) {
		$data->birth = $data->birth['year'].'-'.$data->birth['month'].'-'.$data->birth['date'];
	};

	$data->birth = $data->birth ? sg_date($data->birth, 'Y-m-d') : NULL;

	if (empty($data->sex)) $data->sex = NULL;

	if (empty($data->house)) $data->house = '';
	if (empty($data->village)) $data->village = '';
	if (empty($data->tambon)) $data->tambon = '';
	if (empty($data->ampur)) $data->ampur = '';
	if (empty($data->changwat)) $data->changwat = '';
	if (empty($data->zip)) $data->zip = '';

	if (empty($data->rhouse)) $data->rhouse = '';
	if (empty($data->rvillage)) $data->rvillage = '';
	if (empty($data->rtambon)) $data->rtambon = '';
	if (empty($data->rampur)) $data->rampur = '';
	if (empty($data->rchangwat)) $data->rchangwat = '';
	if (empty($data->rzip)) $data->rzip = '';

	if (empty($data->phone)) $data->phone = '';

	$data->uid = SG\getFirst($data->uid, i()->uid, NULL);
	$data->created = date('U');
	$data->userId = SG\getFirst($data->userId, NULL);

	$stmt = 'INSERT INTO %db_person%
		(
			  `psnid`
			, `uid`, `cid`, `prename`, `name`, `lname`, `sex`
			, `birth`, `religion`
			, `areacode`, `hrareacode`
			, `house`, `village`, `tambon`, `ampur`, `changwat`, `zip`
			, `rhouse`, `rvillage`, `rtambon`, `rampur`, `rchangwat`
			, `graduated`, `faculty`
			, `phone`, `email`
			, `created`, `userid`
		) VALUES (
			  :psnid
			, :uid, :cid, :prename, :firstname, :lastname, :sex
			, :birth, :religion
			, :areacode, :hrareacode
			, :house, :village, :tambon, :ampur, :changwat, :zip
			, :rhouse, :rvillage, :rtambon, :rampur, :rchangwat
			, :graduated, :faculty
			, :phone, :email
			, :created, :userid
		) ON DUPLICATE KEY UPDATE
		'.implode(', ' , $updateFields);

	mydb::query($stmt,$data);

	$result->_query[] = mydb()->_query;

	if (mydb()->_error) {
		$result->_error = mydb()->_error;
		return $result;
	}

	if (empty($data->psnid)) $data->psnid = mydb()->insert_id;

	$result->psnid = $data->psnid;

	return $result;
}
?>