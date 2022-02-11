<?php
/**
* Model Name
* Created 2019-12-01
* Modify  2019-12-01
*
* @param Object $conditions
* @param Object $options
* @return Object Data Set
*/

$debug = true;

function r_ibuy_product_create($data, $options = '{}') {
	$defaults = '{debug: false}';
	$options = SG\json_decode($options, $defaults);
	$debug = $options->debug;

	$tpid = false;
	$result = NULL;
	$result->tpid = NULL;
	$result->data = $data;
	$result->_query = NULL;


	if ($data->title) {
		$data->post->title = $data->title;
		$data->post->poster = i()->name;
		$data->post->type = 'ibuy';
		$data->post->orgid = $data->shopid;
		//$data->status = _PUBLISH;
		//debugMsg($data, '$data');

		$result = R::Model('node.create', $data, $options);

		$data->listprice = SG\getFirst($data->listprice, 0);
		$data->retailprice = SG\getFirst($data->retailprice, 0);
		$data->cost = SG\getFirst($data->cost, 0);
		$data->price1 = SG\getFirst($data->price1, 0);
		$data->price2 = SG\getFirst($data->price2, 0);
		$data->price3 = SG\getFirst($data->price3, 0);
		$data->price4 = SG\getFirst($data->price4, 0);
		$data->price5 = SG\getFirst($data->price5, 0);
		$data->resalerprice = SG\getFirst($data->resalerprice, 0);
		$data->available = 1;
		$data->outofsale = 'N';
		$data->isnew = 0;

		$stmt = 'INSERT INTO %ibuy_product%
			(`tpid`, `available`, `listprice`, `retailprice`, `price1`, `price2`, `price3`, `price4`, `price5`, `resalerprice`, `cost`, `outofsale`, `isnew`)
			VALUES
			(:tpid, :available, :listprice, :retailprice, :price1, :price2, :price3, :price4, :price5, :resalerprice, :cost, :outofsale, :isnew)';
		mydb::query($stmt, $data);

		$result->process[] = mydb()->_query;


		if ($data->category) {
			$data->vid = cfg('ibuy.vocab.category');

			$stmt = 'INSERT INTO %tag_topic% (`tpid`, `vid`, `tid`) VALUES (:tpid, :vid, :category)';
			mydb::query($stmt, $data);
			$result->process[] = mydb()->_query;
		}
	}

	return $result;
}

?>