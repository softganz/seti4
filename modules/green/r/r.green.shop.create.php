<?php
/**
* Model Name
* Created 2020-01-01
* Modify  2020-01-01
*
* @param Object $conditions
* @param Object $options
* @return Object Data Set
*/

$debug = true;

function r_green_shop_create($data, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;
	$result->shopId = NULL;
	$result->data = $data;
	$result->query = array();
	$result->error = false;

	$data->name = SG\getFirst($data->name);
	$data->deedno = SG\getFirst($data->deedno);
	$data->uid = i()->uid;
	$data->created = date('U');
	$data->membership = 'ShopOwner';

	if ($data->name) {
		$stmt = 'INSERT INTO %db_org% (`uid`,`name`,`created`) VALUES (:uid,:name,:created)';

		mydb::query($stmt, $data);
		$result->query[] = mydb()->_query;

		if (!mydb()->_error) {
			$result->shopId = $data->shopId = mydb()->insert_id;

			$stmt = 'INSERT INTO %org_officer% (`orgid`, `uid`, `membership`) VALUES (:shopId, :uid, :membership)';

			mydb::query($stmt, $data);
			$result->query[] = mydb()->_query;


			$stmt = 'INSERT INTO %ibuy_shop% (`shopid`, `uid`, `created`) VALUES (:shopId, :uid, :created)';

			mydb::query($stmt,$data);
			$result->query[] = mydb()->_query;

			// Save Land
			if ($data->landname) {
				$address = SG\explode_address($data->address,$data->areacode);
				$data->house = $address['house'];

				$stmt = 'INSERT INTO %ibuy_farmland%
					(`orgid`, `uid`, `landname`, `deedno`, `house`, `areacode`, `arearai`, `areahan`, `areawa`)
					VALUES
					(:shopId, :uid, :landname, :deedno, :house, :areacode, :arearai, :areahan, :areawa)';
				mydb::query($stmt, $data);
				$result->query[] = mydb()->_query;
			}

			$_SESSION['shopid'] = $result->shopId;
		} else {
			$result->error = 'มีข้อผิดพลาดในการสร้างหน้าร้านใหม่';
		}
	} else {
		$result->error = 'ไม่ระบุชื่อ';
	}
	return $result;
}
?>