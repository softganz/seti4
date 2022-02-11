<?php
/**
* iMedCare :: My Serv
* Created 2021-08-04
* Modify  2021-08-04
*
* @param Object $userInfo
* @return Widget
*
* @usage imed/care/taker/0/req[/{servId}]
*/

$debug = true;

import('package:imed/care/models/model.request.php');
import('package:imed/care/widgets/widget.request.list');

class ImedCareTakerReq {
	var $userInfo;
	var $tranId;

	function __construct($userInfo) {
		$this->userInfo = $userInfo;
	}

	function build() {
		if (!$this->userInfo->userId) return 'PROCESS ERROR';

		return new Scaffold([
			'appBar' => new AppBar(['title' => 'บริการ', 'removeOnApp' => true]),
			'body' => new Container([
				'children' => [
					new RequestListWidget([
						'title' => 'รอให้บริการ',
						'leading' => '<i class="icon -material">hourglass_empty</i>',
						'children' => RequestModel::items(['waiting' => true,'takerId' => i()->uid,]),
					]), // RequestListWidget
					new RequestListWidget([
						'title' => 'รับบริการเรียบร้อย',
						'leading' => '<i class="icon -material">done_all</i>',
						'children' => RequestModel::items(['closed' => true,'takerId' => i()->uid,]),
					]), // RequestListWidget
				], // children
			]), // Container
		]); // Scaffold
	}
}
?>