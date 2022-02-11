<?php
/**
* Calendar Room Approve
* Created 2019-08-03
* Modify  2019-08-03
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function calendar_room_approve($self,$id=NULL,$action=NULL,$confirm=NULL) {
	$para=para(func_get_args(),2);
	$rs = R::Model('calendar.get.resv',$id);

	R::View('calendar.toolbar',$self, 'Approve');

	if ( $rs->_empty ) {
		$ret .= message('error','Room item not found');
	} else if (!user_access('administer calendar rooms','edit own calendar room content',$rs->uid)) {
		$ret .= message('error','Access denied','calendar');
	} else if ( $_POST['confirm']=='no' ) {
		location('calendar/room/'.$rs->resvid);
	} else if ( $_POST['confirm']=='yes' ) {
		mydb::query('DELETE FROM %calendar_room% WHERE `resvid`=:id LIMIT 1',':id',$id);
		 mydb::clear_autoid('%calendar_room%');
		$ret .= '<font color="red">Room reservation item was deleted.</font><br/>';
		location('calendar/room');
	} else {
		$form = new Form([
			'action' => url(q()),
			'children' => [
				'confirm' => [
					'type' => 'radio',
					'name' => 'confirm',
					'label' => 'คุณต้องการดำเนินการเปลี่ยนสถานะการจองห้องประชุมรายการ <strong>"'.$rs->title.'"</strong>  ใช่หรือไม่?',
					'options' => ['no' => 'ไม่ ฉันไม่ต้องการ', 'yes' => 'ใช่ ฉันต้องการ'],
				],
				'submit' => [
					'type' => 'submit',
					'items' => (Object)[
						'cancel' => 'ยกเลิกการจอง',
						'notapprove' => 'ไม่ผ่านอนุมัติ',
						'approve' => 'ผ่านอนุมัติ',
						'delete' => 'ลบรายการทิ้ง',
					],
					'posttext' => ' หรือ <a href="'.url('calendar/room/info/'.$id).'">กลับสู่รายละเอียด</a>',
				],
				'หมายเหตุ : หากเลือกจะทำการลบรายการทิ้ง ข้อมูลการจองรายการนี้ถูกลบทิ้งและจะไม่สามารถเรียกคืนได้อีกแล้ว',
			], // children
		]);

		$ret .= $form->build();
	}
	return $ret;
}
?>