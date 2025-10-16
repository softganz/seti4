<?php
/**
* Calendar :: Room Reservation Information
* Created  :: 2019-08-03
* Modify   :: 2022-12-05
* Version  :: 2
*
* @param Object $ResvInfo
* @return String
*
* @usage calendar/room/{resvId}
*/

class CalendarRoomView extends Page {
	var $resvId;
	var $right;
	var $resvInfo;

	function __construct($resvInfo = NULL) {
		parent::__construct([
			'resvId' => $resvInfo->resvId,
			'resvInfo' => $resvInfo,
			'right' => (Object) [
				'admin' => user_access('administer calendar rooms'),
			],
		]);
		// debugMsg($this, '$this');
	}

	function build() {
		if (!$this->resvInfo->resvId) {
			return new ErrorMessage([
				'responseCode' => _HTTP_ERROR_NOT_FOUND,
				'text' => 'Data not found'
			]);
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Calendar Room',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Container([
						'id' => 'calendar-room-info',
						'children' => [
							new ListTile(['title' => 'รายละเอียดการจองห้องประชุม', 'leading' => new Icon('info')]),
							new Table([
								'id' => 'calendar-room-info',
								'children' => [
									['ห้องประชุม','<strong>'.$this->resvInfo->room_name.'<strong>'],
									['วันที่ใช้ - เวลา','<strong>'.sg_date($this->resvInfo->checkin,'j ดด ปปปป').' เวลา '.substr($this->resvInfo->from_time,0,5).' - '.substr($this->resvInfo->to_time,0,5).' น.</strong>'],
									['จำนวนคน',$this->resvInfo->peoples.' คน'],
									['อุปกรณ์ที่ใช้',$this->resvInfo->equipment],
									['จองโดย',$this->resvInfo->resv_by],
									['หน่วยงาน',$this->resvInfo->org_name],
									user_access('administer calendar rooms','edit own calendar room content',$this->resvInfo->uid) ? ['โทรศัพท์ติดต่อ',$this->resvInfo->phone] : NULL,
									['ทำอะไร',$this->resvInfo->title],
									['ผู้รับจอง',$this->resvInfo->resv_name],
									['วันที่จอง',$this->resvInfo->created?sg_date($this->resvInfo->created,'j ดด ปปปป'):''],
									['สถานะ',$this->resvInfo->approve],
									$this->resvInfo->body ? ['รายละเอียด',sg_text2html($this->resvInfo->body)] : NULL,
								], // children
							]), // Table
						],
					]), // Container

					new Column([
						'id' => 'calendar-room-status',
						'children' => [
							new ListTile(['title' => 'สถานะ : '.$this->resvInfo->approve]),
							user_access('administer calendar rooms','edit own calendar room content',$this->resvInfo->uid) ?
								// $approveItems = (Object) ['ยกเลิก' => 'ยกเลิกการจอง'];
								// 	if (user_access('administer calendar rooms')) {
								// 		$approveItems->{"ไม่อนุมัติ"} = 'ไม่ผ่านอนุมัติ';
								// 		$approveItems->{"อนุมัติ"}='ผ่านอนุมัติ';
								// 	}
								// 	$approveItems->delete='ลบรายการทิ้ง';
									new Column([
										'children' => [
											'<a class="sg-action btn -fill" href="'.url('api/calendar/room/info/'.$this->resvId.'/cancel').'" data-rel="notify" data-done="load"><i class="icon -material">cancel</i><span>ยกเลิกการจอง</span></a>',
											$this->right->admin ? '<a class="sg-action btn -danger -fill" href="'.url('api/calendar/room/info/'.$this->resvId.'/notpass').'" data-rel="notify" data-done="load"><i class="icon -material">block</i><span>ไม่อนุมัติ</span></a>' : NULL,
											$this->right->admin ? '<a class="sg-action btn -primary -fill" href="'.url('api/calendar/room/info/'.$this->resvId.'/pass').'" data-rel="notify" data-done="load"><i class="icon -material">verified</i><span>ผ่านอนุมัติ</span></a>' : NULL,
											$this->right->admin ? '<a class="sg-action btn -fill" href="'.url('api/calendar/room/info/'.$this->resvId.'/delete').'" data-rel="notify" data-done="reload:'.url('calendar/room').'" data-title="อนุมัติ" data-confirm="คุณต้องการดำเนินการเปลี่ยนสถานะการจองห้องประชุมรายการนี้  ใช่หรือไม่?"><i class="icon -material">delete</i><span>ลบรายการทิ้ง</span></a>' : NULL,
											'หมายเหตุ : หากเลือกจะทำการลบรายการทิ้ง ข้อมูลการจองรายการนี้ถูกลบทิ้งและจะไม่สามารถเรียกคืนได้อีกแล้ว',
										],
									]) : NULL,
									// new Form([
									// 	'action' => url('api/calendar/room/info/'.$this->resvId),
									// 	'id' => 'calendar-room-approve',
									// 	'children' => [
									// 		'submit' => [
									// 			'label' => $approve ? 'ดำเนินการเปลี่ยนสถานะเป็น '.$approve.' เรียบร้อยแล้ว' : 'คุณต้องการดำเนินการเปลี่ยนสถานะการจองห้องประชุมรายการนี้  ใช่หรือไม่?',
									// 			'type' => 'submit',
									// 			'items' => $approveItems,
									// 		],
									// 		'หมายเหตุ : หากเลือกจะทำการลบรายการทิ้ง ข้อมูลการจองรายการนี้ถูกลบทิ้งและจะไม่สามารถเรียกคืนได้อีกแล้ว',
									// 	], // children
									// ]) : NULL,

						], // children
					]), // Container
				], // children
			]), // Widget
		]);
	}
}
?>