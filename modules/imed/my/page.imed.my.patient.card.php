<?php
/**
* iMed :: My Patient Card
* Created 2020-09-28
* Modify  2021-05-26
*
* @return Widget
*
* @usage imed/my/patient/card
*/

$debug = true;

class ImedMyPatientCard extends Page {
	var $ref;
	var $item;

	function __construct() {
		$this->ref = post('ref');
		$this->item = SG\getFirst(post('item'), 5);
	}

	function build() {
		return R::View('imed.my.patient', ['ref' => $this->ref, 'item' => $this->item]);
	}
}
?>