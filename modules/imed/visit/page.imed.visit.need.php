<?php
/**
* iMed :: Patient Need List
* Created 2019-03-05
* Modify  2021-05-28
*
* @param Object $patientInfo
* @return String
*
* @usage imed/visit/{psnId}/need
*/

$debug = true;

class ImedVisitNeed {
	var $patientInfo;

	function __construct($patientInfo) {
		$this->patientInfo = $patientInfo;
	}

	function build() {
		$psnId = $this->patientInfo->psnId;
		$uid = i()->uid;


		if (!$psnId) return message('error','ไม่มีข้อมูล');

		$isAccess = $this->patientInfo->RIGHT & _IS_ACCESS;
		$isEdit = $this->patientInfo->RIGHT & _IS_EDITABLE;



		$ret = '';
		$headerUi = new Ui();
		$headerUi->add('<a class="sg-action" href="'.url('imed/visit/'.$psnId.'/need').'" data-rel="#imed-app"><i class="icon -material -circle -green">how_to_reg</i><span></i><span class="-hidden">ความต้องการ</span></a>');
		$headerUi->add('<a class="sg-action" href="'.url('imed/visit/'.$psnId.'/vitalsign').'" data-rel="#imed-app"><i class="icon -local -vitalsign -white '.($hasVitalsign?'-has':'-not').'"></i><span></i><span class="-hidden">สัญญาณชีพ</span></a>');
		$headerUi->add('<a class="sg-action" href="'.url('imed/visit/'.$psnId.'/barthel').'" data-rel="#imed-app"><i class="icon -local -barthel-no"></i><span></i><span class="-hidden">ดัชนีบาร์เธล</span></a>');
		$ret .= '<header class="header"><h3>ความต้องการ</h3><nav class="nav">'.$headerUi->build().'</nav></header>'._NL;


		$stmt = 'SELECT
				n.*
			, u.`username`, u.`name`, CONCAT(p.`name`," ",p.`lname`) `patient_name`
			, nt.`name` `needTypeName`
			FROM %imed_need% n
				LEFT JOIN %users% u USING(`uid`)
				LEFT JOIN %db_person% p USING(`psnid`)
				LEFT JOIN %imed_stkcode% nt ON nt.`stkid` = n.`needtype`
			WHERE `psnid` = :psnid
			ORDER BY `needid` DESC';
		$dbs = mydb::select($stmt, ':psnid', $psnId);


		$ui = new Ui('div','ui-card imed-my-note -need');
		$ui->addId('imed-my-note');
		if ($dbs->_empty) {
			$ret .=message('notify','ไม่มีข้อมูลความต้องการ');
		} else {
			foreach ($dbs->items as $rs) {
				$ui->add(R::View('imed.need.render',$rs), '{class: "-urgency-'.$rs->urgency.'", id: "noteUnit-'.$rs->seq.'"}');
			}
		}
		$ret .= $ui->build();

		return $ret;
	}
}
?>