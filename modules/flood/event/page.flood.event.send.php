<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function flood_event_send($self) {
	if (!i()->ok) {
		$ret .= R::View('signform');
	} else if ($hasStation=mydb::select('SELECT COUNT(*) total FROM %flood_user% WHERE `uid`=:uid LIMIT 1',':uid',i()->uid)->total) {
		//$ret.='hasStation='.$hasStation.mydb()->_query;
		//$ret.='<h3>แจ้งข่าว</h3>';

		$ret.='<form id="flood-event-post" class="sg-form flood__event__post flood__event__post--send" method="post" action="'.url('flood/event/post').'" data-rel="#main" data-done="'.url('flood/event/send').'">';
		$ret.='<input type="hidden" name="source" value="staff" />';
		$ret.='<input type="hidden" name="priority" value="5" />';
		$ret.='<div class="form-item form-item--stations">';

		// Set station
		$stmt='SELECT * FROM %flood_user% fu LEFT JOIN %flood_station% s USING(`station`) WHERE fu.`uid`=:uid';
		$dbs=mydb::select($stmt,':uid',i()->uid);
		foreach ($dbs->items as $rs) $userStations[]=$rs->station;

		if ($dbs->_num_rows==0) {
			$ret.='<p class="notify">ขออภัย ท่านยังไม่สามารถรายแจ้งข่าวสถานการณ์ของสถานีวัดน้ำได้ กรุณาติดต่อผู้ดูแลระบบเพื่อกำหนดสถานีวัดน้ำ</p>';
			return $ret;
		} else if ($dbs->_num_rows==1) {
			$rs=$dbs->items[0];

			$ret.='<input type="hidden" name="station" value="'.$rs->station.'" />';
			$ret.='<h3>'.$rs->title.'</h3>';
		} else if ($dbs->_num_rows<=5) {
			$ret.='<div class="form-item form-item--station"><label>เลือกสถานี :</label>';
			foreach ($dbs->items as $item) {
				$ret.='<div><label class="label--option" style="display: block;"><input type="radio" name="station" value="'.$item->station.'"> '.$item->station.' : '.$item->title.'</label></div>';
			}
			$ret.='</div>';
		} else {
			$ret.='<div class="form-item form-item--station"><select id="flood-event-station" class="form-select" name="station"><option value="">==เลือกสถานี==</option>';
			foreach ($dbs->items as $item) {
				$ret.='<option value="'.$item->station.'">'.$item->station.' : '.$item->title.'</option>';
			}
			$ret.='</select></div>';
		}
		$ret.='</div>';

		$ret.='<h4>สถานการณ์ :</h4>';
		$ret.='<div class="form-item form-item--flag">';
		$ret.='<div class="flag--red"><label><input type="radio" name="flag" value="Red" /> ธงแดง</label></div>';
		$ret.='<div class="flag--yellow"><label><input type="radio" name="flag" value="Yellow" /> ธงเหลือง</label></div>';
		$ret.='<div class="flag--green"><label><input type="radio" name="flag" value="Green" /> ธงเขียว</label></div>';
		$ret.='</div>';
		$ret.='<div class="form-item form-item--photo"><span class="btn btn-success fileinput-button"><i class="icon -camera"></i><span>ภาพถ่าย</span><input type="file" name="photoimg" id="flood-event-photoimg" accept="image/*;capture=camera" capture="camera" /></span>';
		$ret.='</div>';

		$ret.='<div id="form-event-msg" class="form-item form-item--msg"><label>รายละเอียดสถานการณ์</label><textarea id="flood-event-msg" name="msg" class="form-textarea -fill" rows="3" cols="20" placeholder="รายละเอียดสถานการณ์ฝนหรือน้ำท่วม"></textarea></div></div>';

		$ret.='<div class="form-item -sg-text-right" style="padding: 32px;"><button id="flood-event-submit-x" class="btn -primary"><i class="icon -material">done_all</i><span>'.tr('POST').'</span></button></div></div>';
		$ret.='</form>'._NL;
		$ret.='<div id="flood-event-show">'._NL
				. R::Page('flood.event.drawmsg',implode(',',$userStations))
				. '</div>'._NL;
	} else {
		$ret.='<p class="notify">ขออภัย ท่านยังไม่ได้อยู่ใน "ทีมเตือนภัย"<br /> กรุณาติดต่อผู้ดูแลระบบเพื่อดำเนินการเพิ่มเข้าเป็นสมาชิกของทีมเตือนภัย</p>';

	}
	return $ret;
}
?>