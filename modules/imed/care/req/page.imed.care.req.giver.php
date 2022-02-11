<?php
/**
* iMed :: Care Seav
* Created 2021-07-30
* Modify  2021-09-03
*
* @param Object $requestInfo
* @return Widget
*
* @usage imed/care/req/{id}/giver
*/

$debug = true;

import('package:imed/models/model.imed.user.php');

class ImedCareReqGiver extends Page {
	var $seqId;
	var $keyId;
	var $requestInfo;

	function __construct($requestInfo) {
		$this->seqId = $requestInfo->seqId;
		$this->keyId = $requestInfo->keyId;
		$this->requestInfo = $requestInfo;
	}

	function build() {
		if (empty($this->requestInfo->giverId) && $this->requestInfo->is->admin) return $this->_selectGiver();

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->requestInfo->info->giverName,
				'leading' => '<img class="profile-photo" src="'.model::user_photo($this->requestInfo->info->takerUsername).'" />',
				'boxHeader' => true,
				'boxHeaderBack' => _HEADER_BACK,
			]),
			'body' => new Container([
				'children' => [
					new Column([
						'class' => 'popup-profile -clearfix -sg-text-center',
						'children' => [
							'<img class="-photo" src="'.model::user_photo($this->requestInfo->info->giverUsername).'" />',
							'<span class="-name">'.$this->requestInfo->info->giverName.'</span>',
							'<span class="-org">'.$userInfo->organization.'</span>',
							new Row([
								'children' => [
									'<a class="btn -link">0<br />พื้นที่</a>',
									'<a class="btn -link">0<br />เยี่ยมบ้าน</a>',
									'<a class="btn -link">0<br />ดูแล</a>',
								],
							]),
						],
					]),
					'พื้นที่ให้บริการ',
				], // children
			]), // Container
		]); // Scaffold
	}

	function _selectGiver() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'เลือกผู้ให้บริการ',
				'boxHeader' => true,
				'boxHeaderBack' => _HEADER_BACK,
			]),
			'body' => new Form([
				'action' => url('imed/care/api/req/'.$this->keyId.'/giver.add'),
				'class' => 'sg-form',
				'rel' => 'notify',
				'done' => 'close | reload',
				'children' => (function() {
					$result = [];
					$result['giver'] = [
						'type' => 'radio',
						'value' => $giver->uid,
						'container' => '{class: "imed-care-choice"}',
					];
					foreach (ImedUserModel::givers() as $giver) {
						$result['giver']['options'][$giver->uid] = '<i class="icon -material">radio_button_checked</i><i class="icon -imed-care"><img src="'.model::user_photo($giver->username).'" /></i><span>'.$giver->realName.'</span>';
					}
					$result['save'] = [
						'type' => 'button',
						'value' => '<i class="icon -material">done</i><span>บันทึกผู้ให้บริการ</span>',
						'container' => '{class: "-sg-text-right"}',
					];
					return $result;
				})(),
			]), // Form
		]);
	}
}
?>