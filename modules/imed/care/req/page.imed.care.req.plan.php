<?php
/**
* iMed Care :: Service Plan
* Created 2021-07-30
* Modify  2021-08-02
*
* @param Object $requestInfo
* @return Widget
*
* @usage imed/care/req/{keyId}/plan
*/

$debug = true;

import('model:imed.plan');

class ImedCareReqPlan extends Page {
	var $reqId;
	var $keyId;
	var $requestInfo;
	var $planInfo;

	function __construct($requestInfo) {
		$this->reqId = $requestInfo->reqId;
		$this->keyId = $requestInfo->keyId;
		$this->requestInfo = $requestInfo;
		$this->planInfo = ImedPlanModel::get($requestInfo->carePlanId);
	}

	function build() {
		// debugMsg($this->requestInfo,'$requestInfo');
		$this->isEdit = $isEdit = is_admin('imed care') || $this->requestInfo->giverId == i()->uid;

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'แผนการให้บริการ',
				'boxHeader' => true,
				'boxHeaderBack' => _HEADER_BACK,
				'navigator' => [
					$isEdit ? '<a class="sg-action" href="'.url('imed/care/req/'.$this->keyId.'/plan.form').'" data-rel="box" data-width="full"><i class="icon -material">add</i><span>เพิ่มรายการ</span></a>' : NULL,
				],
			]),
			'body' => new Widget([
				'children' => [
					new Container([
						'children' => (function(){
							$result = [];
							foreach ($this->planInfo->items as $item) {
								$result[] = new Card([
									'children' => [
										new ListTile([
											'title' => $item->servName,
											'subtitle' => '@'.sg_date($item->planDate, 'ว ดด ปปปป').' '.substr($item->planTime, 0, 5).' น.',
											'leading' => '<img class="profile-photo" src="https://communeinfo.com/'.$item->servIcon.'" width="48" height="48" style="width: 48px; height: 48px;" />',
											'trailing' => new Row([
												'children' => [
													$this->isEdit ? '<a class="sg-action btn -link" href="'.url('imed/care/req/'.$this->keyId.'/plan.form/'.$item->tranId).'" data-rel="box" data-width="full"><i class="icon -material">edit</i></a>' : NULL,
													// $this->isEdit ? '<a class="sg-action btn -link" href="'.url('imed/care/req/'.$this->keyId.'/write/'.$item->tranId).'" data-rel="box" data-width="full"><i class="icon -material">post_add</i></a>' : NULL,
													$this->isEdit ? '<a class="sg-action btn -link" href="'.url('imed/plan/api/'.$item->planId.'/tran.remove/'.$item->tranId).'" data-rel="notify" data-done="remove:parent .widget-card | load" data-title="ลบรายการ" data-confirm="ลบรายการนี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a>' : NULL,
												],
											]),
										]), // ListTile
										new Container([
											'class' => '-sg-paddingmore',
											'child' => nl2br($item->servDetail),
										]), // Container
										$item->detail ? new Container([
											'class' => '-sg-paddingmore',
											'child' => nl2br($item->detail),
										]) : NULL,
										// print_o($item,'$item'),
									],
								]);
							}
							return $result;
						})(), // children
					]), // Container
				], // children
			]), // body
		]);
	}
}
?>