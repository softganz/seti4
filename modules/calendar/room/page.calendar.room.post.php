<?php
/**
* Create New Room Reservation
* Created 2019-08-03
* Modify  2019-08-03
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function calendar_room_post($self, $data = NULL) {
	$para=para(func_get_args(),1);

	list($year,$month,$date)=explode('/',$para->_src);
	if ($year && $month && $date) $rs['from_date']=$date.'-'.$month.'-'.$year;

	R::View('calendar.toolbar',$self, 'จองใช้ห้องประชุม');

	if ($error) $ret.=message('error',$error);

	if (is_object($data)) {
	} else if (is_array($data)) {
		$data = (Object) $data;
	} else {
		$data = (Object) [];
	}
	$data->from_time=substr($data->from_time,0,5);
	$data->to_time=substr($data->to_time,0,5);

	$form = new Form([
		'variable' => 'room',
		'action' => url('calendar/room/save'),
		'id' => 'edit-meeting',
		'children' => [
			'tbutton' => [
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}',
				'pretext' => '<a class="btn -link -cancel" href=""><i class="icon -material -gray">cancel</i><spna>{tr:CANCEL}</span></a>',
				'container' => '{class: "-sg-text-right"}',
			],
			'roomid' => [
				'type' => 'select',
				'label' => 'ห้องประชุม:',
				'require' => true,
				'options' => (function() {
					$roomProperty = property('calendar.room');
					$tree = model::get_taxonomy_tree($roomProperty['roomvid']);
					$options = [];
					foreach ($tree as $term) {
						$options[$term->tid]=$term->name;
					}
					return $options;
				})(),
				'value' => $data->roomid,
				'description' => $room_type->help,
			],
			'peoples' => [
				'type' => 'text',
				'label' => 'จำนวนคน',
				'maxlength' => 3,
				'size' => 3,
				'value' => $data->peoples,
				'placeholder' => '-',
			],
			'date' => [
				'type' => 'textfield',
				'label' => 'เมื่อไหร่',
				// 'require' => true,
				'value' => (function($data) {
					for ($hr=8;$hr<24;$hr++) {
						for ($min=0;$min<60;$min+=30) {
							$times[]=sprintf('%02d',$hr).':'.sprintf('%02d',$min);
						}
					}
					$value = '<input type="text" name="room[checkin]" id="edit-calendar-from_date" maxlength="10" class="form-text form-date require" style="width:80px;" value="'.htmlspecialchars(SG\getFirst($data->checkin,date('d/m/Y'))).'"> เวลา <select class="form-select" name="room[from_time]" id="edit-calendar-from_time">';
					foreach ($times as $time) $value.='<option value="'.$time.'"'.($time==$data->from_time?' selected="selected"':'').'>'.$time.'</option>';
					$value.='</select>
					ถึง <select class="form-select" name="room[to_time]" id="edit-calendar-to_time">';
					foreach ($times as $time) $value.='<option value="'.$time.'"'.($time==$data->to_time?' selected="selected"':'').'>'.$time.'</option>';
					$value.='</select>';
					return $value;
				})($data),
			],
			'equipment' => [
				'type' => 'checkbox',
				'multiple' => true,
				'label' => 'อุปกรณ์ที่ใช้:',
				'display' => 'inline',
				'options' => (function() {
					$equipment=mydb::get_set_member('%calendar_room%','equipment');
					$options = [];
					foreach ($equipment as $i) $options[$i]=$i;
					return $options;
				})(),
				'value' => $data->equipment,
			],
			'resv_by' => [
				'type' => 'text',
				'label' => 'จองโดยใคร',
				'maxlength' => 50,
				'class' => '-fill',
				'require' => true,
				'value' => htmlspecialchars($data->resv_by),
				'placeholder' => 'ระบุชื่อผู้ขอใช้',
			],
			'org_name' => cfg('calendar.room.vid.org') ?
			[
				'type' => 'select',
				'label' => 'หน่วยงานอะไร',
				'require' => true,
				'value' => $data->org_name,
				'options' => (function() {
					$tree = model::get_taxonomy_tree(cfg('calendar.room.vid.org'));
					$options = ['' => '==เลือกหน่วยงาน=='];
					foreach ($tree as $term) {
						$options[$term->name] = $term->name;
					}
					return $options;
				})(),
				'posttext' => ' หรือ <input maxlength="50" size="30" name="room[org_name_etc]" id="edit-room-org_name_etc" class="form-text" type="text" value="'.htmlspecialchars(SG\getFirst($data->org_name_etc,in_array($data->org_name,$form->org_name->options) ? NULL : $data->org_name)).'" placeholder="ระบุชื่อชื่อหน่วยงาน" />',
			]
			:
			[
				'type' => 'text',
				'label' => 'หน่วยงานอะไร',
				'class' => '-fill',
				'maxlength' => 100,
				'require' => true,
				'value' => htmlspecialchars($data->org_name),
				'placeholder' => 'ระบุชื่อหน่วยงาน',
			],
			'phone' => [
				'type' => 'text',
				'label' => 'โทรศัพท์ติดต่อ',
				'class' => '-fill',
				'maxlength' => 50,
				'value' => htmlspecialchars($data->phone),
				'placeholder' => 'ระบุหมายเลขโทรศัพท์ผู้ขอใช้',
			],
			'title' => [
				'type' => 'text',
				'label' => 'ทำอะไร',
				'class' => '-fill',
				'maxlength' => 255,
				'require' => true,
				'value' => htmlspecialchars($data->title),
				'placeholder' => 'ระบุชื่องานหรือลักษณะงานคร่าว ๆ',
			],
			'body' => [
				'type' => 'textarea',
				'label' => 'รายละเอียด',
				'class' => '-fill',
				'rows' => 5,
				'value' => $data->body,
			],
			'save' => [
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}',
				'pretext' => '<a class="btn -link -cancel" href=""><i class="icon -material -gray">cancel</i><spna>{tr:CANCEL}</span></a>',
				'container' => '{class: "-sg-text-right"}',
			],
		], // children
	]);

	$ret .= $form->build();
	$ret.='<script type="text/javascript">
	$(document).ready(function() {
		$("#edit-calendar-from_date").datepicker({
			dateFormat: "dd/mm/yy",
			disabled: false,
			monthNames: thaiMonthName
		});
	});
	</script>';

	//$ret.=$this->_post_form($post);
	//		$ret.=print_o($_POST,'$_POST');
	return $ret;
}
?>