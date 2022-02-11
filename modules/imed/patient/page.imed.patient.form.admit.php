<?php
/**
* iMed :: Form for Patient Admit
* Created 2021-08-19
* Modify  2021-08-19
*
* @param Object $patientInfo
* @return Widget
*
* @usage module/{id}/method
*/

$debug = true;

class ImedPatientFormAdmit extends Page {
	var $psnId;
	var $patientInfo;

	function __construct($patientInfo) {
		$this->psnId = $patientInfo->psnId;
		$this->patientInfo = $patientInfo;
	}

	function build() {
		$isAccess = $this->patientInfo->RIGHT & _IS_ACCESS;

		if (!$isAccess) return message(['responseCode' => _HTTP_ERROR_UNAUTHORIZED, 'text' => 'Access Denied']);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => $this->patientInfo->info->admit ? 'ประวัติการเข้ารับการรักษา' : 'Admit?',
				'boxHeader' => true,
				'leading' => _HEADER_BACK,
			]),
			'body' => new Widget([
				'children' => [
					$this->patientInfo->info->admit ? $this->_admitHistory() : $this->_admitForm(),
					$this->_script(),
				], // children
			]), // Widget
		]);
	}

	function _admitForm() {
		return new Card([
			'children' => [
				new Form([
					'action' => url('imed/patient/'.$this->psnId.'/info/tran.save'),
					'class' => 'sg-form',
					'rel' => 'none',
					'checkValid' => true,
					'done' => 'callback:admitDone | close',
					'children' => [
						'code' => ['type' => 'hidden', 'value' => 'admit'],
						'admit' => ['type' => 'hidden', 'value' => 'yes'],
						'detail2' => [
							'type' => 'text',
							'label' => 'โรงพยาบาล/สถานพยาบาล',
							'class' => '-fill',
							'require' => true,
							'placeholder' => 'ระบุชื่อโรงพยาบาล/สถานพยาบาลที่เข้ารับการรักษา',
						],
						'detail1' => [
							'type' => 'textarea',
							'class' => '-fill',
							'require' => true,
							'rows' => 2,
							'placeholder' => 'เขียนบันทึกการ Admit',
						],
						'save' => [
							'type' => 'button',
							'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
							'container' => '{class: "-sg-text-right"}',
						]
					], // children
				]), // Form
				$this->_admitHistory(),
			], // children
		]);
	}

	function _admitHistory() {
		return new Container([
			'children' => (function() {
				$widgets = [];
				foreach (array_reverse($this->patientInfo->admit) as $item) {
					$widgets[] = new Card([
						'children' => [
							new ListTile([
								'title' => $item->detail2,
								'subtitle' => '@'.sg_date($item->created, 'ว ดด ปปปป'),
								'trailing' => $item->detail4 ?
									sg_date($item->detail4, 'ว ดด ปปปป').'</span>'.'<i class="icon -material">restore</i><span>'
									:
									new Form([
									'action' => url('imed/api/patient/'.$this->psnId.'/admit.backhome'),
									'class' => 'sg-form -sg-flex',
									'rel' => 'none',
									'done' => 'close | reload',
									'children' => [
										'tranId' => ['type' => 'hidden', 'value' => $item->tr_id],
										'backDate' => [
											'type' => 'text',
											// 'label' => 'วันที่กลับบ้าน',
											'class' => '-date sg-datepicker',
											'value' => date('d/m/Y'),
											'attr' => ['style' => 'width: 5em',],
										],
										'save' => [
											'type' => 'button',
											'value' => '<i class="icon -material">done</i><span>บันทึกกลับบ้าน</span>',
										],
									], // children
								]), // Form
							]), // ListTile
						], // children
					]); // Card
				}
				return $widgets;
			})(), // children
		]);
	}
	function _script() {
		return '<script type="text/javascript">
		function admitDone() {
			$("#imed-admit-button").addClass("-admit")
		}
		</script>';
	}
}
?>