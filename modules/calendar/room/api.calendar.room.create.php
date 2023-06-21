<?php
/**
* Module  :: Description
* Created :: 2022-12-05
* Modify  :: 2022-12-05
* Version :: 1
*
* @param Int $mainId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage module/api/{id}/{action}[/{tranId}]
*/

class CalendarRoomCreateApi extends PageApi {
	var $mainId;
	var $action;
	var $tranId;

	function __construct() {
		parent::__construct([
			'action' => $action,
			'tranId' => $tranId,
			// 'mainInfo' => $mainId ? GetInfoModel::get($mainId) : NULL,
		]);
		$this->mainId = $this->mainInfo->mainId;
	}

	function build() {
		// debugMsg('mainId '.$this->mainId.' Action = '.$this->action.' TranId = '.$this->tranId);


		$post = (Object) post('room',_TRIM+_STRIPTAG);


		if (!$post->roomid) return ['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'ไม่ระบุห้องประชุม'];

		$post->resvId = \SG\getFirst($post->resvId);
		if (empty($post->checkin)) $field_missing[]='วันที่-เดือน-ปีที่จอง';
		if (empty($post->from_time) || empty($post->to_time)) $field_missing[]='ตั้งแต่เวลา-ถึงเวลา';
		if (empty($post->resv_by)) $field_missing[]='จองโดยใคร';
		if (empty($post->org_name) && empty($post->org_name_etc)) $field_missing[]='หน่วยงานอะไร';
		if (empty($post->title)) $field_missing[]='ทำอะไร';
		if ($field_missing) $error[]='กรุณาป้อนข้อมูลต่อไปนี้ให้ครบถ้วน : <ol><li>'.implode('</li><li>',$field_missing).'</li></ol>';

		// if ($error) return ['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => ]

		// start save new item
		$simulate=debug('simulate');

		//preg_match($this->date_format,$post->checkin,$from_date);

		$post->checkin = sg_date($post->checkin, 'Y-m-d');
		$post->calid = SG\getFirst($post->calid,'func.NULL');
		$post->uid = SG\getFirst(i()->uid,'func.NULL');
		$post->org_name = SG\getFirst($post->org_name,$post->org_name_etc);
		$post->equipment = \SG\getFirst(is_array($post->equipment) ? implode(',',$post->equipment) : NULL,'func.NULL');
		$post->created=date('U');

		$stmt='INSERT INTO %calendar_room%
				(`resvId`, `calId`, `roomid`, `uid`, `title`, `body`, `resv_by`, `org_name`, `checkin`, `from_time`, `to_time`, `peoples`, `equipment`, `phone`, `created`)
			VALUES
				(:resvId, :calid, :roomid, :uid, :title, :body, :resv_by, :org_name, :checkin, :from_time, :to_time, :peoples, :equipment, :phone, :created)
			ON DUPLICATE KEY UPDATE
			  `roomid` = :roomid
			, `title`=:title
			, `body`=:body
			, `resv_by`=:resv_by
			, `org_name`=:org_name
			, `checkin`=:checkin
			, `from_time`=:from_time
			, `to_time`=:to_time
			, `peoples`=:peoples
			, `equipment`=:equipment
			, `phone`=:phone';

		mydb::query($stmt,$post);

		if (mydb()->_error) {
			return ['responseCode' => _HTTP_ERROR_NOT_ACCEPTABLE, 'text' => 'มีข้อผิดพลาดในการบันทึกข้อมูล'];
		}

		// debugMsg(mydb(),'mydb()');

		if (empty($post->resvId)) $post->resvId = mydb()->insert_id;
		// debugMsg(mydb()->_query);
		// debugMsg($post, '$post');
		// debugMsg(post(), 'post()');

		return ['resvId' => $post->resvId];
	}
}
?>