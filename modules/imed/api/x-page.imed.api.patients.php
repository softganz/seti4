<?php
/**
* iMed API :: Patient List
* Created 2021-07-04
* Modify  2021-07-12
*
* @return Widget
*
* @usage imed/api/patients?start=$start&item=$item
*/

$debug = true;

import('model:imed.patient');

class ImedApiPatients extends Page {
	var $start = 0;
	var $item = 10;

	function __construct($args = []) {
		parent::__construct($args);
	}

	function build() {
		$result = [];

		$getUserId = post('u');
		$showItems = SG\getFirst($this->item,10);
		$isAdmin = user_access('administer imeds');

		$conditions = (Object)[];
		$options = (Object)[];

		// $options->debug = true;

		if ($getUserId && ($isAdmin || (i()->ok && i()->uid == $getUserId))) {
			$conditions->userId = $getUserId;
		} else if (i()->ok) {
			$conditions->userId = i()->uid;
		} else {
			return $result;
		}

		$result = PatientModel::items($conditions, $options);

		return $result;
	}

}
?>