<?php
/**
* Calendar room home page
* Created 2019-08-03
* Modify  2019-08-03
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function calendar_room_home($self) {
//die('I am die here @'.date('H:i:s'));
	R::View('calendar.toolbar',$self);

	//$ret.=$this->property['detail'];
	$sort = $_REQUEST['show']=='all'?'DESC':'ASC';
	$stmt = 'SELECT
			r.*
		, u.name AS resv_name
		, tg.name AS room_name
		, r.approve+0 approve_status
		FROM %calendar_room% r
			LEFT JOIN %users% u USING (uid)
			LEFT JOIN %tag% tg ON r.roomid=tg.tid
		WHERE '.($_REQUEST['show']=='all'?'1':'r.checkin >= "'.date('Y-m-d 00:00:00').'"').'
		ORDER BY r.checkin '.$sort.', r.from_time '.$sort;

	$dbs = mydb::select($stmt);
	//$ret .= print_o($dbs);

	$ret .= R::Page('calendar.room.list', NULL, $dbs, $para);

	$ret.='<a href="'.url('calendar/room?show=all').'">ดูรายละเอียดทั้งหมด</a>';
	return $ret;
}
?>