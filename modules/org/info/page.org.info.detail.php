<?php
/**
* Module :: Description
* Created 2021-12-06
* Modify  2021-12-06
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

class OrgInfoDetail extends Page {
	var $orgId;
	var $orgInfo;

	function __construct($orgInfo) {
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
	}

	function build() {
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
							['ชื่อองค์กร', $this->orgInfo->name],
							['ที่อยู่', $this->orgInfo->info->address.' '.$this->orgInfo->info->zip],
							['โทรศัพท์', $this->orgInfo->info->phone],
							$this->orgInfo->info->fax ? ['โทรสาร', $this->orgInfo->info->fax] : NULL,
							['อีเมล์', $this->orgInfo->info->email],
						], // children
					]), // Table
				],
			]),
		]);
	}
}
?>