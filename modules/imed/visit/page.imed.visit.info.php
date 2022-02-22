<?php
/**
* iMed :: Visit Information
* Created 2019-03-05
* Modify  2021-05-28
*
* @param Object $patientInfo
* @return String
*
* @usage imed/visit/{psnId}/{action}/{seqId}
*/

$debug = true;

import('model:imed.visit');

class ImedVisitInfo {
	var $psnId;
	var $patientInfo;

	function __construct($patientInfo) {
		$this->psnId = $patientInfo->psnId;
		$this->patientInfo = $patientInfo;
	}

	function build() {

		$uid = i()->uid;

		if (!$this->psnId) return message('error','ไม่มีข้อมูล');

		$isAccess = $this->patientInfo->RIGHT & _IS_ACCESS;
		$isEdit = $this->patientInfo->RIGHT & _IS_EDITABLE;


		$visitInfo = ImedVisitModel::items(['psnId' => $this->psnId],['items' => 100]);

		$headerUi = new Ui();
		$headerUi->add('<a class="sg-action" href="'.url('imed/patient/need/'.$this->psnId).'" data-rel="#imed-app"><i class="icon -material">how_to_reg</i><span></i><span class="-hidden">ความต้องการ</span></a>');
		$ret .= '<header class="header"><h3>ประวัติการเยี่ยมบ้าน</h3><nav class="nav">'.$headerUi->build().'</nav></header>'._NL;


		$ui = new Ui('div', 'ui-card imed-my-note sg-inline-edit');
		$ui->addData('update-url', url('imed/edit/patient'));
		$ui->addId('imed-my-note');
		if (debug('inline')) $ui->addData('debug', 'inline');

		foreach ($visitInfo->items as $rs) {
			if ($isAccess || $rs->uid == $uid) {
				$ui->add(R::View('imed.visit.render',$rs), '{class: "", id: "noteUnit-'.$rs->seq.'"}');
			}
		}
		$ret .= $ui->build().'<!-- imed-my-note -->';

		return $ret;
	}
}
?>