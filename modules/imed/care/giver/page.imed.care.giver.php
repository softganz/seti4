<?php
/**
* iMed :: Care Giver Home Page
* Created 2021-05-26
* Modify  2021-07-22
*
* @return Widget
*
* @usage imed/care/giver[/0/$action]
*/

$debug = true;

import('model:imed.user.php');
import('package:imed/care/widgets/widget.hello.php');
import('package:imed/care/widgets/widget.signform.php');
import('package:imed/care/models/model.request.php');
import('package:imed/care/widgets/widget.request.list');

class ImedCareGiver extends Page {
	var $userId;
	var $action;

	function __construct($userId = NULL, $action = NULL) {
		$this->userId = $userId;
		if ($this->userId === '0' && empty($action)) $action = 'info.home';
		$this->action = $action;
		// debugMsg('User = '.$this->userId);
	}

	function build() {
		if (!i()->ok) {
			// return new ImedCareSignFormWidget();
			return new SignForm();
		}

		$userInfo = new ImedUserModel(['userId' => $this->userId, 'role' => 'IMED GIVER']);
		$isWaiting = $userInfo->isWaiting();
		$isEnable = $userInfo->isEnable();

		// debugMsg($userInfo, '$userInfo');

		if (!$userInfo->isRole()) {
			return $this->_registerButton();
		// } else if ($isWaiting) {
		// 	return '<p class="notify">อยู่ระหว่างรอการตรวจสอบและอนุมัติ</p>';
		// } else if (!$isEnable) {
		// 	return '<p class="notify">ผู้ให้บริการอยู่ในสถานะยกเลิกการให้บริการ กรุณาติดต่อผู้ดูแลระบบ</p>';
		}

		if ($this->action) {
			$argIndex = 3; // Start argument
			$ret = R::Page(
				'imed.care.giver.'.$this->action,
				$userInfo,
				func_get_arg($argIndex),
				func_get_arg($argIndex+1),
				func_get_arg($argIndex+2),
				func_get_arg($argIndex+3),
				func_get_arg($argIndex+4)
			);

			//debugMsg('TYPE = '.gettype($ret));
			if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';

			return $ret;

		}

		return new Scaffold([
			'body' => new Container([
				'children' => [
					new HelloWidget(['title' => 'สวัสดีผู้ให้บริการ', 'name' => i()->name, 'address' => '']),
					new ScrollView([
						'child' => new Row([
							'class' => 'imed-care-menu -imed-info',
							'children' => [
								// $isWaiting ? '<p class="-sg-paddingmore -sg-text-center">ท่านได้สมัครเป็นผู้ให้บริการเรียบร้อยแล้ว อยู่ระหว่างรอการตรวจสอบและอนุมัติ</p>' : '',
								'<a href="'.url('imed/care/giver/0/profile').'" data-webview="ข้อมูลผู้ให้บริการ"><i class="icon -imed-care -giver"></i><span>ข้อมูลผู้ให้บริการ</span></a>',
								$isEnable ? '<a href="'.url('imed/care/giver/0/calendar').'" data-webview="นัดหมายบริการ"><i class="icon -imed-care -giver"></i><span>นัดหมายบริการ</span></a>' : '<a class="-disabled"><i class="icon -imed-care -giver"></i><span>นัดหมายบริการ</span></a>',
								$isEnable ? '<a href="'.url('imed/care/giver/0/do').'" data-webview="บันทึกการให้บริการ"><i class="icon -imed-care -giver"></i><span>บันทึกการให้บริการ</span></a>' : '<a class="-disabled"><i class="icon -imed-care -giver"></i><span>บันทึกการให้บริการ</span></a>',
								$isEnable ? '<a href="'.url('imed/care/giver/0/money').'" data-webview="รับค่าบริการ"><i class="icon -imed-care -giver"></i><span>รับค่าบริการ</span></a>' : '<a class="-disabled"><i class="icon -imed-care -giver"></i><span>รับค่าบริการ</span></a>',
							], // children
						]), // Row
					]),
					'<div style="height: 8px;"></div>',
					$isWaiting ? '<p class="notify">อยู่ระหว่างรอการตรวจสอบและอนุมัติ</p>' : NULL,
					new RequestListWidget([
						'title' => 'รอให้บริการ',
						'leading' => '<i class="icon -material">hourglass_empty</i>',
						'children' => RequestModel::items(['waiting' => true,'giverId' => i()->uid,]),
					]),
					'<div style="height: 8px;"></div>',
					new RequestListWidget([
						'title' => 'ให้บริการเรียบร้อย',
						'leading' => '<i class="icon -material">done_all</i>',
						'children' => RequestModel::items(['closed' => true,'giverId' => i()->uid,]),
					]),
				], // children
			]), // Container
		]); // Scaffold
	}

	function _registerButton() {
		return new Container([
			'class' => '-sg-text-center',
			'children' => [
				'<p>ท่านไม่ได้เป็นผู้ให้บริการ ต้องการสมัครเป็นผู้ให้บริการหรือไม่?</p>',
				'<a class="btn -primary" href="'.url('imed/care/regist/giver').'"><i class="icon -material">app_registration</i><span>สมัครเป็นผู้ให้บริการ</span></a>',
			], // children
		]);
	}
}
?>