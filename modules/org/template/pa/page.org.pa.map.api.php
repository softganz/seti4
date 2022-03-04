<?php
/**
* Module :: Description
* Created 2022-02-28
* Modify  2022-02-28
*
* @param Int $resvId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage org/room/info/api/{id}/{action}[/{tranId}]
*/
import('package:org/template/pa/models/model.pa.php');
// url :: org/room/info/api/1/delete
$debug = true;

class OrgPaMapApi extends Page {
	var $orgId;
	var $action;
	var $data;
 

	function __construct($orgId, $action, $data) {
		$this->orgId = $orgId;
		$this->action = $action;
		$this->data = $data;
		// $this->roomInfo = $roomInfo;
	}

	function build() {
		//debugMsg('roomId '.$this->resvid.' Action = '.$this->action.' TranId = '.$this->tranId);

		if (empty($this->orgId)) return message(['responseCode' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'PROCESS ERROR']);

		$isAccess = $roomInfo->RIGHT & _IS_ACCESS;
		$isEdit = $roomInfo->RIGHT & _IS_EDITABLE;

		// if (!$isAccess) return message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'Access Denied']);
		// else if (!$isEdit) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Access Denied']);

		$ret = '';
		switch ($this->action) {
			case 'delete' :
				//return 'delete '.$this->orgId;
				return PaModel::orgDel($this->orgId);
				break;
			case 'adminedit' :
				if(in_array('officer',i()->roles) || in_array('teacher',i()->roles) ||  in_array('admin',i()->roles)) 
				{ echo RoomModel::editAdminuser($this->resvid,post()); }
				break;

		}

		return $ret;
		
	}
}
?>
