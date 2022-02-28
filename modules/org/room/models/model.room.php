<?php
/**
* Org Room :: Room Model
* Created 2021-10-02
* Modify  2021-10-02
*
* @param Array $args
* @return Object
*
* @usage new RoomModel([])
* @usage RoomModel::function($conditions, $options)
*/

$debug = true;
date_default_timezone_set("Asia/Bangkok");
class RoomModel {
	function __construct($args = []) {
	}

	public static function get($id, $options = '{}') {
		$defaults = '{debug: false}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		$result = NULL;

		return $result;
	}

	public static function items($conditions, $options = '{}') {
		$defaults = '{debug: false}';
		$options = SG\json_decode($options, $defaults);
		$debug = $options->debug;

		$result = NULL;

		if (is_string($conditions) && preg_match('/^{/',$conditions)) {
			$conditions = SG\json_decode($conditions);
		} else if (is_object($conditions)) {
			//
		} else if (is_array($conditions)) {
			$conditions = (Object) $conditions;
		} else {
			$id = $conditions;
			unset($conditions);
			$conditions->id = $id;
		}

        $dbs = mydb::select('SELECT * FROM %calendar_room%');

        $result->count = count($dbs->items);
        $result->items = $dbs->items;
        // RoomModel->items[0] = Object f1 = 1
		return $result;
	}
	public static function selectEditRoom($roomId)
	{
		$dbs = mydb::select(
			'SELECT * FROM %calendar_room% where `resvid` = :id',
			[
				':id' => $roomId,
			]
		);
		return $dbs->items[0];
	}
	public static function selectResvRoom($month,$year)
	{
		$dbs = mydb::select(
			'SELECT * FROM %calendar_room% where YEAR(`checkin`) = :year AND MONTH(`checkin`) = :month',
			[
				':year' => $year,
				':month' => $month,
			]
		);
		return $dbs->items;
	}


	public static function selectAnyEdit($roomId,$phone)
	{
		$dbs = mydb::select(
			'SELECT * FROM %calendar_room% where `resvid` = :id AND `phone` = :phone',
			[
				':id' => $roomId,
				':phone' => $phone
			]
		);
		return $dbs->items[0];
	}

	
	public static function checkAnyEdit($roomId,$phone)
	{
		$dbs = mydb::select(
			'SELECT * FROM %calendar_room% where `resvid` = :id AND `phone` = :phone',
			[
				':id' => $roomId,
				':phone' => $phone
			]
		);
		return $dbs->count();
	}
	public static function editAnyuser($resvid,$data)
	{
		echo $data['roomid'];
		$dbs = mydb::query(
			'UPDATE %calendar_room%  set title = :title , resv_by = :resv_by, org_type = :org_type, roomid = :roomid , uid_last_edit = :usid, checkin = :checkin , checkout = :checkout, from_time = :from_time, to_time = :to_time, peoples = :peoples, food = :food, descript = :descript,  edit_date_time = :edit_date_time WHERE `resvid` = :resvid LIMIT 1',
			[':resvid' => $resvid,
			 ':title' => $data['title'],
			 'resv_by' => $data['resvName'],
			 'org_type' => $data['org_type'],
			 'roomid' => $data['roomid'],
			 'usid' => i()->uid,
			 'checkin' => date("Y-m-d", strtotime(str_replace('/','-',$data['checkin']))) ,
			 'checkout' => date("Y-m-d", strtotime(str_replace('/','-',$data['checkout']))),
			 'from_time' => $data['from_time'],
			 'to_time' => $data['to_time'],
			 'peoples' => $data['peoples'],
			 'food' => $data['food'],
			 'descript' => $data['description'],
			 'edit_date_time' => date("Y-m-d H:i:s")
			]
		);
		return mydb()->_query;
	}
	public static function editAdminuser($resvid,$data)
	{
		echo $data['roomid'].' '.$data["paid_date"].' '.$data['descript_admin'];

		$dbs = mydb::query(
			'UPDATE %calendar_room%  set title = :title , resv_by = :resv_by, org_type = :org_type, roomid = :roomid , uid_last_edit = :usid, checkin = :checkin , checkout = :checkout, from_time = :from_time, to_time = :to_time, peoples = :peoples, food = :food, descript = :descript, descript_admin = :desc_admin, paid_date = :paid_date, paid_date_record = :paidDate_record, edit_date_time = :edit_date_time  WHERE `resvid` = :resvid LIMIT 1',
			[':resvid' => $resvid,
			 ':title' => $data['title'],
			 'resv_by' => $data['resvName'],
			 'org_type' => $data['org_type'],
			 'roomid' => $data['roomid'],
			 'usid' => i()->uid,
			 'checkin' => date("Y-m-d", strtotime(str_replace('/','-',$data['checkin']))) ,
			 'checkout' => date("Y-m-d", strtotime(str_replace('/','-',$data['checkout']))),
			 'from_time' => $data['from_time'],
			 'to_time' => $data['to_time'],
			 'peoples' => $data['peoples'],
			 'food' => $data['food'],
			 ':descript' => $data['description'],
			 ':desc_admin' => $data['descript_admin'],
			 'paid_date' => date("Y-m-d", strtotime(str_replace('/','-',$data['paid_date']))),
			 'paidDate_record' => date("Y-m-d", strtotime(str_replace('/','-',$data['paid_date_record']))),
			 'edit_date_time' => date("Y-m-d H:i:s")
			]
		);
		return mydb()->_query;
	}
	public static function getOrderNum()
	{
		$dbs = mydb::select(
			'SELECT * FROM %calendar_room% order by created desc limit 1'
		);
		$order_num = $dbs->order_num;
		 $lqyear = sg_date($dbs->created,'Y');
		 $lqmonth = sg_date($dbs->created,'m');
		 $lqdate =  sg_date($dbs->created,'d');

		 if(date('Y') == $lqyear && $lqmonth < 11 || (date('Y')-1) == $lqyear && $lqmonth > 10 )
		 {
			$order_num += 1;
		 }
		 else {
			 $order_num = 1;
		 }
		return $order_num;
	}
	public static function editApprove($resvid,$approve)
	{
		$dbs = mydb::query(
			'UPDATE %calendar_room%  set approve = :approve  WHERE `resvid` = :resvid LIMIT 1',
			[':resvid' => $resvid,
			 ':approve' => $approve]
			);
		//return mydb()->_query;
		return mydb()->status;
	
	}
}
?>