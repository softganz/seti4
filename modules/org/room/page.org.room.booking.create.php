<?php
/**
* Org Room :: Create Booking
* Created 2021-10-31
* Modify  2021-10-31
*
* @param Array $_REQUEST
* @return String
*
* @usage org/room/booking
*/

$debug = true;
import('package:org/room/models/model.room.php');
class OrgRoomBookingCreate extends Page {
	function build() {
		$order_num = RoomModel::getOrderNum();
		echo print_o(post());
		mydb::query(
			'INSERT INTO %calendar_room%
			(`order_num`,`org_name`, `phone`, `uid`, `created`,`title`,`resv_by`,`org_type`,`roomid`,`checkin`,`checkout`,`from_time`,`to_time`,`peoples`,`food`,`descript`)
			VALUES
			( :order_num, :org_name, :phone, :uid, :created, :title, :resv_by, :org_type ,:roomid ,:checkin, :checkout, :from_time, :to_time, :peoples ,:food, :descript)
			',
			[
				':order_num' => $order_num,
				':org_name' => post('org_name'),
				':phone' => post('phone'),
				':uid' => i()->uid,
				'created' => date('U'),
				'title' => post('title'),
				'resv_by' => post('resvName'),
				'org_type' => post('org_type'),
				'roomid' => post('roomid'),
				'checkin' => date("Y-m-d", strtotime(str_replace('/','-',post('checkin'))))  ,
				'checkout' => date("Y-m-d", strtotime(str_replace('/','-',post('checkout')))),
				'from_time' => post('from_time'),
				'to_time' => post('to_time'),
				'peoples' => post('peoples'),
				'food' => post('food'),
				'descript' => post('description'),
			]
		);
		return mydb()->_query;
	}
}
?>