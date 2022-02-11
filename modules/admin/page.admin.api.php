<?php
/**
* Module :: Description
* Created 2021-12-27
* Modify  2021-12-27
*
* @param Int $mainId
* @param String $action
* @param Int $tranId
* @return String
*
* @usage module/api/{id}/{action}[/{tranId}]
*/

$debug = true;

class AdminApi extends Page {
	var $action;
	function __construct($action, $tranId = NULL) {
		$this->action = $action;
		$this->tranId = $tranId;
	}

	function build() {
		// debugMsg('mainId '.$this->mainId.' Action = '.$this->action.' TranId = '.$this->tranId);

		$ret = '';

		switch ($this->action) {
			case 'ip.ban' :
				if ($ip = post('ip')) {
					$banIpList = cfg('ban.ip');
					if (!is_object($banIpList)) $banIpList = (Object) [];
					$banTime = SG\getFirst(post('time'), cfg('ban.time'), 1*24*60); // Ban time in minute
					$banIpList->{$ip} = (Object) [
						'start' => date('Y-m-d H:i:s'),
						'end' => date('Y-m-d H:i:s', strtotime(date('Y-m-d H:i:s') . ' +'.$banTime.' minutes')),
					];
					$ret .= 'BAN IP '.$ip;
					cfg_db('ban.ip', SG\json_encode($banIpList));
					// debugMsg($banIpList, '$banIpList');
				}
				break;

			default:
				return message(['responseCode' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Action not found!!!']);
				break;
		}

		return $ret;
	}
}
?>