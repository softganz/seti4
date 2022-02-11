<?php
/**
* Module Method
* Created 2019-08-03
* Modify  2019-08-03
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function calendar_room_view($self, $resvInfo) {
	if (!$resvInfo->resvid) return $ret.message('error','Data not found');

	R::View('calendar.toolbar',$self, $resvInfo->title);

	if ($_POST && user_access('administer calendar rooms','edit own calendar room content',$resvInfo->uid)) {
		if ($_POST['ยกเลิก']) $approve='ยกเลิก';
		else if ($_POST['ไม่อนุมัติ'] && user_access('administer calendar rooms')) $approve='ไม่อนุมัติ';
		else if ($_POST['อนุมัติ'] && user_access('administer calendar rooms')) $approve='อนุมัติ';
		else if ($_POST['delete']) {
			mydb::query('DELETE FROM %calendar_room% WHERE `resvid`=:resvid LIMIT 1',':resvid',$resvInfo->resvid);
			location('calendar/room');
		}
		if ($approve) {
			mydb::query('UPDATE %calendar_room% SET `approve`=:approve WHERE `resvid`=:resvid LIMIT 1',':resvid',$resvInfo->resvid,':approve',$approve);
			$resvInfo->approve=$approve;
		}
	}

	$tables = new Table();
	$tables->addId('calendar-room-info');
	$tables->caption='รายละเอียดการจองห้องประชุม';
	$tables->rows[]=array('ห้องประชุม','<strong>'.$resvInfo->room_name.'<strong>');
	$tables->rows[]=array('วันที่ใช้ - เวลา','<strong>'.sg_date($resvInfo->checkin,'j ดด ปปปป').' เวลา '.substr($resvInfo->from_time,0,5).' - '.substr($resvInfo->to_time,0,5).' น.</strong>');
	$tables->rows[]=array('จำนวนคน',$resvInfo->peoples.' คน');
	$tables->rows[]=array('อุปกรณ์ที่ใช้',$resvInfo->equipment);
	$tables->rows[]=array('จองโดย',$resvInfo->resv_by);
	$tables->rows[]=array('หน่วยงาน',$resvInfo->org_name);
	if (user_access('administer calendar rooms','edit own calendar room content',$resvInfo->uid)) $tables->rows[]=array('โทรศัพท์ติดต่อ',$resvInfo->phone);
	$tables->rows[]=array('ทำอะไร',$resvInfo->title);
	$tables->rows[]=array('ผู้รับจอง',$resvInfo->resv_name);
	$tables->rows[]=array('วันที่จอง',$resvInfo->created?sg_date($resvInfo->created,'j ดด ปปปป'):'');
	$tables->rows[]=array('สถานะ',$resvInfo->approve);
	if ($resvInfo->body) $tables->rows[]=array('รายละเอียด',sg_text2html($resvInfo->body));
	$ret .= $tables->build();

	$ret.='<div id="calendar-room-status"><span class="button">'.$resvInfo->approve.'</span>';
	if (user_access('administer calendar rooms','edit own calendar room content',$resvInfo->uid)) {

		$approveItems = (Object) ['ยกเลิก' => 'ยกเลิกการจอง'];
		if (user_access('administer calendar rooms')) {
			$approveItems->{"ไม่อนุมัติ"} = 'ไม่ผ่านอนุมัติ';
			$approveItems->{"อนุมัติ"}='ผ่านอนุมัติ';
		}
		$approveItems->delete='ลบรายการทิ้ง';

		$form = new Form([
			'action' => url(q()),
			'id' => 'calendar-room-approve',
			'children' => [
				'submit' => [
					'label' => $approve ? 'ดำเนินการเปลี่ยนสถานะเป็น '.$approve.' เรียบร้อยแล้ว' : 'คุณต้องการดำเนินการเปลี่ยนสถานะการจองห้องประชุมรายการนี้  ใช่หรือไม่?',
					'type' => 'submit',
					'items' => $approveItems,
				],
				'หมายเหตุ : หากเลือกจะทำการลบรายการทิ้ง ข้อมูลการจองรายการนี้ถูกลบทิ้งและจะไม่สามารถเรียกคืนได้อีกแล้ว',
			], // children
		]);

		$ret .= $form->build();

		$ret.='<script type="text/javascript">
	$(document).ready(function() {
		$("input[name=delete]").click(function() {
			return confirm("คุณกำลังจะลบการจองห้องประชุมรายการนี้ คุณแน่ใจหรือไม่ว่าต้องการจะลบ?");
		});
	});
	</script>';
	}
	$ret.='</div>';
	return $ret;
}
?>