<?php
/**
* iMed :: Patient Barthel Index List
* Created 2019-03-05
* Modify  2021-05-28
*
* @param Object $patientInfo
* @return String
*/

$debug = true;

class ImedVisitBarthel {
	var $patientInfo;

	function __construct($patientInfo) {
		$this->patientInfo = $patientInfo;
	}

	function build() {
		$psnId = $this->patientInfo->psnId;

		$isAccess = $this->patientInfo->RIGHT & _IS_ACCESS;

		$ret = '';

		$headerUi = new Ui();
		$headerUi->add('<a class="sg-action" href="'.url('imed/visit/'.$psnId.'/need').'" data-rel="#imed-app"><i class="icon -material -circle -green">how_to_reg</i><span></i><span class="-hidden">ความต้องการ</span></a>');
		$headerUi->add('<a class="sg-action" href="'.url('imed/visit/'.$psnId.'/vitalsign').'" data-rel="#imed-app"><i class="icon -local -vitalsign -white '.($hasVitalsign?'-has':'-not').'"></i><span></i><span class="-hidden">สัญญาณชีพ</span></a>');
		$headerUi->add('<a class="sg-action" href="'.url('imed/visit/'.$psnId.'/barthel').'" data-rel="#imed-app"><i class="icon -local -barthel-no"></i><span></i><span class="-hidden">ดัชนีบาร์เธล</span></a>');
		$ret .= '<header class="header"><h3>ดัชนีบาร์เธล (Barthel ADL index)</h3><nav class="nav">'.$headerUi->build().'</nav></header>'._NL;

		mydb::where('s.`pid` = :pid', ':pid', $psnId);
		mydb::where('s.`service` IN ("Treatment","Home Visit","Web Distance Treatment","Vitalsign")');

		if (!$isAccess) {
			mydb::where('s.`uid` = :uid', ':uid', i()->uid);
		}

		$stmt='SELECT
			  b.*
			, s.*
			, u.`username`, u.`name`
			, CONCAT(p.`name`," ",p.`lname`) patient_name
			FROM %imed_service% s
				LEFT JOIN %users% u USING (uid)
				LEFT JOIN %db_person% p ON p.`psnid` = s.`pid`
				LEFT JOIN %imed_barthel% b USING(`seq`)
			%WHERE%
			ORDER BY s.`seq` DESC';

		$dbs=mydb::select($stmt);

		$tables = new Table();
		$tables->addClass('imed-vitalsign-item -center');
		$tables->thead = array('SEQ','date' => 'วันที่','ADL','');
		foreach ($dbs->items as $rs) {
			$barthel = R::Model('imed.barthel.level', $rs->score);

			$tables->rows[] = array(
					$rs->seq,
					sg_date($rs->timedata,'d/m/ปป'),
					$rs->score,
					'<i class="icon -local -barthel-'.$barthel->level.'"></i>',
				);
		}
		$ret.=$tables->build();

		return $ret;
	}
}
?>