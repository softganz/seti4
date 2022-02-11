<?php
/**
* iMed :: Patient Need List
* Created 2021-06-01
* Modify  2021-06-01
*
* @param ['patient' => $patientInfo, 'ref']
* @return Widget
*
* @usage new ImedPatientNeedWidget([])
*/

$debug = true;

class ImedPatientNeedWidget extends Widget {
	var $patient;
	var $ref;

	function __construct($args = []) {
		parent::__construct($args);
	}

	function build() {
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
		$dbs = mydb::select($stmt, ':psnid', $this->patient->psnId);

		return $this->_list($dbs->items);
	}

	function _list($items = []) {
		$ui = new Ui([
			'type' => 'card',
			'tagName' => 'div',
			'class' => 'imed-my-note',
			'id' => 'imed-my-note',
		]);

		if (empty($items)) return $ui->add('ไม่มีข้อมูลความต้องการ');

		foreach ($items as $rs) {
			$ui->add(
				R::View(
					'imed.need.render',
					$rs,
					['ref' => $this->ref]
				),
				'{class: "-urgency-'.$rs->urgency.'", id: "noteUnit-'.$rs->seq.'"}'
			);
		}
		return $ui;
	}
}
?>