<?php
/**
* iMed :: Care home page
* Created 2021-05-26
* Modify  2021-05-31
*
* @return Widget
*
* @usage imed/care
*/

$debug = true;

import('package:imed/models/model.imed.user.php');
import('widget:imed.care.sign.form');
import('package:imed/care/widgets/widget.hello.php');
import('package:imed/care/models/model.request.php');
import('package:imed/care/widgets/widget.request.list');

class ImedCareTaker {
	var $userId;
	var $action;
	private $_args;

	function __construct($userId = NULL, $action = NULL) {
		$this->userId = $userId;
		if ($this->userId === '0' && empty($action)) $action = 'info.home';
		$this->action = $action;
		$this->_args = func_get_args();
	}

	function build() {
		$userInfo = new ImedUserModel(['role' => 'IMED TAKER']);
		$isWaiting = $userInfo->isWaiting();
		$isEnable = $userInfo->isEnable();

		// debugMsg($userInfo, '$userInfo');
		if (!i()->ok) {
			// return new ImedCareSignFormWidget();
			return new SignForm();
		} else if (!$userInfo->isRole()) {
			return $this->_registerButton();
		}

		if ($this->action) {
			$argIndex = 2; // Start argument
			// debugMsg($this->_args);
			$ret = R::Page(
				'imed.care.taker.'.$this->action,
				$userInfo,
				$this->_args[$argIndex],
				$this->_args[$argIndex+1],
				$this->_args[$argIndex+2],
				$this->_args[$argIndex+3],
				$this->_args[$argIndex+4]
			);

			//debugMsg('TYPE = '.gettype($ret));
			if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';

			return $ret;

		}

		return new Scaffold([
			'body' => new Container([
				'children' => [
					new HelloWidget(['title' => 'สวัสดีผู้ใช้บริการ', 'name' => i()->name, 'address' => '']),
					new ScrollView([
						'child' => new Row([
							'class' => 'imed-care-menu -imed-info',
							'children' => [
								'<a href="'.url('imed/care/taker/0/menu').'" data-webview="ขอใช้บริการ"><i class="icon -imed-care -patient"></i><i class="icon -material" style="background-color: red; color: #fff;">add</i><span>ขอใช้บริการ</span></a>',
								'<a href="'.url('imed/care/taker/0/req').'" data-webview="บริการ"><i class="icon -imed-care -patient"></i><span>บริการ</span></a>',
								'<a href="'.url('imed/care/taker/0/giver').'" data-webview="ผู้ให้บริการ" data-options=\'{actionBar: false}\'><i class="icon -imed-care -giver"></i><span>ผู้ให้บริการ</span></a>',
								'<a href="'.url('imed/care/taker/0/eval').'" data-webview="ประเมิน"><i class="icon -imed-care -service"></i><span>ประเมิน</span></a>',
								'<a href="'.url('imed/care/taker/0/pay').'" data-webview="ค่าบริการ"><i class="icon -imed-care -patient"></i><span>ค่าบริการ</span></a>',
							], // children
						]), // Row
					]), // ScrollView
					new RequestListWidget([
						'title' => 'รอให้บริการ',
						'leading' => '<i class="icon -material">hourglass_empty</i>',
						'trailing' => '<a class="btn -link" href="'.url('imed/care/taker/0/req').'" data-webview="บริการ"><span>More</span><i class="icon -material">navigate_next</i></a>',
						'children' => RequestModel::items(['waiting' => true,'takerId' => i()->uid,]),
					]), // RequestListWidget
				], // children
			]), // Container
		]); // Scaffold
	}

	function _registerButton() {
		return new Container([
			'children' => [
				new HelloWidget(['name' => i()->name, 'address' => '']),
				new Card([
					'class' => '-sg-text-center -sg-paddingmore',
					'children' => [
						'<p>ท่านไม่ได้เป็นผู้รับบริการ ต้องการสมัครเป็นผู้รับบริการหรือไม่?</p>',
						'<a class="btn -primary" href="'.url('imed/care/regist/taker').'"><i class="icon -material">app_registration</i><span>สมัครเป็นผู้รับบริการ</span></a>',
					],
				]),
			], // children
		]);
	}
}
?>