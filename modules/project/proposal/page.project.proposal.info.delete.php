<?php
/**
* Project :: Delete Proposal
* Created 2019-10-15
* Modify  2021-11-25
*
* @param String $arg1
* @return Widget
*
* @usage project/proposal/{id}/info.delete
*/

$debug = true;

class ProjectProposalInfoDelete extends Page {
	var $projectId;
	var $proposalInfo;

	function __construct($proposalInfo) {
		$this->projectId = $proposalInfo->projectId;
		$this->proposalInfo = $proposalInfo;
		$this->right = (Object) [
			'access' => $this->proposalInfo->RIGHT & _IS_RIGHT,
			'admin' => $this->proposalInfo->RIGHT & _IS_ADMIN,
		];
	}

	function build() {
		if (!$this->projectId) {
			return  message(['responseCode' => _HTTP_OK_NO_CONTENT, 'text' => 'ไม่มีข้อมูลข้อเสนอโครงการที่ระบุ']);
		} else if (!$this->right->access) return message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'Access Denied']);

		$isDeletable = R::Model('project.right.develop.delete',$this->proposalInfo);

		if (!$isDeletable) return message('error','Access Denied');
		else if ($this->proposalInfo->followId) return message('error','โครงการเข้าสู่ระบบติดตามแล้ว ไม่สามารถลบทิ้งได้!!!!');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ลบข้อเสนอโครงการ!!!!',
				'boxHeader' => true,
			]),
			'body' => new Widget([
				'children' => [
					new Form([
						'action' => url('project/proposal/api/'.$this->projectId.'/delete'),
						'class' => 'sg-form',
						'rel' => 'notify',
						'done' => 'close | reload:'.($this->proposalInfo->orgId ? url('org/'.$this->proposalInfo->orgId) : url('project/proposal')),
						'children' => [
							'confirm' => [
								'type'=>'checkbox',
								'name'=>'confirm',
								'label'=>'คุณต้องการลบข้อเสนอโครงการ <strong>"'.$this->proposalInfo->info->title.'"</strong>  ใช่หรือไม่?',
								'options'=>array('yes'=>'ใช่ ฉันต้องการลบทิ้ง')
							],
							'submit' => [
								'type'=>'button',
								'class' => '-danger',
								'value'=>'<i class="icon -delete -white"></i>ดำเนินการลบข้อเสนอโครงการ</span>',
								'pretext' => '<a class="sg-action btn -link -cancel" data-rel="close"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
								'container' => '{class: "-sg-text-right"}'
							],
						], // children
					]), // Form
					'<em>คำเตือน : จะทำการลบข้อมูลข้อเสนอโครงการนี้ พร้อมทั้งภาพและเอกสารประกอบทั้งหมด ข้อมูลทั้งหมดจะไม่สามารถเรียกคืนได้อีกต่อไป!!!</em>',
					// new DebugMsg($this, '$this'),
				],
			]),
		]);
	}
}
?>