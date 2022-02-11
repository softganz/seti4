<?php
/**
* iMed Care :: Service Plan Add New Item
* Created 2021-08-27
* Modify  2021-08-27
*
* @param Object $requestInfo
* @return Widget
*
* @usage imed/care/req/{keyId}/plan.add
*/

$debug = true;

import('model:imed.plan');
import('package:imed/care/models/model.service.code');

class ImedCareReqPlanForm extends Page {
	var $reqId;
	var $keyId;
	var $tranId;
	var $requestInfo;

	function __construct($requestInfo, $tranId = NULL) {
		$this->reqId = $requestInfo->reqId;
		$this->keyId = $requestInfo->keyId;
		$this->tranId = $tranId;
		$this->requestInfo = $requestInfo;
	}

	function build() {
		// debugMsg($this->requestInfo,'$requestInfo');
		$isEdit = is_admin('imed care') || $this->requestInfo->giverId == i()->uid;
		if (!$isEdit) return message('error', 'Access Denied');

		$data = $this->tranId ? ImedPlanModel::get($this->requestInfo->carePlanId)->items[$this->tranId] : (Object) [] ;

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => ($this->tranId ? 'แก้ไข' : 'เพิ่ม').'แผนการให้บริการ',
				'boxHeader' => true,
				'boxHeaderBack' => _HEADER_BACK,
			]),
			'body' => new Form([
				'variable' => 'data',
				'action' => url('imed/care/api/req/'.$this->keyId.'/plan.tran.save'),
				'class' => 'sg-form',
				'checkValid' => true,
				'rel' => 'notify',
				'done' => 'back | load | load:.box-page',
				'children' => [
					'tranId' => ['type' => 'hidden', 'value' => $data->tranId],
					'planDate' => [
						'type' => 'text',
						'label' => 'วันที่ดูแล',
						'class' => 'sg-datepicker -fill',
						'require' => true,
						'value' => sg_date(SG\getFirst($data->planDate, date('Y-m-d')), 'd/m/Y'),
						'placeholder' => '31/12/2560',
					],
					'planTime' => [
						'type' => 'time',
						'label' => 'เวลา',
						'class' => '-fill',
						'require' => true,
						'readonly' => true,
						'start' => 6,
						'end' => 24,
						'step' => 60,
						'value' => $data->planTime,
					],
					'careCode' => [
						'type' => 'select',
						'label' => 'การดูแล:',
						'class' => '-fill',
						'value' => $data->careCode,
						'options' => (function() {
							$options = [];
							foreach (ServiceCodeModel::items(['menu' => true]) as $item) {
								$options[$item->serviceId] = $item->name;
							}
							return $options;
						})(),
					],
					'detail' => [
						'type' => 'textarea',
						'label' => 'รายละเอียดเพิ่มเติม',
						'class' => '-fill',
						'rows' => 4,
						'value' => $data->detail,
						'placeholder' => 'อธิบายรายละเอียดเพิ่มเติม',
					],
					'save' => [
						'type' => 'button',
						'value' => '<i class="icon -save -white"></i><span>{tr:SAVE}</span>',
						'pretext' => '<a class="sg-action btn -link -cancel" href="'.url('imed/care/req/'.$this->keyId).'" data-rel="back"><i class="icon -material -gray">cancel</i><span>{tr:CANCEL}</span></a>',
						'container' => '{class: "-sg-text-right"}',
					],
					// new DebugMsg($data, '$data'),
					$data->servDetail ? new Card([
						'children' => [
							new ListTile(['title' => $data->servDetail,]),
							new Container([
								'class' => 'detail -sg-paddingnorm',
								'child' => nl2br($data->servDescription)
							]),
						], // children
					]) : NULL, // Card
				], // children
			]), // Form
		]);
	}
}
?>