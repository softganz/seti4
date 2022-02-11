<?php
/**
* iMed Care :: Request Evaluate
* Created 2021-09-08
* Modify  2021-08-08
*
* @param Object $requestInfo
* @return Widget
*
* @usage imed/care/req/{keyId}/eval
*/

$debug = true;

class ImedCareReqEval extends Page {
	var $reqId;
	var $keyId;
	var $requestInfo;

	function __construct($requestInfo) {
		$this->reqId = $requestInfo->reqId;
		$this->keyId = $requestInfo->keyId;
		$this->requestInfo = $requestInfo;
	}

	function build() {
		$this->isEdit = $isEdit = is_admin('imed care') || $this->requestInfo->giverId == i()->uid;

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'ประเมินผลการรับบริการ',
				'boxHeader' => true,
				'boxHeaderBack' => _HEADER_BACK,
				'navigator' => [
				],
			]),
			'body' => new Card([
				'action' => url('imed/req/'.$this->keyId.'/eval'),
				'style' => 'overflow: hidden',
				'children' => [
					new ListTile([
						'title' => 'กิจกรรมบริการดูแลที่บ้าน',
						'leading' => '<i class="icon -material">rule</i>',
					]),
					new Table([
						'children' => (function() {
							$list = [
								'บุคลิกภาพและหัวใจบริการ (การตรงต่อเวลา การแต่งกาย การใช้คำพูด การช่วยเหลือดูแล)',
								'การทำความสะอาดร่างกาย และเปลี่ยนเสื้อผ้า',
								'การทำความสะอาดที่นั่ง/ที่นอน',
								'การให้อาหาร (ทางสายยาง/ป้อนทางปาก)',
								'การพลิกตะแคงตัว',
								'การยืดเหยียดข้อและกล้ามเนื้อ',
								'การเคาะปอดและดูดเสมหะ',
								'การดูแลช่วยเหลือในกิจวัตรประจำวัน',
								'การบริการในภาพรวม',
							];
							$rows = [];
							foreach ($list as $item) {
								$rows[] = [
									$item,
									'<div style="white-space: nowrap">'.str_repeat('<i class="icon -material -gray">star_rate</i> ',5).'</div>',
								];
							}
							return $rows;
						})(),
					]), // Table
					new Form([
						'action' => '',
						'children' => [
							'comment' => [
								'type' => 'textarea',
								'class' => '-fill',
								'rows' => 3,
								'placeholder' => 'ข้อเสนอแนะเพื่อการพัฒนาบริการ',
							],
							'save' => [
								'type' => 'button',
								'value' => '<i class="icon -material">done_all</i><span>บันทึกการประเมินผล</span>',
								'container' => '{class: "-sg-text-right"}',
							],
						], // children
					]), // Form
				], // children
			]), // Column
		]);
	}
}
?>