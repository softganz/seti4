<?php
/**
* Calendar Room List
* Created 2019-08-03
* Modify  2019-08-03
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function calendar_room_list($self, $dbs) {
	$tables = new Table();
	$tables->addClass('calendar-room-list');
	$tables->thead = array('date'=>'วัน-เดือน-ปี','เวลา','ห้องประชุม','ประชุม','ผู้จอง/หน่วยงาน','ผู้รับจอง','สถานะ','');
	$no = 0;
	$now = date('Y-m-d');

	foreach ($dbs->items as $rs ) {
		$config['class'] = 'approve-'.$rs->approve_status;
		if ($now > $rs->checkin) $config['class'] .= ' completed';

		$tables->rows[] = array(
				sg_date($rs->checkin,'j ดด ปปปป'),
				substr($rs->from_time,0,5).'-'.substr($rs->to_time,0,5),
				$rs->room_name,
				$rs->title,
				$rs->resv_by.'/'.$rs->org_name,
				$rs->resv_name,
				$rs->approve,
				'<a class="" href="'.url('calendar/room/'.$rs->resvid.'/view').'"><i class="icon -material">pageview</i></a>',
				'config'=>$config
			);
		//$ret .= print_o($rs,'$rs');
	}

	$ret .= $tables->build();
	//$ret .= print_o($dbs,'$dbs');
	return $ret;
}
?>