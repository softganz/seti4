<?php
/**
* iMedCare :: My Giver
* Created 2021-08-04
* Modify  2021-08-04
*
* @param Object $userInfo
* @return Widget
*
* @usage imed/care/taker/0/giver[/{giverId}]
*/

$debug = true;

import('package:imed/care/models/model.taker');

class ImedCareTakerGiver {
	var $userInfo;

	function __construct($userInfo, $giverId = NULL) {
		$this->userId = $userInfo->userId;
		$this->giverId = $giverId;
		$this->userInfo = $userInfo;
	}

	function build() {
		if (!$this->userInfo->userId) return 'PROCESS ERROR';

		if ($this->giverId) return $this->_showGiver();

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ผู้ให้บริการ',
			]),
			'body' => new Container([
				'children' => (function() {
					$result = [];
					foreach (TakerModel::giverList(['userId' => $this->userId], '{debug: false}') as $item) {
						$result[] = new ListTile([
							'class' => 'sg-action',
							'href' => url('imed/care/taker/0/giver/'.$item->giverId),
							'rel' => 'box',
							'data-class-name' => '-full',
							'title' => $item->name,
							'leading' => '<img class="profile-photo" src="'.model::user_photo($item->username).'" />',
							'subtitle' => 'From '.sg_date($item->firstRequestDate, 'ว ดด ปปปป').' - '.sg_date($item->lastRequestDate, 'ว ดด ปปปป'),
							'trailing' => '<i class="icon -material">navigate_next</i>',
						]);
						// debugMsg($item,'$item');
					}
					return $result;
				})(), // children
			]), // Container
		]); // Scaffold
	}

	function _showGiver() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'GIVER '.$this->giverId,
				'boxHeader' => true,
			]),
			'body' => new Container([
				'children' => [
					'Giver'
				],
			]), // body
		]);
	}
}
?>