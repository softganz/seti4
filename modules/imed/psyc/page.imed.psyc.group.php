<?php
/**
* iMed :: App Group Page Comtroller
* Created 2021-08-04
* Modify  2021-08-04
*
* @param Int $groupId
* @param String $adtion
* @return String
*
* @usage imed/app/group/{id}/{action}
*/

$debug = true;

import('model:imed.group');

class ImedPsycGroup extends Page {
	var $groupId;
	var $action;
	var $_args = [];

	function __construct($groupId = NULL, $action = NULL) {
		$this->groupId = $groupId;
		$this->action = $action;
		$this->_args = func_get_args();
		parent::__construct();
	}

	function build() {
		if (empty($this->action) && empty($this->groupId)) $this->action = 'home';
		else if (empty($this->action) && $this->groupId) $this->action = 'patient';

		$className = 'ImedGroup'.$this->action;
		// debugMsg('Load Class Name = '.$className);
		// debugMsg($this->_args, '$_args');

		import('page:imed.group'.($this->action ? '.'.$this->action : ''));
		if (class_exists($className)) {
			$groupInfo = is_numeric($this->groupId) ? ImedGroupModel::get($this->groupId, '{debug: false}') : NULL;

			$newClass = new $className($groupInfo, $this->_args[2], $this->_args[3], $this->_args[4]);
			$newClass->refApp = 'psyc';
			$newClass->urlView = 'imed/psyc/group/';
			$newClass->urlPatientView = 'imed/psyc/';
			// debugMsg('get_class = '.get_class($newClass));
			// debugMsg($newClass,' $newClass');
			return $newClass->build();
		} else {
			return 'ERROR: APP GROUP NOT FOUND';
		}
	}
}
?>


<?php
// *
// * Module :: Description
// * Created 2021-01-01
// * Modify  2021-01-01
// *
// * @param Object $self
// * @param Int $id
// * @return String
// *
// * @usage module/{id}/method


// $debug = true;

// import('page:imed.group');

// class ImedPsycGroup extends ImedGroup {
// 	var $ref = 'psyc';
// }
?>