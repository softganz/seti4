<?php
/**
* iMed Care :: Request Paid
* Created 2021-09-08
* Modify  2021-08-08
*
* @param Object $requestInfo
* @return Widget
*
* @usage imed/care/req/{keyId}/paid
*/

$debug = true;

class ImedCareReqPaid extends Page {
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
				'title' => 'ชำระค่าบริการ',
				'boxHeader' => true,
				'boxHeaderBack' => _HEADER_BACK,
				'navigator' => [
				],
			]),
			'body' => new Column([
				'children' => [
					new Card([
						'child' => new Row([
							'children' => [
								'<img src="//communeinfo.com/upload/photo/logo-ktb.jpg" width="120" />',
								'<p style="padding: 4px 16px; margin: 0;">ธ.กรุงไทย สาขา...<br />เลขที่บัญชี 000-0-00000-0</p>',
							]
						]),
					]),
					new Card([
						'child' => new Row([
							'children' => [
								'<img src="//communeinfo.com/upload/photo/logo-scb.png" width="120" />',
								'<p  style="padding: 0 16px; margin: 0;">ธ.ไทยพาณิชย์ สาขา...<br />เลขที่บัญชี 000-0-00000-0</p>',
							]
						]),
					]),
					new Container([
						'class' => '-sg-paddingmore',
						'child' => '<a class="btn -primary -fill" href="javascript:void(0)">แจ้งการจ่ายชำระเงิน</a>',
					]),
				], // children
			]), // body
		]);
	}
}
?>