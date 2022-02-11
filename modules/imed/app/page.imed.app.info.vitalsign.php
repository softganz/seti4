<?php
/**
* Module :: Description
* Created 2021-01-01
* Modify  2021-01-01
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage module/{id}/method
*/

$debug = true;

class ImedAppInfoVitalSign {
	var $patientInfo;

	function __construct($patientInfo) {
		$this->patientInfo = $patientInfo;
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'สัญญาญชีพ '.$this->patientInfo->realname,
				'removeOnApp' => true,
			]), // AppBar
			'children' => [
				$this->_list(),
			], // children
		]);
	}

	function _list() {
		$psnId = $this->patientInfo->psnId;

		$isAccess = $this->patientInfo->RIGHT & _IS_ACCESS;

		mydb::where('s.`pid` = :pid', ':pid', $psnId);
		mydb::where('s.`service` IN ("Treatment","Home Visit","Web Distance Treatment","Vitalsign")');

		if (!$isAccess) {
			mydb::where('s.`uid` = :uid', ':uid', i()->uid);
		}

		$stmt='SELECT
			  s.*, u.`username`, u.`name`, CONCAT(p.`name`," ",p.`lname`) patient_name
			FROM %imed_service% s
				LEFT JOIN %users% u USING (uid)
				LEFT JOIN %db_person% p ON p.`psnid`=s.`pid`
			%WHERE%
			GROUP BY `seq`
			ORDER BY s.`seq` DESC';
		$dbs = mydb::select($stmt);
		//$ret .= print_o($dbs,'$dbs');

		$tables = new Table();
		$tables->addClass('imed-vitalsign-item -center');
		$tables->thead = array('SEQ','date'=>'วันที่','น้ำหนัก<br />(ก.ก.)','ส่วนสูง<br />(ซ.ม.)','อุณหภูมิ<br />(C)','ชีพจร<br />(ครั้ง/นาที)','อัตราการหายใจ<br />(ครั้ง/นาที)','blood -hover-parent' => 'ความดันโลหิต<br />(มม.ปรอท)');
		foreach ($dbs->items as $rs) {
			$hasVitalsign=$rs->weight || $rs->height || $rs->temperature || $rs->pulse || $rs->respiratoryrate || $rs->bloodpressure || $rs->dbp || $rs->sbp;
			$tables->rows[]=array(
					$rs->seq,
					sg_date($rs->timedata,'ว ดด ปป'),
					$rs->weight ? $rs->weight : '',
					$rs->height ? $rs->height : '',
					$rs->temperature > 0 ? $rs->temperature : '',
					$rs->pulse ? $rs->pulse : '',
					$rs->respiratoryrate ? $rs->respiratoryrate : '',
					$rs->bloodpressure ? $rs->bloodpressure:$rs->sbp.($rs->sbp?'/':'').$rs->dbp
					.'<nav class="nav -icons -hover"><a class="sg-action" href="'.url('imed/visit/'.$psnId.'/form.vitalsign/'.$rs->seq,array('ref'=>'main')).'" data-rel="box" data-width="480" data-max-height="80%"><i class="icon '.($hasVitalsign?'-edit':'-add').'"></i></a></nav>',
				);
		}
		return $tables;
	}
}
?>