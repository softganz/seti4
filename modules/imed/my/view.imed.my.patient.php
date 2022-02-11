<?php
/**
* iMed :: My Psychiatry Care
* Created 2021-05-26
* Modify  2021-05-26
*
* @return Widget
*
* @usage R::View('imed.my.patient', [ref,item])
*/

$debug = true;

class ViewImedMyPatient extends Widget {
	var $ref;
	var $item;
	function __construct($args = []) {
		parent::__construct($args);
	}

	function build() {
		return new Ui([
			'join' => 'div',
			'class' => 'imed-patient-card ui-card -sg-flex',
			'children' => $this->_patientList(), // container
		]) // Ui
		;
	}

	function _patientList() {
		$refApp = ['app' => 'app/', 'psyc' => 'psyc/', 'care' => 'care/'][$this->ref];

		$result = [];

		mydb::where('s.`uid` = :uid AND s.`pid` IS NOT NULL', ':uid', i()->uid);
		mydb::value('$LIMIT$', $this->item ? 'LIMIT '.$this->item : '');

		foreach (mydb::select(
			'SELECT s.`pid` `psnid`, CONCAT(p.`name`," ",p.`lname`) `fullname`, COUNT(*) `visitTimes`
				FROM %imed_service% s
					LEFT JOIN %db_person% p ON p.`psnid` = s.`pid`
				%WHERE%
				GROUP BY `psnid`
				ORDER BY `visitTimes` DESC, CONVERT(`name` USING tis620) ASC
				$LIMIT$
				'
			)->items as $rs) {

			$result[] = '<div class="sg-action imed-patient-photo" href="'.url('imed/'.$refApp.$rs->psnid).'" data-webview="'.htmlspecialchars($rs->fullname).'">'
				. '<img class="-photo" src="'.imed_model::patient_photo($rs->psnid).'" width="100%" height="100%"/>'
				. '<span class="-name">'.$rs->fullname.'</span>'
				. '<span class="-number">'.$rs->visitTimes.'</span>'
				. '</div>';
		}
		return $result;
	}
}
?>