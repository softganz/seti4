<?php
/**
* Paper   :: Send Delete Request
* Created :: 20xx-xx-xx
* Modify  :: 2025-06-25
* Version :: 3
*
* @param String $topicInfo
* @return Widget
*
* @usage paper/{Id}/send.delete
*/

class PaperSendDelete extends Page {
	var $nodeId;
	var $topicInfo;

	function __construct($topicInfo = NULL) {
		parent::__construct([
			'nodeId' => $topicInfo->nodeId,
			'topicInfo' => $topicInfo
		]);
	}

	function rightToBuild() {
		if (empty($this->nodeId)) return error(_HTTP_ERROR_NOT_FOUND, 'ไม่มีข้อมูลตามที่ระบุ');
		return true;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'แจ้งลบหัวข้อที่ไม่เหมาะสม',
				'leading' => _HEADER_BACK,
				'boxHeader' => true,
			]), // AppBar
			'body' => new Form([
				'variable' => 'contact',
				'action' => url('api/paper/'.$this->nodeId.'/send.delete'),
				'id' => 'edit-senddelete',
				'class' => 'sg-form',
				'checkValid' => true,
				'rel' => 'replace',
				'children' => [
					'detail' => [
						'type' => 'textarea',
						'label' => 'ความไม่เหมาะสมของเนื้อหา',
						'class' => '-fill',
						'rows' => 5,
						'require' => true,
						'value' => $post->detail,
						'placeholder' => 'กรุณาระบุความไม่เหมาะสมของเนื้อหา',
					],
					'sender' => [
						'type' => 'text',
						'label' => tr('Sender').(i()->ok ? ' (you are member)':''),
						'class' => '-fill',
						'require' => true,
						'value' => SG\getFirst($post->sender,i()->name),
						'placeholder' => 'ชื่อผู้แจ้ง',
					],
					'email' => [
						'type' => 'text',
						'label' => tr('E-mail'),
						'class' => '-fill',
						'value' => $post->email,
						'placeholder' => 'name@example.com',
					],
					'daykey' => !i()->ok ? [
						'name' => 'daykey',
						'type' => 'text',
						'label' => tr('Anti-spam word'),
						'size' => 10,
						'require' => true,
						'value' => $_POST['daykey'],
						'posttext' => ' &laquo; <em class="spamword">'.Poison::getDayKey(5,true).'</em>',
						'description' => 'ป้อนตัวอักษรของ Anti-spam word ในช่องข้างบนให้ถูกต้อง',
					] : NULL,
					'save' => [
						'type' => 'button',
						'value' => '<i class="icon -material">done_all</i><span>{tr:SEND}</span>',
						'container' => '{class: "-sg-text-right"}',
					],
				], // children
			]), // Form
		]);
	}
}
?>