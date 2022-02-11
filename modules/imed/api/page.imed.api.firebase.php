<?php
/**
* iMed API :: Firebase API
* Created 2021-08-05
* Modify  2021-08-05
*
* @param Int $mainId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage imed/api/{id}/{action}[/{tranId}]
*/

$debug = true;

import('model:imed.visit');

class ImedApiFirebase extends Page {
	var $action;

	function __construct($action, $tranId = NULL) {
		$this->action = $action;
	}

	function build() {
		// debugMsg('mainId '.$this->mainId.' Action = '.$this->action.' TranId = '.$this->tranId);


		$ret = '';

		switch ($this->action) {
			case 'visitAdd' :
				ImedVisitModel::firebaseAdded(post('psnId'), post('seqId'));
				break;

			default:
				return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Action not found!!!']);
				break;
		}

		return $ret;
	}


}
?>