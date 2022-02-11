<?php
/**
* Garage :: Show Recieve Information
* Created 2020-02-08
* Modify  2020-02-18
*
* @param Object $self
* @param Int $rcvId
* @param String $action
* @return String
*/

$debug = true;

function garage_recieve_view($self, $rcvInfo = NULL, $action = NULL) {
	if (!($rcvId = $rcvInfo->rcvid)) return message('error', 'PROCESS ERROR:NO RECIEVE');

	$shopInfo = $rcvInfo->shopInfo;

	new Toolbar($self,'ใบเสร็จรับเงิน'.($rcvInfo?' - '.$rcvInfo->rcvno:''),'finance',$rcvInfo);

	$formOptions = '
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
	}';

	switch ($action) {
		case 'gettran' :
			$ret .= __garage_recieve_view_tran($shopInfo,$rcvInfo);
			return $ret;
			break;

		default:
			$ret .= '<div class="garage-recieve -main -no-print">';
			$ret .= __garage_recieve_info($shopInfo,$rcvInfo);
			$ret .= __garage_recieve_view_tran($shopInfo,$rcvInfo);
			$ret .= '</div>';

			if ($rcvInfo->rcvstatus == _CANCEL) {
				$ret .= '<p class="notify -no-print">ใบเสร็จรับเงินถูกยกเลิก</p>';
				return $ret;
			}

			$ret .= '<p class="notify -no-print">สำหรับพิมพ์สู่เครื่องพิมพ์</p>';

			$ret .= '<div id="garage-recieve-info" class="garage-recieve -info" data-url="'.url('garage/recieve/'.$rcvId.'/view/').'">'._NL;
			foreach (sg_json_decode($formOptions) as $key => $value) {
				$ret .= '<div class="'.$key.' -forprint">'
					. __garage_recieve_info($shopInfo, $rcvInfo, $value->title, $value->for)
					. '</div>';
			}
			$ret .= '</div><!-- garage-recieve-info -->';
			break;
	}


	$ret .= '<style type="text/css">
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

	//$ret.=print_o($rcvInfo,'$rcvInfo');

	return $ret;
}


function __garage_recieve_view_tran($shopInfo,$rcvInfo) {
	$ret = '';
	$tables = new Table();
	$tables->addId('garage-recieve-tran');
	$tables->addClass('-center');
	$tables->thead = array(
		'เลขเคลม',
		'เลขใบสั่งซ่อม',
		'ยี่ห้อรถ',
		'เลขทะเบียนรถ',
		'ราคาต่อหน่วย',
		'ภาษีมูลค่าเพิ่ม',
		'รวมเงิน',
		$rcvInfo->rcvstatus > 0 ? '<a class="sg-action" href="'.url('garage/api/insurerqt',array('id'=>$rcvInfo->insurerid,'cond'=>'norcv')).'" data-rel="box"><i class="icon -add"></i></a>' : ''
	);

	foreach ($rcvInfo->qt as $rs) {
		$isRecieved = $rs->rcvmdate != '';

		$menu = '<nav class="nav -icons">'
			. '<a class="sg-action" href="'.url('garage/recieve/'.$rcvInfo->rcvid.'/form.tran/'.$rs->qtid).'" data-rel="box" data-width="480"><i class="icon -material">edit</i></a> '
			. '<a class="sg-action" href="'.url('garage/job/'.$rs->tpid.'/rcvmoney/'.$rs->qtid).'" data-rel="box" data-width="640"><i class="icon -material '.($isRecieved ? '-green' : '-gray').'">'.($isRecieved ? 'attach_money' : 'money_off').'</i></a> '
			. '<a class="sg-action -no-print" href="'.url('garage/info/'.$rcvInfo->rcvid.'/recieve.delqt/'.$rs->qtid).'" data-rel="notify" data-done="load" data-title="ลบรายการใบเสนอราคา" data-confirm="ต้องการลบรายการใบเสนอราคานี้ออกจากใบเสร็จรับเงิน กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a>';
		$itemTotalError = $rs->unitprice + $rs->vat != $rs->replyprice;
		$tables->rows[]=array(
			$rs->insuclaimcode,
			$rs->jobno,
			$rs->brandid,
			$rs->plate,
			number_format($rs->unitprice,2),
			number_format($rs->vat,2),
			($itemTotalError ? '<span style="color:red">' : '').number_format($rs->replyprice,2).($itemTotalError ? '</span>' : ''),
			$menu,
		);
	}

	$tables->tfoot[] = array('','','','รวมเงิน',
		number_format($rcvInfo->subtotal,2),
		number_format($rcvInfo->vattotal,2),
		number_format($rcvInfo->total,2),
		''
	);

	$ret .= $tables->build();

	//$ret.=print_o($rcvInfo,'$rcvInfo');

	return $ret;
}

function __garage_recieve_info($shopInfo,$rcvInfo,$billheader=NULL,$billfooter=NULL) {
	$ret .= '<div class="container">';

	$ret .= '<div class="row -header">';
	$ret .= '<div class="col -md-6">';
	$ret .= '<address><b>'.$shopInfo->shopname.'</b><br />'.$shopInfo->shopaddr.' '.$shopInfo->shopzipcode.'<br />เลขประจำตัวผู้เสียภาษี '.$shopInfo->taxid.'<br />โทร. '.$shopInfo->shopphone.'</address>'._NL;
	$ret .= '</div><!-- col -->';
	$ret .= '<div class="col -md-6">';
	$ret .= '<h3 class="title">'.$billheader.'</h3>'._NL;
	$ret .= '</div><!-- col -->';
	$ret .= '</div><!-- row -->';

	$ret .= '<div class="row -billheader -flex clear">';
	$ret .= '<div class="col -md-8 -customer -flex">';
	$ret .= '<div class="clear"><b class="label">นามผู้ซื้อ </b><span class="billvalue">'.$rcvInfo->rcvcustname.'</span></div>';
	$ret .= '<div class="clear"><b class="label">ที่อยู่</b><span class="billvalue">'.$rcvInfo->rcvaddr.'</span></div>';
	//$ret .= '<b>โทรศัพท์</b> '.$rcvInfo->rcvphone.'<br />';
	$ret .= '<div class="clear">เลขประจำตัวผู้เสียภาษี '.$rcvInfo->rcvtaxid;
	if ($rcvInfo->rcvbranch) $ret .= ' สาขาลำดับที่ '.$rcvInfo->rcvbranch;
	$ret .= '</div>';
	$ret .= '</div><!-- col -->';
	$ret .= '<div class="col -md-4 -billid">';
	$ret .= '<div class="clear"><b class="label">เลขที่เอกสาร </b><span class="billvalue">'.$rcvInfo->rcvno.'</span></div>';
	$ret .= '<div class="clear"><b class="label">วันที่ </b><span class="billvalue">'.sg_date($rcvInfo->rcvdate,'d/m/ปปปป').'</span></div>';
	$ret .= '<div class="clear"><b class="label">อัตราภาษีร้อยละ </b><span class="billvalue">'.$rcvInfo->vatrate.'</span></div>';
	$ret .= '</div><!-- col -->';
	$ret .= '</div><!-- row -->';

	if (empty($billheader)) {
		$ret .= '<br clear="all" />';
		$ret .= '</div><!-- container -->';

		return $ret;
	}

	$ret .= __garage_recieve_view_rcvtr($shopInfo,$rcvInfo);

	$ret .= '<div class="row -billsign -flex">';
	$ret .= '<div class="col -md-4 billsign -sg-text-center"><br />ผู้อนุมัติ .........................<br />(<span class="sign"></span>)</div>';
	$ret .= '<div class="col -md-4 billsign -sg-text-center"><br />ผู้รับสินค้า .........................<br />(<span class="sign"></span>)</div>';
	$ret .= '<div class="col -md-4 billsign -sg-text-center"><br />ผู้รับเงิน .........................<br />(<span class="sign"></span>)</div>';
	$ret .= '</div><!-- row -->';

	$ret .= '<div class="billfooter">'.$billfooter.'</div>';
	$ret .= '</div><!-- container -->';
	$ret .= '<hr class="pagebreak" />';


	return $ret;
}


function __garage_recieve_view_rcvtr($shopInfo,$rcvInfo) {
	$vatRate = $rcvInfo->vatrate;

	$tables = new Table();
	$tables->addClass('garage-recieve-tran -center');
	$tables->thead = array(
		'center -no' => 'ลำดับที่<br />Item',
		'รายการ<br />Description',
		'amt -qty' => 'จำนวน<br />Quantity',
		'money -price' => 'ราคาต่อหน่วย<br />Unit Price',
		'money -total' => 'จำนวนเงิน<br />Amount'
	);

	$no = 0;
	$vatCase = 1;

	foreach ($rcvInfo->qt as $rs) {

		if ($rcvInfo->showsingle) {
			$desc .= '<span class="-claimcode">'.$rs->insuclaimcode.'</span>';
		} else {
			$tables->rows[] = array(
				$rcvInfo->showno ? ++$no : '',
				'<div class="detail">ค่าซ่อมรถยนต์ <span class="-plate">'.$rs->plate.'</span> <span class="-brand">'.$rs->brandid.'</span> '
				. '<span class="-claimcode">'.$rs->insuclaimcode.'</span>'
				. ($rcvInfo->showinsuno && $rs->insuno ? '<span class="-insuno">'.$rs->insuno.'</span>' : '')
				. '</div>',
				number_format(1.00,2),
				number_format($rs->unitprice,2),
				number_format($rs->unitprice,2),
			);
		}
	}
	if ($rcvInfo->showsingle) {
		$tables->rows[] = array(
			'<td></td>',
			'<div class="detail -showsingle">ค่าซ่อมรถยนต์ '.$desc.'</div>',
			number_format(1.00,2),
			number_format($rcvInfo->subtotal,2),
			number_format($rcvInfo->subtotal,2)
		);
	}

	// Show empty row
	$tables->rows[] = array('<td></td>','','','','','config'=>array('class'=>'-empty'));

	$tables->rows[] = array('<td colspan="3">หมายเหตุ</td>','รวมเงิน',number_format($rcvInfo->subtotal,2),'config'=>array('class'=>'-total'));
	$tables->rows[] = array('<td colspan="3"></td>','ภาษีมูลค่าเพิ่ม',number_format($rcvInfo->vattotal,2));
	$tables->rows[] = array('<td colspan="3" style="text-align:center;">('.sg_money2bath($rcvInfo->total,2).')</td>','รวมเงินทั้งสิ้น',number_format($rcvInfo->total,2));

	$ret .= $tables->build();
	//$ret.=print_o($rcvInfo,'$rcvInfo');

	return $ret;
}


?>