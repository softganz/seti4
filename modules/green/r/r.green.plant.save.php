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

function r_green_plant_save($data, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	if (!($data->productname || $data->landid)) return false;

	$result = new stdClass();
	$result->plantId = NULL;
	$result->data = NULL;
	$result->query = NULL;

	$landInfo = mydb::select('SELECT `orgid`, `standard`, `approved` FROM %ibuy_farmland% WHERE `landid` = :landid LIMIT 1', ':landid', $data->landid);

	$result->query[] = mydb()->_query;

	$data->plantid = SG\getFirst($data->plantid);
	$data->orgid = SG\getFirst($data->orgid, $landInfo->orgid);
	$data->tagname = SG\getFirst($data->tagname);
	$data->catid = SG\getFirst($data->catid);
	$data->treelevel = SG\getFirst($data->treelevel);
	$data->productcode = SG\getFirst($data->productcode);
	$data->standard = $landInfo->standard;
	$data->approved = $landInfo->approved;
	$data->startdate = sg_date($data->startdate, 'Y-m-d');
	$data->startage = SG\getFirst($data->startage);
	$data->cropdate = sg_date($data->cropdate, 'Y-m-d');
	$data->safety = SG\getFirst($data->safety);
	$data->qty = sg_strip_money($data->qty);
	$data->unit = SG\getFirst($data->unit);
	$data->saleprice = sg_strip_money($data->saleprice);
	$data->bookprice = sg_strip_money($data->bookprice);
	$data->detail = SG\getFirst($data->detail);
	$data->uid = i()->uid;
	$data->created = date('U');

	if ($data->saleprice == 0) $data->saleprice = NULL;
	if ($data->bookprice == 0) $data->bookprice = NULL;

	$stmt = 'INSERT INTO %ibuy_farmplant%
		(
		`plantid`, `orgid`, `uid`
		, `landid`, `standard`, `approved`
		, `tagname`, `catid`, `treelevel`
		, `productname`, `productcode`, `startdate`, `startage`, `cropdate`
		, `qty`, `unit`, `saleprice`, `bookprice`
		, `safety`, `detail`, `created`
		)
		VALUES
		(
		:plantid, :orgid, :uid
		, :landid, :standard, :approved
		, :tagname, :catid, :treelevel
		, :productname, :productcode, :startdate, :startage, :cropdate
		, :qty, :unit, :saleprice, :bookprice
		, :safety, :detail, :created
		)
		ON DUPLICATE KEY UPDATE
		  `landid` = :landid
		, `catid` = :catid
		, `treelevel` = :treelevel
		, `productname` = :productname
		, `productcode` = :productcode
		, `startdate` = :startdate
		, `startage` = :startage
		, `cropdate` = :cropdate
		, `qty` = :qty
		, `unit` = :unit
		, `saleprice` = :saleprice
		, `bookprice` = :bookprice
		, `safety` = :safety
		, `detail` = :detail
		, `created` = :created
		';

	mydb::query($stmt, $data);

	$result->query[] = mydb()->_query;

	$result->plantId = SG\getFirst($data->plantid, mydb()->insert_id);
	$result->data = $data;

	return $result;
}
?>