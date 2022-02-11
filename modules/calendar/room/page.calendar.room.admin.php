<?php
/**
* Calendar Room Admin
* Created 2019-08-03
* Modify  2019-08-03
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function calendar_room_admin($self) {
	R::View('calendar.toolbar',$self, 'Calendar Room Admin');

	if (!user_access('administer calendar rooms')) return message('error','Access denied');

	$post=(object)post('config');

	if ($post->title) property('calendar.room:title',$post->title);
	if ($post->detail) property('calendar.room:detail',$post->detail);
	if ($post->roomvid) property('calendar.room:roomvid',$post->roomvid);

	$ret.='<h3><a href="'.url('calendar/room/admin').'">Calendar room reservation Administrator</a></h3>';

	$property=property('calendar.room');

	$form = new Form([
		'variable' => 'config',
		'action' => url(q()),
		'class' => 'edit-config',
	]);

	$form->addField(
			'title',
			array(
				'type' => 'text',
				'label' => 'ชื่อระบบงาน',
				'class' => '-fill',
				'value' => htmlspecialchars($property['title']),
			)
		);

	$form->addField(
			'detail',
			array(
				'type' => 'textarea',
				'label' => 'คำอธิบาย',
				'class' => '-fill',
				'value' => htmlspecialchars($property['detail']),
			)
		);

	$vocabs = model::get_vocabulary();
	$roomList = array(-1 => '===เลือก===');
	foreach ($vocabs->items as $vocab) $roomList[$vocab->vid] = $vocab->name;

	$form->addField(
			'roomvid',
			array(
				'type' => 'select',
				'label' => 'รายชื่อห้องประชุม : ',
				'class' => '-fill',
				'value' => $property['roomvid'],
				'options' => $roomList,
				'posttext' => '<a class="btn -link" href="'.url('admin/content/taxonomy/list/'.$property['roomvid']).'"><i class="icon -material">add</i><span>เพิ่ม/ลบรายชื่อห้องประชุม</span></a>',
			)
		);

	//		$ret.='<select name="room-vid" onchange="window.location=\''.url('calendar/room/admin').'?room_vid=\'+this.value">';
	//		'"'.($vocab->vid==cfg('calendar.room.vid')?' selected="selected"':'').'>'..'</option>';
	//		$ret.='</select> <a href="'.url('admin/content/taxonomy/list/'.cfg('calendar.room.vid')).'">เพิ่ม/ลบรายชื่อ</a>';

	$form->addField(
			'save',
			array(
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i>{tr:SAVE}',
				'container' => '{class: "-sg-text-right"}',
			)
		);

	$ret .= $form->build();

	//		$ret.=print_o($post,'$post');
	//		$ret.=print_o($property,'$property');
	//		$ret.=print_o($vocabs,'$vocabs');

	return $ret;
}
?>