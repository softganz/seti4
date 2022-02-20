<?php
/**
* Green :: Requirement API
* Created 2022-01-27
* Modify  2022-02-17
*
* @param Object $requirementId
* @param String $action
* @return Object
*
* @usage green/api/requirement/{id}[/{action}]
*/

import('model:green.requirement.php');

class GreenApiRequirement extends Page {
	var $requirementId;
	var $action;

	function __construct($requirementId, $action = NULL) {
		$this->action = $action;
		$this->requirementInfo = GreenRequirementModel::get($requirementId);
		$this->requirementId = $this->requirementInfo->requirementId;
		// debugMsg($this->requirementInfo, '$requirementInfo');
	}

	function build() {
		//TODO: ตรวจสอบ token ด้วย

		if ($this->requirementInfo->code) {
			return (Object) [
				'code' => $this->requirementInfo->code,
				'text' => $this->requirementInfo->text,
			];
		}

		switch ($this->action) {
			case 'delete':
				if ($this->requirementInfo->right->edit) {
					GreenRequirementModel::delete($this->requirementId);
					$result = (Object) ['code' => _HTTP_OK, 'text' => 'Requirement Deleted'];
				} else {
					$result = (Object) ['code' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'Access Denied'];
				}
				break;

			default:
				$result = $this->requirementInfo->info;
				break;
		}

		return $result;
	}
}
?>