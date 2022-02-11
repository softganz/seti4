<?php
/**
* iMed :: Add Patient into Group
* Created 2019-02-15
* Modify 	2021-08-22
*
* @param Object $patientInfo
* @return Widget
*
* @usage imed/patient/{id}/group.add
*/

$debug = true;

class ImedPatientGroupAdd extends Page {
	var $psnId;
	var $patientInfo;

	function __construct($patientInfo) {
		$this->psnId = $patientInfo->psnId;
		$this->patientInfo = $patientInfo;
	}

	function build() {
		if (!$this->psnId) return message('error','ไม่มีข้อมูล');

		$isAccess = $this->patientInfo->RIGHT & _IS_ACCESS;

		if (!$isAccess) return message('error',$this->patientInfo->error);

		$stmt = 'SELECT m.`orgid`, m.`uid`, o.`name` `groupname`
			FROM %imed_socialmember% m
				LEFT JOIN %db_org% o USING(`orgid`)
			WHERE m.`uid` = :uid';
		$groupDbs = mydb::select($stmt, ':uid',i()->uid);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'เพิ่มเข้ากลุ่ม',
			]),
			'body' => new Widget([
				'children' => [
					'<header class="header -box -hidden">'._HEADER_BACK.'<h3 class="title -box">เพิ่มเข้ากลุ่ม</h3></header>',
					$groupDbs->_empty ?
					new Container([
						'children' => [message(['type' => 'error', 'text' => 'ขออภัย!!! ท่านไม่ได้เป็นสมาชิกของกลุ่มใด ๆ ในระบบ '])],
					])
					:
					new Container([
						'children' => (function($groupDbs) {
							$result = [];
							foreach ($groupDbs->items as $rs) {
								$result[] = new Card([
									'child' => new ListTile([
										'class' => 'sg-action',
										'href' => url('imed/social/'.$rs->orgid.'/patient.add', ['psnid' => $this->psnId]),
										'rel' => 'parent:.widget-container',
										'crossAxisAlignment' => 'center',
										'title' => $rs->groupname,
										'leading' => '<i class="icon -material -sg-32">group</i>',
										'trailing' => '<i class="icon -material -sg-32">add_circle</i>'
									]), // ListTile
								]);
							}
							return $result;
						})($groupDbs), // children
					]), // Ui
				], // children
			]), // Widget
		]);
	}
}
?>