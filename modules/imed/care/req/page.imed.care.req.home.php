<?php
/**
* iMed :: Care Seav
* Created 2021-07-30
* Modify  2021-08-02
*
* @param Object $requestInfo
* @return Widget
*
* @usage imed/care/req/{keyId}
*/

$debug = true;

import('model:imed.visit');
import('model:imed.plan');
import('widget:imed.visits');
import('package:imed/care/widgets/widget.request.step.php');

class ImedCareReqHome extends Page {
	var $reqId;
	var $keyId;
	var $currentStep;
	var $giverUsername;
	var $right;
	var $requestInfo;

	function __construct($requestInfo) {
		$this->reqId = $requestInfo->reqId;
		$this->keyId = $requestInfo->keyId;
		$this->requestInfo = $requestInfo;
		$this->giverUsername = $requestInfo->info->giverUsername;
		$this->right = (Object) [
			'access' => (i()->ok && in_array(i()->uid, [$requestInfo->giverId, $requestInfo->takerId])) || is_admin('imed'),
			'editTaker' => (i()->ok && $requestInfo->takerId == i()->uid) || is_admin('imed'),
			'editGiver' => (i()->ok && $requestInfo->giverId == i()->uid) || is_admin('imed'),
		];
	}

	function build() {
		if (!$this->right->access) return new ErrorMessage(['code' => _HTTP_ERROR_FORBIDDEN, 'text' => 'Access Denied']);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => SG\getFirst($this->requestInfo->serviceName,'Package ??'),
				'leading' => '<img class="profile-photo" src="'.model::user_photo($this->requestInfo->info->takerUsername).'" />',
				'trailing' => new Dropbox([
					'children' => [
						$this->requestInfo->is->admin ? '<a><i class="icon -material">delete</i><span>ลบคำขอรับบริการ</span></a>' : NULL,
					],
				]),
				'navigator' => $this->_showNavigator(),
			]),
			'body' => new Container([
				'class' => 'imed-care-req',
				'children' => [
					new RequestStepWidget([
						'keyId' => $this->keyId,
						'currentStep' => $this->currentStep,
						'activeStep' => array_merge([0], ($this->requestInfo->giverId ? [1] : [])),
						'giver' => $this->giverUsername ? ['username' => $this->giverUsername, 'id' => $this->requestInfo->giverId] : NULL,
					]),
					$this->_showTaker(),
					$this->_showGiver(),
					$this->requestWidget(),
					$this->patientWidget(),
					$this->planWidget(),
					new ImedVisitsWidget([
						'children' => ImedVisitModel::items(
							['reqId' => $this->requestInfo->reqId],
							'{items: "*", debug: false, debugResult: false}'
						)->items,
					]),

					// new DebugMsg($this->requestInfo,'$this->requestInfo'),

					$this->_script(),
				], // children
			]), // Container
		]); // Scaffold
	}

	function _showNavigator() {
		$nav = [];
		if ($this->requestInfo->is->admin) {
			$nav[] = new Ui([
				'children' => [
					$this->requestInfo->giverId ? '<a class="sg-action -img" href="'.url('imed/care/req/'.$this->keyId.'/giver').'" data-rel="box" data-width="full"><i class="icon -img"><img src="'.model::user_photo($this->requestInfo->info->giverUsername).'" /></i></a>' : '<a class="sg-action" href="'.url('imed/care/req/'.$this->keyId.'/giver').'" data-rel="box" data-width="full"><i class="icon -material">person_add</i></a>',
					'<a class="sg-action" href="'.url('imed/care/req/'.$this->keyId.'/plan').'" data-rel="box" data-width="full" title="แผนการให้บริการ"><i class="icon -material">list_alt</i></a>',
					$this->requestInfo->done ? '<a class="sg-action" href="'.url('imed/care/api/req/'.$this->requestInfo->keyId.'/done.cancel').'" data-rel="notify" data-done="reload" data-title="ยกเลิกสถานะบริการเรียบร้อย" data-confirm="ต้องการยกเลิกสถานะบริการเรียบร้อย กรุณายืนยัน?" title="ให้บริการเรียบร้อย""><i class="icon -material -complete">done_all</i><span></span></a>' : '<a class="sg-action" href="'.url('imed/care/api/req/'.$this->requestInfo->keyId.'/done').'" data-rel="notify" data-done="reload" data-title="บริการเรียบร้อย" data-confirm="ให้บริการเรียบร้อยแล้ว กรุณายืนยัน?" title="สิ้นสุดการให้บริการ""><i class="icon -material">done</i><span></span></a>',
					$this->requestInfo->closed ? '<a class="sg-action" href="'.url('imed/care/api/req/'.$this->keyId.'/closed.cancel').'" data-rel="notify" data-done="reload" data-title="เปิดสถานะบริการ" data-confirm="บริการยังไม่เรียบร้อย ต้องการเปิดคำขอบริการใหม่?" title="เปิดคำขอรับบริการ"><i class="icon -material -complete">verified</i></a>' : '<a class="sg-action" href="'.url('imed/care/api/req/'.$this->keyId.'/closed').'" data-rel="notify" data-done="reload" data-title="รับบริการเรียบร้อย" data-confirm="ได้รับบริการเรียบร้อยแล้ว ต้องการปิดคำขอบริการนี้?" title="ปิดคำขอรับบริการ"><i class="icon -material">verified</i></a>',
				],
			]);
		} else if ($this->requestInfo->is->taker) {
			$nav[] = new Ui([
				'children' => [
					'<a class="sg-action" href="'.url('imed/care/req/'.$this->keyId.'/plan').'" data-rel="box" data-width="full" title="แผนการให้บริการ"><i class="icon -material">list_alt</i></a>',
					$this->requestInfo->done ? '<a title="บริการเรียบร้อย"><i class="icon -material -complete">done_all</i></a>' : '<a title="ยังคงให้บริการ"><i class="icon -material">done</i></a>',
					$this->requestInfo->closed ? '<a title="บริการเรียบร้อย"><i class="icon -material -complete">verified</i></a>' : '<a title="ยังคงให้บริการ"><i class="icon -material">verified</i></a>',
				],
			]);
		} else if ($this->requestInfo->is->giver) {
			$nav[] = new Ui([
				'children' => [
					'<a class="sg-action" href="'.url('imed/care/req/'.$this->keyId.'/plan').'" data-rel="box" data-width="full" title="แผนการให้บริการ"><i class="icon -material">list_alt</i></a>',
					// '<a class="sg-action" href="'.url('imed/care/req/'.$this->requestInfo->keyId.'/write').'" data-rel="box" data-width="480" title="เขียนบันทึกการให้บริการ""><i class="icon -material">post_add</i><span></span></a>',
					$this->requestInfo->done ? '<a class="sg-action" href="'.url('imed/care/api/req/'.$this->requestInfo->keyId.'/done.cancel').'" data-rel="notify" data-done="reload" data-title="ยกเลิกสถานะบริการเรียบร้อย" data-confirm="ต้องการยกเลิกสถานะบริการเรียบร้อย กรุณายืนยัน?" title="ให้บริการเรียบร้อย""><i class="icon -material -complete">done_all</i><span></span></a>' : '<a class="sg-action" href="'.url('imed/care/api/req/'.$this->requestInfo->keyId.'/done').'" data-rel="notify" data-done="reload" data-title="บริการเรียบร้อย" data-confirm="ให้บริการเรียบร้อยแล้ว กรุณายืนยัน?" title="สิ้นสุดการให้บริการ""><i class="icon -material">done</i><span></span></a>',
				$this->requestInfo->closed ? '<a title="บริการเรียบร้อย"><i class="icon -material -complete">verified</i></a>' : '<a title="ยังคงให้บริการ"><i class="icon -material">verified</i></a>',
				],
			]);
		}


		return $nav;
	}

	function _showGiver() {
		if (!$this->requestInfo->giverId) return '<p class="notify">ยังไม่มีการกำหนดผู้ให้บริการ</p>';
		if ($this->requestInfo->giverId != i()->uid) return NULL;

		return new Container([
			'children' => [
				// new Card([
				// 	'children' => [
				// 		new ListTile([
				// 			'crossAxisAlignment' => 'center',
				// 			'leading' => '<img class="profile-photo" src="'.imed_model::patient_photo($this->requestInfo->psnId).'" width="29" height="29" />',
				// 			'title' => $this->requestInfo->psnId ? 'ข้อมูลผู้ป่วย '.$this->requestInfo->info->patientName : 'ไม่มีข้อมูลผู้ป่วย',
				// 			'trailing' => $this->requestInfo->psnId ? '<a class="sg-action btn -link" href="'.url('imed/care/req/'.$this->keyId.'/patient').'" data-rel="box" data-width="full"><i class="icon -material">navigate_next</i></a>' : NULL,
				// 		]),
				// 	],
				// ]),
				// new Row([
				// 	'class' => 'imed-care-menu',
				// 	'children' => [
				// 		'<a class="sg-action -primary" href="'.url('imed/care/req/'.$this->keyId.'/plan').'" data-rel="box"  data-width="full" title="แผนการให้บริการ"><i class="icon -material">list_alt</i><span>แผนการให้บริการ</span></a>',
				// 		'<a class="sg-action -primary" href="'.url('imed/care/req/'.$this->requestInfo->keyId.'/write').'" data-rel="box" data-width="full" title="เขียนบันทึกการให้บริการ""><i class="icon -material">post_add</i><span>บันทึกการให้บริการ</span></a>',
				// 	], // children
				// ]), // Row
			],
		]);
		// return new Ui([
		// 	'type' => 'menu',
		// 	// 'class' => '-sg-text-right',
		// 	'children' => [
		// 		'<a class="btn">แผนการให้บริการ</a>',
		// 		'<a class="sg-action btn" href="'.url('imed/care/req/'.$this->requestInfo->keyId.'/write').'" data-rel="box" data-width="480"><i class="icon -material">post_add</i><span>เขียนบันทึกการให้บริการ</span></a>',
		// 		'<a class="btn">สิ้นสุดการให้บริการ</a>',
		// 		// '<a class="btn">รอจ่ายเงิน</a>',
		// 		'<a class="btn">ปิดคำขอรับบริการ(Admin)</a>',
		// 	],
		// ]);
	}

	function _showTaker() {
		if ($this->requestInfo->takerId != i()->uid) return NULL;
		return new Container([
			'children' => [
				$this->requestInfo->done ? new Row([
					'class' => 'imed-care-menu',
					'style' => 'padding: 8px;text-align: center;',
					'children' => [
						'<a class="sg-action" href="'.url('imed/care/req/'.$this->keyId.'/paid').'" data-rel="box" data-width="full"><i class="icon -imed-care -material">attach_money</i><span>จ่ายเงิน</span></a>',
						'<a class="sg-action" href="'.url('imed/care/req/'.$this->keyId.'/eval').'" data-rel="box" data-width="full"><i class="icon -imed-care -material">rule</i><span>ประเมินผลการรับบริการ</span></a>',
					],
				]) : NULL,
				// print_o($this->requestInfo),
			],
		]);
	}

	function patientWidget() {
		return new Card([
			'class' => $this->right->editTaker ? 'sg-inline-edit' : NULL,
			'attribute' => $this->right->editTaker ? [
				'data-update-url' => url('imed/care/api/req/'.$this->keyId.'/patient.pain'),
				'data-debug' => debug('inline') ? 'inline' : NULL,
			] : NULL,
			'children' => [
				new ListTile([
					'crossAxisAlignment' => 'center',
					'leading' => '<img class="profile-photo" src="'.imed_model::patient_photo($this->requestInfo->psnId).'" width="29" height="29" />',
					'title' => $this->requestInfo->psnId ? 'ข้อมูลผู้ป่วย '.$this->requestInfo->info->patientName : 'ไม่มีข้อมูลผู้ป่วย',
					'trailing' => $this->requestInfo->psnId ? '<a class="sg-action btn -link" href="'.url('imed/care/req/'.$this->keyId.'/patient').'" data-rel="box" data-width="full"><i class="icon -material">'.($this->requestInfo->is->taker ? 'edit' : 'navigate_next').'</i></a>' : '<a class="sg-action btn -primary" href="'.url('imed/care/req/'.$this->keyId.'/patient.add').'" data-rel="box" data-width="full"><i class="icon -material">person_add_alt</i><span>เพิ่มข้อมูลผู้ป่วย</span></a>',
				]),

				$this->requestInfo->psnId ? new Column([
					'class' => '-sg-paddingnorm',
					'children' => [
						'อายุ',
						'ศาสนา',
						'ที่อยู่',
						new ListTile(['title' => 'รายละเอียดความเจ็บป่วย']),
						view::inlineedit(
							[
								'group' => '',
								'fld' => 'detail',
								'value' => $this->requestInfo->info->detail,
							],
							nl2br($this->requestInfo->info->detail),
							$this->right->editTaker,
							'textarea'
						),
						'โรคประจำตัว',
						'การพึ่งพาทางการแพทย์',
						'กิจกรรมที่ทำต่อเนื่อง',
						'ภาวะแทรกซ้อน',
					], // children
				]) : NULL, // Container
			],
		]);
	}

	function requestWidget() {
		return new Card([
			'children' => [
				new ListTile([
					'title' => 'รายละเอียดบริการ',
					'leading' => '<i class="icon -material -gray">info</i>',
				]), // ListTile
				new Container([
					'class' => 'detail -sg-paddingnorm',
					'children' => [
						'ผู้ขอใช้บริการ :: <b>'.$this->requestInfo->info->takerName.'</b> เมื่อ '.sg_date($this->requestInfo->info->created, 'ว ดด ปปปป H:i').' น.<br />วันที่ขอใช้บริการ :: '.sg_date($this->requestInfo->info->dateStart).' '.substr($this->requestInfo->info->timeStart,0,5).' น.<br />',
						$this->requestInfo->giverId ? 'ผู้ให้บริการ :: <b>'.$this->requestInfo->info->giverRealName.'</b>' : NULL,
					], // children
				]), // Container
			], // children
		]);
	}

	function planWidget() {
		return new Card([
			'children' => [
				new ListTile([
					'title' => 'แผนการให้บริการ',
					'leading' => '<i class="icon -material">list_alt</i>',
					'trailing' => new Row([
						'children' => [
							$this->requestInfo->psnId && ($this->requestInfo->is->giver || $this->requestInfo->is->admin) ? '<a class="sg-action btn -primary" href="'.url('imed/care/req/'.$this->keyId.'/plan').'" data-rel="box"  data-width="full" title="แผนการให้บริการ"><i class="icon -material">add_circle_outline</i><span>เพิ่มรายการ</span></a>' : NULL,
						], // children
					]), // Row
				]),
				new Container([
					'class' => '-plan-list',
					'children' => (function() {
						$widgets = [];
						if (!$this->requestInfo->psnId) {
							$widgets[] = new Card(['class'=>'-sg-text-center','child' => 'ยังไม่มีข้อมูลผู้ป่วย']);
						} else if (!$this->requestInfo->carePlanId) {
							$widgets[] = new Card(['class'=>'-sg-text-center','child' => 'ยังไม่มีแผนการให้บริการ']);
						} else {
							$isEdit = $this->requestInfo->is->giver || $this->requestInfo->is->admin;
							foreach (ImedPlanModel::get($this->requestInfo->carePlanId, ['debug' => false])->items as $item) {
								$widgets[] = new ListTile([
									'title' => $item->servName,
									'subtitle' => '@'.sg_date($item->planDate, 'ว ดด ปปปป').' '.substr($item->planTime, 0, 5).' น.',
									'leading' => '<img class="profile-photo" src="https://communeinfo.com/'.$item->servIcon.'" width="48" height="48" style="width: 29px; height: 29px;" />',
									'trailing' => new Row([
										'children' => [
											$isEdit ? '<a class="sg-action btn -link" href="'.url('imed/care/req/'.$this->keyId.'/plan.form/'.$item->tranId).'" data-rel="box" data-width="full"><i class="icon -material">edit</i></a>' : NULL,
											$isEdit ? '<a class="sg-action btn'.($item->seqId ? ' -success' : '').'" href="'.url('imed/care/req/'.$this->requestInfo->keyId.'/write/'.$item->tranId).'" data-rel="box" data-width="full" title="'.($item->seqId ? 'แก้ไข' : 'เขียน').'บันทึกการให้บริการ""><i class="icon -material">post_add</i></a>' : NULL,
											// '<a class="btn -link"><i class="icon -material">post_add</i></a>'
										],
									]),
								]); // ListTile
							}
							if (empty($widgets)) $widgets[] = new Card(['class'=>'-sg-text-center','child' => 'ยังไม่มีแผนการให้บริการ']);
						}
						return $widgets;
					})(), // children
				]), // Container
			], // children
		]);
	}

	function _script() {
		head('<style>
			.imed-care-menu .icon.-imed-care.-material {font-size: 48px; width: 64px; height: 64px; line-height: 64px;}
		</style>');

		return '<script type="text/javascript">
		function onWebViewComplete() {
			console.log("CALL onWebViewComplete FROM WEBVIEW")
			var options = {
				actionBar: false,
			}
			return options
		}
		</script>';
	}
}
?>