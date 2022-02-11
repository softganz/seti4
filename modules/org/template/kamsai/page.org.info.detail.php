<?php
/**
* Org :: Detail Information
* Created 2021-12-06
* Modify  2021-12-06
*
* @param Object $orgInfo
* @return Widget
*
* @usage org/{id}/info.detail
*/

class OrgInfoDetail extends Page {
	var $orgId;
	var $orgInfo;

	function __construct($orgInfo) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
		$this->right = (Object) [
			'edit' => $this->orgInfo->RIGHT & _IS_EDITABLE,
		];
	}

	function build() {
		$schoolInfo = R::Model('school.get',$this->orgId);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Title',
			]),
			'body' => new Card([
				'class' => '-area',
				'children' => [
					// new ListTile([
					// 	'class' => '-sg-paddingnorm',
					// 	'title' => 'รายละเอียดองค์กร',
					// 	'leading' => '<i class="icon -material">info</i>',
					// 	'trailing' => $this->right->admin ? '<a class="btn -link" href="'.url('org/'.$this->orgId.'/info.view').'"><i class="icon -material">find_in_page</i></a>' : NULL,
					// ]),
					new Table([
						'children' => [
							['ชื่อโรงเรียน', $this->orgInfo->name],
							['สังกัด', SG\getFirst($this->orgInfo->info->groupType, '??')],
							['ที่อยู่โรงเรียน', $this->orgInfo->info->address.' '.$this->orgInfo->info->zip],
							['จำนวนนักเรียน', number_format($schoolInfo->info->studentamt).' คน'],
							['ช่วงชั้น', SG\getFirst($schoolInfo->info->classlevel, '??')],
							['ผู้อำนวยการ', SG\getFirst($this->orgInfo->info->managername, '??')],
							['ครูผู้รับผิดชอบ', SG\getFirst($this->orgInfo->info->contactname, '??')],
							// ['โทรศัพท์', $this->orgInfo->info->phone],
							// $this->orgInfo->info->fax ? ['โทรสาร', $this->orgInfo->info->fax] : NULL,
							// ['อีเมล์', $this->orgInfo->info->email],
						], // children
					]), // Table
					$this->right->edit ? new ListTile([
						'class' => '-sg-paddingnorm',
						'title' => '',
						'trailing' => '<a class="sg-action btn" href="'.url('project/knet/'.$this->orgId.'/school.edit').'" data-rel="box" data-width="640"><i class="icon -material">edit</i><span>แก้ไข</span></a>',
					]) : NULL,
				],
			]),
		]);
	}
}
?>