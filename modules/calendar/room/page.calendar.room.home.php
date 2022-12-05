<?php
/**
* Calendar :: Calendar Room Home
* Created  :: 2019-08-03
* Modify   :: 2022-12-05
* Version  :: 1
*
* @return Widget
*
* @usage module/{id}/method
*/

class CalendarRoomHome extends Page {
	var $sort;

	function __construct() {
		parent::__construct([
			'sort' => post('show' == 'all') ? 'DESC' : 'ASC',
		]);
	}
	function build() {
		$stmt = 'SELECT
				r.*
			, u.name AS resv_name
			, tg.name AS room_name
			, r.approve+0 approve_status
			FROM %calendar_room% r
				LEFT JOIN %users% u USING (uid)
				LEFT JOIN %tag% tg ON r.roomid=tg.tid
			WHERE '.($_REQUEST['show']=='all'?'1':'r.checkin >= "'.date('Y-m-d 00:00:00').'"').'
			ORDER BY r.checkin '.$this->sort.', r.from_time '.$this->sort;

		$dbs = mydb::select($stmt);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Calendar Room',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Table([
						'class' => 'calendar-room-list',
						'thead' => ['date'=>'วัน-เดือน-ปี','เวลา','ห้องประชุม','ประชุม','ผู้จอง/หน่วยงาน','ผู้รับจอง','สถานะ',''],
						'children' => array_map(
							function($rs) {
								static $no = 0;
								$now = date('Y-m-d');
								$config = [
									'class' => 'approve-'.$rs->approve_status.($now > $rs->checkin ? ' completed' : '')
								];
								return [
									sg_date($rs->checkin,'j ดด ปปปป'),
									substr($rs->from_time,0,5).'-'.substr($rs->to_time,0,5),
									$rs->room_name,
									$rs->title,
									$rs->resv_by.'/'.$rs->org_name,
									$rs->resv_name,
									$rs->approve,
									'<a class="" href="'.url('calendar/room/'.$rs->resvid).'"><i class="icon -material">pageview</i></a>',
									'config'=>$config
								];
							},
							$dbs->items
						), // children
					]),
				], // children
			]), // Widget
		]);
	}
}
?>