<?php
/**
* iMed :: Patient Visit VitalSign
* Created 2019-03-05
* Modify  2021-05-28
*
* @param Object $patientInfo
* @return String
*
* @usage imed/visit/{psnId}/vitalsign
*/

$debug = true;

class ImedVisitVitalsign {
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
		$ret .= '<header class="header"><h3>สัญญาญชีพ</h3><nav class="nav">'.$headerUi->build().'</nav></header>'._NL;

		mydb::where('s.`pid` = :pid', ':pid', $psnId);
		mydb::where('s.`service` IN ("Treatment","Home Visit","Web Distance Treatment","Vitalsign")');

		if (!$isAccess) {
			mydb::where('s.`uid` = :uid', ':uid', i()->uid);
		}

		$stmt='SELECT
			  s.*
			, u.`username`
			, u.`name`
			, CONCAT(p.`name`," ",p.`lname`) patient_name
			FROM %imed_service% s
				LEFT JOIN %users% u USING (uid)
				LEFT JOIN %db_person% p ON p.`psnid` = s.`pid`
			%WHERE%
			GROUP BY `seq`
			ORDER BY s.`seq` DESC';

		$dbs=mydb::select($stmt);

		$tables = new Table();
		$tables->addClass('imed-vitalsign-item -center');
		$tables->thead=array('SEQ','date'=>'วันที่','น้ำหนัก<br />(ก.ก.)','ส่วนสูง<br />(ซ.ม.)','อุณหภูมิ<br />(C)','ชีพจร<br />(ครั้ง/นาที)','อัตราการหายใจ<br />(ครั้ง/นาที)','ความดันโลหิต<br />(มม.ปรอท)','');
		foreach ($dbs->items as $rs) {
			$hasVitalsign=$rs->weight || $rs->height || $rs->temperature || $rs->pulse || $rs->respiratoryrate || $rs->bloodpressure || $rs->dbp || $rs->sbp;
			$tables->rows[] = array(
					$rs->seq,
					sg_date($rs->timedata,'m/d/ปป'),
					$rs->weight ? $rs->weight : '',
					$rs->height ? $rs->height : '',
					$rs->temperature > 0 ? $rs->temperature : '',
					$rs->pulse ? $rs->pulse : '',
					$rs->respiratoryrate ? $rs->respiratoryrate : '',
					$rs->bloodpressure?$rs->bloodpressure:$rs->sbp.($rs->sbp?'/':'').$rs->dbp,
					//'<a href="'.url('imed/app/vitalsign/'.$psnId.'/edit/'.$rs->seq).'"><i class="icon '.($hasVitalsign?'-edit':'-add').'"></i></a>',
				);
		}
		$ret.=$tables->build();


		//$ret.=print_o($psn,'$psn');

		$ret.='<style type="text/css">
		.imed-vitalsign-item .col-date span {color:#999; font-size:0.9em;}
		</style>';
		return $ret;
	}
}
?>