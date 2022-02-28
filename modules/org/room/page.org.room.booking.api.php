<?php
/**
* Module :: Description
* Created 2021-10-31
* Modify  2021-10-31
*
* @param Int $resvId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage org/room/info/api/{id}/{action}[/{tranId}]
*/
import('package:org/room/models/model.room.php');
// url :: org/room/info/api/1/delete
$debug = true;

class OrgRoomBookingApi extends Page {
	var $resvid;
	var $action;
	var $tranId;
 

	function __construct($resvid, $action, $tranId = NULL) {
		$this->resvid = $resvid;
		$this->action = $action;
		$this->tranId = $tranId;
		// $this->roomInfo = $roomInfo;
	}

	function build() {
		//debugMsg('roomId '.$this->resvid.' Action = '.$this->action.' TranId = '.$this->tranId);

		if (empty($this->resvid)) return message(['responseCode' => _HTTP_ERROR_BAD_REQUEST, 'text' => 'PROCESS ERROR']);

		$isAccess = $roomInfo->RIGHT & _IS_ACCESS;
		$isEdit = $roomInfo->RIGHT & _IS_EDITABLE;

		// if (!$isAccess) return message(['responseCode' => _HTTP_ERROR_NOT_ALLOWED, 'text' => 'Access Denied']);
		// else if (!$isEdit) return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Access Denied']);

		$ret = '';
		switch ($this->action) {
			case 'anyedit' :
				echo RoomModel::editAnyuser($this->resvid,post());
				break;
			case 'adminedit' :
				if(in_array('officer',i()->roles) || in_array('teacher',i()->roles) ||  in_array('admin',i()->roles)) 
				{ echo RoomModel::editAdminuser($this->resvid,post()); }
				break;
			case 'checkAnyEdit' :
				return RoomModel::checkAnyEdit($this->resvid,$this->tranId);
				break;
			case 'editApprove' :
				//return $this->resvid.' '.$this->tranId;
				return RoomModel::EditApprove($this->resvid,$this->tranId);
				break;

		}

		// 	case 'delete':
		// 		mydb::query(
		// 			'DELETE FROM %calendar_room% WHERE `resvid` = :resvid LIMIT 1',
		// 			[':resvid', $this->resvid]
		// 		)
		// 		$ret .= mydb()->_query;
		// 		break;

		// 		case 'edit' :
		// 			mydb::query(
		// 				'UPDATE %calendar_room% SET `f1` = :f1 WHERE `resvid` = :resvid LIMIT 1',
		// 				[':resvid' => $this->resvid]
		// 			)
		// 		break;

		// 	default:
		// 		return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Action not found!!!']);
		// 		break;
		// }
		//echo $ret;
		return $ret;
		
	}
}
?>
