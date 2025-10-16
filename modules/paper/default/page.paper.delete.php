<?php
/**
* Paper   :: Delete Paper
* Created :: 2019-06-01
* Modify  :: 2023-04-07
* Version :: 2
*
* @param Object $topicInfo
* @return Widget
*
* @usage paper/{id}/delete
*/

import('model:paper.php');

class PaperDelete extends Page {
	var $nodeId;
	var $topicInfo;

	function __construct($topicInfo = NULL) {
		parent::__construct([
			'nodeId' => $topicInfo->tpid,
			'topicInfo' => $topicInfo
		]);
		// debugMsg($this, '$this');
	}

	function build() {
		if (!$this->topicInfo->tpid) return error(_HTTP_ERROR_BAD_REQUEST, 'ไม่มีข้อมูลตามที่ระบุ');
		else if (!user_access('administer contents,administer papers,administer '.$this->topicInfo->info->module.' paper','edit own paper',$this->topicInfo->uid)) return error(_HTTP_ERROR_FORBIDDEN, 'Access denied');
		else if ($this->topicInfo->info->status == _LOCK) return error(_HTTP_ERROR_NOT_ACCEPTABLE, 'This topic was lock:You cannot delete a lock topic. Please unlock topic and go to delete again.');

		$firstTag = is_array($this->topicInfo->tags) ? reset($this->topicInfo->tags) : NULL;
		if ($firstTag) {
			$doneUrl = url('tags/'.$firstTag->tid);
		} else {
			$doneUrl = url('paper');
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ลบหัวข้อ',
				'boxHeader' => true,
			]), // AppBar
			'body' => new Form([
				'action' => url('api/paper/'.$this->nodeId.'/delete'),
				'class' => 'sg-form -sg-paddingnorm',
				'rel' => 'notify',
				'done' => 'close | reload:'.$doneUrl,
				'checkValid' => true,
				'children' => [
					'confirm' => [
						'type' => 'checkbox',
						'require' => true,
						'label' => 'กรุณายืนยันว่าต้องการลบหัวข้อ <strong>"'.$this->topicInfo->title.'"</strong>  ใช่หรือไม่?',
						'options' => ['yes' => 'ใช่ ยืนยันว่าต้องการลบทิ้ง'],
					],
					'proceed' => [
						'type' => 'button',
						'class' => '-danger',
						'value' => '<i class="icon -material">delete</i><span>ดำเนินการลบหัวข้อ</span>',
						'container' => '{class: "-sg-text-right"}',
					],
					'<div style="color:red; font-size: 1.2em; font-weight: bold;">คำเตือน : จะทำการลบข้อมูลหัวข้อนี้พร้อมทั้งภาพและเอกสารประกอบทั้งหมด และจะไม่สามารถเรียกคืนได้อีกแล้ว!!!</div>',
				], // children
			]), // Form
		]);
	}
}
?>