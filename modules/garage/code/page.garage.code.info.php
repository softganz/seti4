<?php
/**
* Garage Code Control : Code Information Control
* Created 2020-10-14
* Modify  2020-10-14
*
* @param Object $self
* @param String $action
* @param Int $tranId
* @return String
*
* @usage garage/code/info/{$action}/[/{$tranId}]
*/

$debug = true;

function garage_code_info($self, $action, $tranId = NULL) {
	$shopInfo = R::Model('garage.get.shop');

	R::Model('garage.verify',$self, $shopInfo,'CODE');

	$shopId = $shopInfo->shopid;

	$ret = '';

	switch ($action) {
	
		case 'customer.save':
			$data = (Object) post('data');
			$data->customerid = SG\getFirst($tranId);
			$data->shopid = $shopId;
			$address = SG\explode_address($data->address);
			$data->house = $address['house'];
			$data->areacode = $address['areaCode'];
			$stmt = 'INSERT INTO %garage_customer%
				(`customerid`, `shopid`, `prename`, `customername`, `areacode`, `house`, `customerphone`, `customermail`, `remark`)
				VALUES
				(:customerid, :shopid, `prename`, :customername, :areacode, :house, :customerphone, :customermail, :remark)
				ON DUPLICATE KEY UPDATE
				`prename` = :prename
				, `customername` = :customername
				, `areacode` = :areacode
				, `house` = :house
				, `customerphone` = :customerphone
				, `customermail` = :customermail
				, `remark` = :remark
				';
			mydb::query($stmt, $data);
			if (empty($data->customerid)) $data->customerid = mydb()->insert_id;
			$ret = $data;

			//$ret .= mydb()->_query;
			//$ret .= print_o($address,'$address');
			//$ret .= print_o($data,'$data');
			break;

		default:
			$ret = 'ERROR!!! No Action';
			break;

	}

	//$ret .= print_o($shopInfo,'$shopInfo');
	return $ret;
}
?>