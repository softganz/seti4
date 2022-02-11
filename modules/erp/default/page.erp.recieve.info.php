<?php
/**
* ERP :: Recieve Information
* Created 2021-12-01
* Modify  2021-12-01
*
* @param Object $orgInfo
* @return Widget
*
* @usage erp/{id}/recieve.info/{rcvId}
*/

class ErpRecieveInfo extends Page {
	var $orgId;
	var $orgInfo;

	function __construct($orgInfo = NULL, $rcvId) {
		$this->rcvId = $rcvId;
		$this->orgId = $orgInfo->orgId;
		$this->orgInfo = $orgInfo;
	}

	function build() {
		// if (!($rcvId = $rcvInfo->rcvid)) return message('error', 'PROCESS ERROR:NO RECIEVE');

		$formOptions = SG\json_decode('
		{
			page1 : {
				title: "ต้นฉบับใบเสร็จรับเงิน/ใบกำกับภาษี<span>(เอกสารออกเป็นชุด)</span>",
				for: "(สำหรับลูกค้า)"
			},
			page2 : {
				title: "สำเนาใบกำกับภาษี<span>(เอกสารออกเป็นชุด)</span>",
				for: "(แผนกบัญชี 1)"
			},
			page3 : {
				title: "สำเนาใบเสร็จรับเงิน/ใบกำกับภาษี<span>(เอกสารออกเป็นชุด)",
				for: "(แผนกบัญชี 2)"
			},
			page4 : {
				title: "ใบเสร็จรับเงิน<span>(เอกสารออกเป็นชุด)</span>",
				for: "(แผนกบัญชี 3)"
			},
			page5 : {
				title: "สำเนาใบเสร็จรับเงิน/ใบกำกับภาษี<span>(เอกสารออกเป็นชุด)</span>",
				for: "(แผนกบัญชี 4)"
			}
		}');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Recieve Information no.'.$this->rcvId,
			]),
			'body' => new Column([
				'children' => [
					$this->control(),
					new Column([
						'children' => array_map(
							function ($pageConfig) {
								return $this->view($shopInfo,$rcvInfo, $pageConfig);
							},
							(Array) $formOptions
						), // children
					]),
					$this->script(),
				], // children
			]), // Widget
		]);
	}

	function control() {
		return 'CONTROL';
	}

	function view($shopInfo,$rcvInfo, $pageConfig) {
		return new Column([
			'class' => 'erp-bill -recieve',
			'children' => [
				// Header
				new Row([
					'class' => '-header',
					'children' => [
						'<address><b>'.$this->orgInfo->name.'</b><br />'.$this->orgInfo->info->address.'<br />เลขประจำตัวผู้เสียภาษี '.$this->orgInfo->info->taxId.'<br />โทร. '.$this->orgInfo->info->phone.'</address>',
							'<h3 class="title">'.$pageConfig->title.'</h3>',
					], // children
				]), // Row
				// new DebugMsg($this->orgInfo, '$this->orgInfo'),
				// Bill Information
				new Row([
					'class' => '-billheader',
					'children' => [
						'<div class="-customer -flex">'
						. '<div class="clear"><b class="label">นามผู้ซื้อ </b><span class="billvalue">'.$rcvInfo->rcvcustname.'</span></div>'
						. '<div class="clear"><b class="label">ที่อยู่</b><span class="billvalue">'.$rcvInfo->rcvaddr.'</span></div>'
						//$ret .= '<b>โทรศัพท์</b> '.$rcvInfo->rcvphone.'<br />';
						. '<div class="clear">เลขประจำตัวผู้เสียภาษี '.$rcvInfo->rcvtaxid
						. ($rcvInfo->rcvbranch ? ' สาขาลำดับที่ '.$rcvInfo->rcvbranch : '')
						. '</div>'
						. '</div>',

						'<div class="-billid">'
						. '<div class="clear"><b class="label">เลขที่เอกสาร </b><span class="billvalue">'.$rcvInfo->rcvno.'</span></div>'
						. '<div class="clear"><b class="label">วันที่ </b><span class="billvalue">'.sg_date($rcvInfo->rcvdate,'d/m/ปปปป').'</span></div>'
						. '<div class="clear"><b class="label">อัตราภาษีร้อยละ </b><span class="billvalue">'.$rcvInfo->vatrate.'</span></div>'
						. '</div>'
					], // children
				]), // Row

				// Footer
				new Row([
					'class' => '-billsign',
					'children' => [
						'<div class="billsign -sg-text-center"><br />ผู้อนุมัติ .........................<br />(<span class="sign"></span>)</div>',
						'<div class="billsign -sg-text-center"><br />ผู้รับสินค้า .........................<br />(<span class="sign"></span>)</div>',
						'<div class="col -md-4 billsign -sg-text-center"><br />ผู้รับเงิน .........................<br />(<span class="sign"></span>)</div>',
					], // children
				]), // Row

				new Container([
					'class' => 'billfooter -sg-text-right',
					'child' => $pageConfig->for,
				]), // Container
				'<hr class="pagebreak" />',

			], // children
		]);
	}

	function script() {
		return '<style type="text/css">
		.erp-bill {box-shadow: 0 0 0 1px #eee; margin: 16px; padding: 8px;}
		.erp-bill .-header {justify-content: space-between; margin-bottom: 32px;}
		.erp-bill .-header .title {font-size: 1.1em; text-align: center;}
		.erp-bill .-header .title>span {display: block;}

		.erp-bill .-billheader {justify-content: space-between; margin-bottom: 32px;}

		.erp-bill .-billsign {justify-content: space-between; margin-bottom: 32px;}

		.garage-recieve .col {}
		.garage-recieve .row.-header {}
			.garage-recieve .row.-header .col {}
			.garage-recieve .row.-billheader {margin:0 0 16px 0; border:2px #000 solid;}
			.garage-recieve .row.-billheader .col {padding: 8px;}
			.garage-recieve address {padding:0;font-style:normal; font-size:0.9em;}
		.garage-recieve .title {text-align: right;}
		.garage-recieve .title>span {display: block; font-size:0.8em;}
		.garage-recieve .-customer {padding: 8px; border-right:2px #333 solid;}
		.garage-recieve .-customer .label {display:block; width:5em; float:left; clear:both;}
		.garage-recieve .-customer .billvalue {display:block; margin-left:5em; }
		.garage-recieve .-billid {padding: 8px;}
		.garage-recieve .-billid .label {display: inline-block; width:8em;}
		.garage-recieve .-billsign {text-align:center;}
		.garage-recieve .sign {display:inline-block;width:80%;}
		.garage-recieve .billfooter {text-align: right;}
		.garage-recieve.-main address {display: none;}
		.garage-recieve .container .item {border:2px #333 solid;}
		.garage-recieve .container .item th {border-right:2px #333 solid;border-bottom:2px #333 solid;}
		.garage-recieve .container .item th:last-child {border-right:none;}
		.garage-recieve .container .item td {padding:8px; border:none; border-right:2px #333 solid;}
		.garage-recieve .container .item td:last-child {border-right:none;}
		.garage-recieve .container .item tr.-total td {border-top: 2px #333 solid;}
		.garage-recieve .row.-billsign {display:flex;}
		.garage-recieve .row.-billsign .billsign {width:28%;margin:8px 8px 0 0; padding:16px; border:2px #333 solid;}
		.garage-recieve .row.-billsign .billsign:last-child {margin-right:0;}
		.garage-recieve .garage-recieve-tran .detail {text-align: left;}
		.garage-recieve .garage-recieve-tran .detail span {display: block;}

		.garage-recieve .-forprint {margin:32px 0;padding:16px;}
		.garage-recieve.-info .row.-header {height:2.5cm; margin:1cm 0 0.5em 0;}
		.garage-recieve.-info .row.-billheader {height:3.5cm; margin:0 0 0.5em 0;}
		.garage-recieve.-info .container {height:25.0cm;}
		.garage-recieve.-info .container .item {height:14.1cm;margin:0;}
		.garage-recieve.-info .container .item tr.-empty td {height:100%;}
		.garage-recieve.-info .row.-billsign {width:100%; position:absolute; bottom:0.7cm;}
		.garage-recieve.-info .billfooter {position: absolute; bottom:0; right:0;}


		@media print {
			.garage-recieve .col {}
			.garage-recieve .-forprint {height:100%;margin:0;padding:0;}
			.garage-recieve .-customer .label {width:2.8em;}
			.garage-recieve .-customer .billvalue {margin-left:2.8em; }
			.garage-recieve .-billid .label {width:5.5em;}
			.garage-recieve .title {font-size:1.0em;}
			.pagebreak {page-break-after: always;}
		}
		</style>';

		$ret .= '<script type="text/javascript">
		var urlRefresh = "'.url('garage/recieve/'.$rcvInfo->rcvid.'/view/gettran').'"
		function refreshTran() {
			//console.log(urlRefresh)
			$.get(urlRefresh,function(html) {
				$("#garage-recieve-tran").replaceWith(html)
			});
		}

		$("body").on("click","#garage-insurerqt td:not(input[type=checkbox])",function() {
			var urlAddQt="'.url('garage/info/'.$rcvInfo->rcvid.'/recieve.addqt/').'"
			var $container=$(this).closest("tr")
			var $checkBox=$container.find("input")
			var qtid=$checkBox.val()
			$checkBox.prop("checked", !$checkBox.prop("checked"))
			$.get(urlAddQt+qtid,function(html){
				notify(html, 3000)
				$container.remove()
				refreshTran()
			});
		});
		</script>';
	}
}
?>