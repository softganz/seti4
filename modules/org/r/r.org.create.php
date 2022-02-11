<?php
/**
* Create Organization
*
* @param Object $data
* @return Object $options
*/

function r_org_create($data, $options = '{}') {
	$defaults = '{debug: false, createOfficer: false}';
	$options = SG\json_decode($options, $defaults);
	$debug = $options->debug;

	$result = (Object) [
		'_error' => NULL,
		'orgid' => NULL,
		'data' => NULL,
		'_query' => [],
	];

	if (is_array($data)) $data = (Object) $data;

	$result->data = $data;

	$data->uid = i()->uid;

	if (empty($data->orgid)) {
		$data->shortname = trim($data->shortname);
		if (empty($data->parent)) $data->parent = NULL;
		if (empty($data->name)) $data->name = NULL;
		if (empty($data->shortname)) $data->shortname = NULL;
		if (empty($data->sector)) $data->sector = NULL;
		if (empty($data->phone)) $data->phone = NULL;
		if (empty($data->email)) $data->email = NULL;
		if (empty($data->managername)) $data->managername = NULL;
		if (empty($data->contactname)) $data->contactname = NULL;
		$data->created = date('U');
		$data->areacode = SG\getFirst($data->areacode);

		if ($data->address) {
			$address = SG\explode_address($data->address, $data->areacode);
			$data->house = $address['house'];
			$data->areacode = $address['areaCode'];
			if ($address['zip'] && !$data->zip) $data->zip = $address['zip'];
		} else {
			$data->areacode = NULL;
			$data->house = '';
		}

		if (empty($data->zip)) $data->zip = NULL;

		$stmt='INSERT INTO %db_org%
			(
			  `parent`, `uid`, `name`, `shortname`, `sector`
			, `areacode`, `house`, `zipcode`
			, `phone`, `email`
			, `managername`, `contactname`
			, `created`
			)
			VALUES
			(
			  :parent, :uid, :name, :shortname, :sector
			, :areacode, :house, :zip
			, :phone, :email
			, :managername, :contactname
			, :created
		)';

		mydb::query($stmt, $data);

		$result->_query[] = mydb()->_query;

		if (mydb()->_error) {
			$data->orgid = NULL;
			$result->_error = mydb()->_error;
			return $data;
		}
		$data->orgid = mydb()->insert_id;
	}

	$result->orgid = $data->orgid;

	if ($data->orgid && $data->uid && $data->officer) {
		$stmt = 'INSERT INTO %org_officer% (`orgid`,`uid`,`membership`) VALUES (:orgid,:uid,:officer)';
		mydb::query($stmt,$data);
		$result->_query[] = mydb()->_query;
	}


	// debugMsg($data,'$data');
	//debugMsg(mydb()->_query);
	return $result;
}
?>