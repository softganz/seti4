<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function flood_event_init($self) {
	$ret.='<h2>รายงานสถานการณ์น้ำ</h2>';
	if (i()->ok) {
		//if (user_access('create flood content')) $ret.='<div id="flood-event-nav" class="toolbar"><ul><li><a href="javascript:void(0)"><i class="icon2 -rain"></i>สถานการณ์น้ำ</a></li><li><a href="javascript:void(0)" onclick="$(\'#form-event-level\').toggle();"><i class="icon2 -gauge"></i>ระดับน้ำ</a></li></ul></div>';
		$ret.='<form id="flood-event-post" class="flood__event__post flood__event__post--post" method="post" action="'.url('flood/event/post').'">';

		if (user_access('create flood content')) {
			$ret.='<div id="form-event-level" class="form-item -hidden"><h4>รายงานระดับน้ำ</h4>';
			$ret.='<select id="flood-event-station" class="form-select" name="station"><option value="">==เลือกสถานี==</option>';
			foreach (mydb::select('SELECT * FROM %flood_station%')->items as $item) {
				$ret.='<option value="'.$item->station.'">'.$item->station.' : '.$item->title.'</option>';
			}
			$ret.='</select><label>ระดับน้ำ</label>';
			$ret.='<select name="waterlevel" class="form-select">';
			for ($i=0; $i <= 40 ; $i=$i+0.10) {
				$ret.='<option value="'.$i.'">'.number_format($i,2).' ม.</option>';
			}
			$ret.='</select>';
			$ret.='<label>วัน-เวลา</label><input type="text" name="recorddate" class="sg-datepicker" size="10" value="'.date('d/m/Y').'" data-minDate="-30" data-maxDate="0" />';

			for ($i=0;$i<24;$i++) {
				$hr=sprintf('%02d',$i);
				for ($m=0;$m<4;$m++) {
					$min=sprintf('%02d',$m*15);
					$r=round(date('i')/15);
					if ($r>3) $r=0;
					$option.='<option value="'.$hr.':'.($min).'"'.($hr==date('H') && $m==$r?' selected="selected"':'').'>'.$hr.'.'.$min.' น.</option>';
				}
			}
			$ret.=' <select name="recordtime" class="form-select">'.$option.'</select>';

			$ret.='</div>';
		}

		$ret.='<div id="form-event-msg" class="form-item"><h4>สถานการณ์ฝน-น้ำท่วม</h4><span class="btn btn-success fileinput-button"><i class="icon -camera"></i><span>ส่งภาพ</span><input type="file" name="photoimg" id="flood-event-photoimg"  accept="image/*;capture=camera" capture="camera"  /></span><textarea id="flood-event-msg" name="msg" class="form-textarea -fill" rows="3" cols="20" placeholder="รายละเอียดสถานการณ์ฝนหรือน้ำท่วม"></textarea></div><div id="flood-event-bar"><div class="form-item"><label>ที่ไหน?</label><input type="text" class="sg-autocomplete form-text -fill" name="where" id="flood-event-where" placeholder="สถานที่เกิดเหตุการณ์" data-query="'.url('flood/event/getwhere').'" /></div><div class="form-item"><label>เมื่อไหร่?</label><input type="text" class="form-text -fill" name="when" id="flood-event-when" value="'.date('Y-m-d H:i').'" /></div>';

		$ret.='<div class="form-item"><button id="flood-event-submit" class="btn -primary">'.tr('Post').'</button></div></div>';
		$ret.='</form>'._NL;
	} else {
		$ret.='<div class="flood__event--signform"><p>กรุณาเข้าสู่ระบบสมาชิกเพื่อแจ้งรายงานสถานการณ์น้ำ หากท่านยังไม่ได้เป็นสมาชิกเว็บไซท์ <a class="sg-action" href="'.url('user/register',array('rel'=>'flood-event')).'" data-rel="#flood-event">สมัครสมาชิก</a> <!-- <a href="'.url('user/register').'">กรุณาสมัครสมาชิก (ฟรี)</a> --> ก่อนค่ะ</p>';

		$ret .= R::View('signform');

		$ret.='<p><a class="sg-action" href="'.url('user/register',array('rel'=>'flood-event')).'" data-rel="#flood-event">สมัครสมาชิก</a></p>';
		$ret.='</div>';
	}
	$ret.='<div class="flood-event-remark"><p>เว็บไซท์ขอความช่วยเหลือจากสมาชิกผู้เข้าชมเว็บ ช่วยกันรายงานสถานการณ์น้ำที่เกิดขึ้นตามความเป็นจริง เพื่อเก็บรวบรวมข้อมูลสำหรับการวิเคราะห์และประเมินสถานการณ์ในสภาวะวิกฤติ ขอขอบคุณทุกท่านมา ณ ที่นี้ จากทีมงาน ACCCRN หาดใหญ่</p><p><strong>คำเตือน : ข้อมูลจากการรายงานสถานการณ์น้ำด้านล่างนี้เป็นการรายงานข้อมูลจากการมีส่วนร่วมของคนทั่วไป ไม่ควรนำมาข้อมูลมาอ้างอิงในการทำงาน จนกว่าท่านจะได้ตรวจสอบความถูกต้องของข้อมูลก่อน !!!</strong></p></div>';
	$ret.='<p align="right"><a href="javascript:void(0)" id="flood-event-refresh" data-url="'.url('flood/event/drawmsg').'">รีเฟรช</a></p>';

	$ret.='<div id="flood-event-show">'._NL
			.R::Page('flood.event.drawmsg',NULL)
			.'</div>'._NL;
	return $ret;
}
?>