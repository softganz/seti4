<?php
/**
* Module Method
* Created 2020-08-01
* Modify  2020-08-01
*
* @param
* @return Widget
*/

$debug = true;

class RequestStepWidget extends Widget {
	var $keyId;
	var $currentStep;
	var $activeStep = [];
	var $giver = [];

	function __construct($args = []) {
		parent::__construct($args);
	}

	function build() {
		return new StepMenuWidget([
			'currentStep' => $this->currentStep,
			'activeStep' => $this->activeStep,
			'children' => [
				'<a class="status" href="javascript:void(0)" title="เมนูบริการ"><i class="icon -material">widgets</i></a>',
				$this->giver ? '<a class="status sg-action" href="'.url('imed/care/req/'.$this->keyId.'/giver').'" data-rel="box" data-width="full" title="ผู้ให้บริการ"><img src="'.model::user_photo($this->giver['username']).'" width="100%" /></a>' : '<a class="status" href="javascript:void(0)" title="ผู้ให้บริการ"><i class="icon -material">how_to_reg</i></a>',
				'<a class="status" href="javascript:void(0)" title="จ่ายค่าบริการ"><i class="icon -material">attach_money</i></a>',
				'<a class="status" href="javascript:void(0)" title="ประเมิน"><i class="icon -material">rule</i></a>',
			], // children
		]);
	}
}
?>