<?php
/**
* Model Name
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_icar_create($data, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	$data->post->type = 'icar';
	$data->post->title = implode(' ',array(mydb::select('SELECT name FROM %tag% where `tid` = :tid LIMIT 1', ':tid', $data->brand)->name, $data->model));
	$data->post->poster = i()->name;

	$result = R::Model('node.create', $data, $options);

	//debugMsg($data,'$data',1);
	//debugMsg($result,'$result');

	// Return on node create error
	if (!$result->tpid) return $result;




	// Save new icar
	$data->buydate = sg_date($data->buydate,'Y-m-d');

	if ($result->complete && $result->tpid) {
		$myShop = icar_model::get_my_shop();
		$data->shopid = SG\getFirst($data->shopid);
		$data->refno = icar_model::get_next_refno($data->shopid);
		$data->uid = i()->uid;

		if ($data->brandname) {
			if ($data->brand = mydb::select('SELECT `tid` FROM %tag% WHERE taggroup="icar:brand" AND `name` LIKE :name LIMIT 1',':name',$data->brandname)->tid) {
				// Use old brandname
			} else {
				// Create new brandname
				$stmt = 'INSERT INTO %tag% SET `taggroup` = :taggroup, `shopid` = :shopid, `ownid` = :uid, `name` = :brandname';
				mydb::query($stmt,':taggroup', 'icar:brand', $data);
				$data->brand = mydb()->insert_id;
				$result->process[] = mydb()->_query;
			}
		}

		if ($data->partnername) {
			if ($data->partner = mydb::select('SELECT `partner` FROM %icarpartner% WHERE `shopid`=:shopid AND `name` LIKE :name LIMIT 1',':shopid',$data->shopid, ':name', $data->partnername)->partner) {
				// Use old partner name
			} else {
				mydb::query('INSERT INTO %icarpartner% SET `shopid` = :shopid, `name` = :partnername, `share` = 1 ',$data);
				$data->partner = mydb()->insert_id;
				$result->process[] = mydb()->_query;
			}
		}

		// Add car information into database
		$stmt = 'INSERT INTO %icar%
						(`tpid`, `shopid`, `refno`, `partner`, `buydate`, `cartype`, `plate`, `brand`, `model`)
						VALUES
						(:tpid, :shopid, :refno, :partner, :buydate, :cartype, :plate, :brand, :model)';
		mydb::query($stmt, $data);
		$result->process[] = mydb()->_query;

		if ($data->brandname) {
			icar_model::update_car_title($data->tpid);
			$result->process[] = mydb()->_query;
		}
	}

	$result->data = $data;

	return $result;
}
?>