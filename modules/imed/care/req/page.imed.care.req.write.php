<?php
/**
* iMed Care :: Write Visit/Service
* Created 2021-07-30
* Modify  2021-08-02
*
* @param Object $requestInfo
* @return Widget
*
* @usage imed/care/req/{keyId}/write
*/

$debug = true;

import('model:imed.plan');
import('model:imed.visit');

class ImedCareReqWrite extends Page {
	var $reqId;
	var $keyId;
	var $planTranId;
	var $requestInfo;

	function __construct($requestInfo, $planTranId = NULL) {
		$this->reqId = $requestInfo->reqId;
		$this->keyId = $requestInfo->keyId;
		$this->planTranId = $planTranId;
		$this->requestInfo = $requestInfo;
	}

	function build() {
		$planItem = ImedPlanModel::get($this->requestInfo->carePlanId)->items[$this->planTranId];
		// debugMsg(new DebugMsg($planItem, '$planItem'), 'PlanItem');
		$data = $planItem->seqId ? ImedVisitModel::get($this->requestInfo->psnId, $planItem->seqId) : NULL;
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'บันทึกการให้บริการ',
				'boxHeader' => true,
				'boxHeaderBack' => _HEADER_BACK,
			]),
			'body' => new Form([
				'action' => url('imed/care/api/req/'.$this->keyId.'/visit.save'),
				'class' => 'sg-form',
				'rel' => 'notify',
				'done' => 'close | load',
				'checkValid' => true,
				'children' => [
					'tranId' => ['type' => 'hidden', 'value' => $this->planTranId],
					'seqId' => $planItem->seqId ? ['type' => 'hidden', 'value' => $planItem->seqId] : NULL,
					'servDate' => [
						'type' => 'text',
						'label' => 'วันที่ให้บริการ',
						'class' => 'sg-datepicker -fill',
						'require' => true,
						'value' => sg_date(SG\getFirst($data->visitDate, date('Y-m-d')), 'd/m/Y'),
						'placeholder' => '31/12/2560',
					],
					'servTime' => [
						'type' => 'time',
						'label' => 'เวลา',
						'class' => '-fill',
						'require' => true,
						'readonly' => true,
						'start' => 6,
						'end' => 24,
						'step' => 60,
						'value' => $data->visitTime,
					],
					'msg' => [
						'type' => 'textarea',
						'label' => 'รายละเอียดการทำงาน',
						'class' => '-fill',
						'require' => true,
						'rows' => '8',
						'value' => SG\getFirst($data->detail, $planItem->detail),
						'placeholder' => 'ข้อความรายละเอียดการทำงาน',
					],
					'save' => [
						'type' => 'button',
						'value' => '<i class="icon -material">done_all</i><span>บันทึกการทำงาน</span>',
						'pretext' => '<a class="sg-action btn -link -cancel" data-rel="back"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
						'container' => '{class: "-sg-text-right"}',
					],
					// new DebugMsg($data, '$data'),
					// new DebugMsg($planItem,'$planItem'),
					// new DebugMsg($this->requestInfo,'$requestInfo'),
					$planItem->servDetail ? new Card([
						'children' => [
							new ListTile(['title' => $planItem->servDetail,]),
							new Container([
								'class' => 'detail -sg-paddingnorm',
								'child' => nl2br($planItem->servDescription)
							]),
						], // children
					]) : NULL, // Card
				], // children
			]), // Form
		]);
	}
}
?>