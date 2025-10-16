<?php
/**
* Calendar :: Room Admin
* Created  :: 2019-08-03
* Modify   :: 2022-12-05
* Version  :: 2
*
* @return Widget
*
* @usage calendar/room/admin
*/

class CalendarRoomAdmin extends Page {
	var $right;

	function __construct() {
		parent::__construct([
			'right' => (Object) [
				'admin' => is_admin(),
			],
		]);
	}

	function build() {
		if (!$this->right->admin) {
			return new ErrorMessage([
				'responseCode' => _HTTP_ERROR_FORBIDDEN,
				'text' => 'Access Denied'
			]);
		}

		$post = (Object) post('config');

		if ($post->title) property('calendar.room:title',$post->title);
		if ($post->detail) property('calendar.room:detail',$post->detail);
		if ($post->roomvid) property('calendar.room:roomvid',$post->roomvid);

		$property=property('calendar.room');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Calendar Room Admin',
			]), // AppBar
			'body' => new Form([
				'variable' => 'config',
				'action' => url(q()),
				'class' => 'edit-config',
				'children' => [
					'title' => [
						'type' => 'text',
						'label' => 'ชื่อระบบงาน',
						'class' => '-fill',
						'value' => htmlspecialchars($property['title']),
					],
					'detail' => [
						'type' => 'textarea',
						'label' => 'คำอธิบาย',
						'class' => '-fill',
						'value' => htmlspecialchars($property['detail']),
					],
					'roomvid' => [
						'type' => 'select',
						'label' => 'รายชื่อห้องประชุม : ',
						'class' => '-fill',
						'value' => $property['roomvid'],
						'options' => (function($vocabs) {
							$roomList = array(-1 => '===เลือก===');
							foreach ($vocabs->items as $vocab) $roomList[$vocab->vid] = $vocab->name;
							return $roomList;
						})(BasicModel::get_vocabulary()),
						'posttext' => '<a class="btn -link" href="'.url('admin/content/taxonomy/list/'.$property['roomvid']).'"><i class="icon -material">add</i><span>เพิ่ม/ลบรายชื่อห้องประชุม</span></a>',
					],
					'save' => [
						'type' => 'button',
						'value' => '<i class="icon -material">done_all</i>{tr:SAVE}',
						'container' => '{class: "-sg-text-right"}',
					],
				], // children
			]), // Form
		]);
	}
}
?>