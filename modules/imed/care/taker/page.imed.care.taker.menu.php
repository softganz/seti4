<?php
/**
* iMed :: Care Taker Menu
* Created 2021-07-22
* Modify  2021-07-22
*
* @return Widget
*
* @usage imed/care/taker/0/menu
*/

$debug = true;

// import('package:imed/models/model.imed.user.php');
import('package:imed/care/widgets/widget.request.step.php');
import('package:imed/care/models/model.service.package.php');

class ImedCareTakerMenu {
	var $userInfo;
	var $currentStep;

	function __construct($userInfo = NULL, $currentStep = 0) {
		$this->userInfo = $userInfo;
		$this->currentStep = $currentStep;
	}

	function build() {
		if (!$this->userInfo->userId) return 'PROCESS ERROR';

		// debugMsg($this, '$this');

		if (post('data')) return $this->_save((Object) post('data'));

		return new Scaffold([
			'appBar' => new AppBar(['title' => 'ขอใช้บริการ']),
			'body' => new Container([
				'children' => [
					new RequestStepWidget([
						'currentStep' => $this->currentStep,
					]),
					new Form([
						'action' => url('imed/care/taker/0/menu'),
						'variable' => 'data',
						'class' => 'sg-form',
						'rel' => 'notify',
						'done' => 'reload:'.url('imed/care/req/last'),
						'children' => [
							'<section><h3 class="-sg-text-center">เลือกเมนูบริการ</h3>',
							'menu' => $this->_selectMenu(),
							// '<div class="-sg-text-right -sg-paddingmore"><a class="btn"><i class="icon -material">navigate_next</i><span>ถัดไป</span></a></div>',
							'</section>',

							'<section><h3 class="-sg-text-center">เลือกวันที่รับบริการ</h3>',
							'date' => $this->_selectDate(),
							'<h3 class="-sg-text-center">เลือกเวลารับบริการ</h3>',
							'time' => $this->_selectTime(),
							// '<div class="-sg-text-right -sg-paddingmore"><a class="btn"><i class="icon -material">navigate_next</i><span>ถัดไป</span></a></div>',
							'</section>',

							'save' => [
								'type' => 'button',
								'value' => '<i class="icon -material">done_all</i><span>บันทึกขอรับบริการ</span>',
								'container' => '{class: "-sg-text-right"}',
							],
						], // children
					]), // Form
				], // children
			]), // Container
		]); // Scaffold
	}

	function _selectMenu() {
		return [
			'type' => 'radio',
			'container' => '{class: "imed-care-choice"}',
			'options' => (function() {
				$result = [];
				foreach (ServicePackageModel::items(['package' => 1]) as $value) {
					$result[$value->serviceId] = '<i class="icon -material">radio_button_checked</i><i class="icon -imed-care"><img src="https://communeinfo.com/'.$value->icon.'" /></i><span>'.$value->name.' <a class="sg-action" href="'.url('imed/care/our/package/'.$value->serviceId).'" data-rel="box" data-width="480">รายละเอียด</a></span>';
				}
				return $result;
			})(),
		];
	}

	function _selectDate() {
		return [
			'type' => 'radio',
			'container' => '{class: "imed-care-choice"}',
			'options' => (function() {
				$result = [];
				for ($i = 0; $i <= 6; $i++) {
					$date = sg_date(strtotime('+'.$i.' day'), 'Y-m-d');
					$dateText = sg_date($date, 'ว ดดด ปปปป');
					$result[$date] = '<i class="icon -material">radio_button_checked</i>'
						. '<i class="icon -imed-care"></i><span>'.$dateText.'</span>';
				}
				return $result;
			})(),
		];
	}

	function _selectTime() {
		return [
			'type' => 'radio',
			'container' => '{class: "imed-care-choice"}',
			'options' => (function() {
				$result = [];
				foreach ([6,9,12,15,18,21,24,3] as $i) {
					$time = sprintf('%02d',$i).':00';
					$result[$time] = '<i class="icon -material">radio_button_checked</i>'
						. '<i class="icon -imed-care"></i><span>'.$time.' น.</span>';
				}
				return $result;
			})(),
		];
	}

	function _selectGiver() {
		return new Container([
			'children' => [
				'<h3 class="-sg-text-center">เลือกผู้ให้บริการ</h3>',
				new Ui([
					'class' => 'imed-care-menu-select',
					'children' => (function() {
						$result = [];
						foreach (ImedUserModel::givers() as $value) {
							$result[] = '<label><i class="icon -imed-care"><img src="'.model::user_photo($rs->username).'" /></i><span>'.$value->name.'</span><input class="-hidden" type="radio" name="data[giver]" value="'.$value->uid.'" /><i class="icon -material">radio_button_checked</i></label>';
						}
						return $result;
					})(),
				]),
				'<div class="-sg-text-right -sg-paddingmore"><a class="btn" href=""><i class="icon -material">navigate_next</i><span>ถัดไป</span></a></div>',
			], // children
		]);
	}

	function _save($post) {
		$data->servId = $post->menu;
		$data->takerId = i()->uid;
		$data->dateStart = sg_date($post->date,'Y-m-d');
		$data->timeStart = $post->time;
		$data->created = date('U');

		$stmt = 'INSERT INTO %imed_request%
			(`takerId`, `servId`, `dateStart`, `timeStart`, `created`)
			VALUES
			(:takerId, :servId, :dateStart, :timeStart, :created)';

		mydb::query($stmt, $data);

		// debugMsg(mydb()->_query);

		if (!mydb()->_error) {
			$reqId = mydb()->insert_id;
			do {
				$keyId = SG\uniqid(10);
				mydb::query('UPDATE %imed_request% SET `keyId` = :keyId WHERE `reqId` = :reqId LIMIT 1', ':reqId', $reqId, ':keyId', $keyId);
				if (mydb()->_affected_rows == 1) break;
			} while (true);
		}

		// debugMsg('Key Id = '.$keyId);
		// debugMsg(mydb()->_query);
		// debugMsg($data, '$data');
		// debugMsg($post, '$post');

		return 'บันทึกขอรับบริการเรียบร้อย';
	}
}
?>