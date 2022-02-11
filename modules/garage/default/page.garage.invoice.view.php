<?php
/**
* View Garage Invoice
* Created 2019-10-13
* Modify  2019-10-14
*
* @param Object $self
* @param Int $invoiceId
* @return String
*/

$debug = true;

function garage_invoice_view($self, $invoiceId = NULL, $action = NULL, $trid = NULL) {
	$shopInfo = R::Model('garage.get.shop');

	if ($invoiceId) {
		$invoiceInfo = R::Model('garage.invoice.get', $shopInfo->shopid, $invoiceId, '{debug:false}');
	}

	new Toolbar($self,'ใบแจ้งหนี้'.($invoiceInfo->docno ? ' - '.$invoiceInfo->docno:''),'finance',$invoiceInfo);

	if (empty($invoiceId)) return '<p class="notify">ไม่มีรายการที่ระบุ</p>';


	$ret .= '<div id="garage-form" class="garage-form -main -no-print" data-url="'.url('garage/invoice/'.$invoiceInfo->invoiceid).'">';
	$ret .= __garage_recieve_info($shopInfo,$invoiceInfo);

	$ret .= '<div id="garage-form-tran">';
	$ret .= __garage_recieve_view_tran($shopInfo,$invoiceInfo);
	$ret .= '</div>';

	$ret .= '</div><!-- garage-form -->';



	if ($invoiceInfo->docstatus == _CANCEL) {
		$ret .= '<p class="notify -no-print">ใบเสร็จรับเงินถูกยกเลิก</p>';
		return $ret;
	}



	$ret .= '<p class="notify -no-print">สำหรับพิมพ์สู่เครื่องพิมพ์</p>';

	$formOptions = '
	{
		page1 : {
			title: "ใบแจ้งหนี้",
			for: "(สำหรับประกัน)"
		},
		page2 : {
			title: "สำเนาใบแจ้งหนี้",
			for: "(แผนกบัญชี 1)"
		}
	}';


	foreach (sg_json_decode($formOptions) as $key => $page) {
		$ret .= '<div id="garage-form-info" class="garage-form -info">'._NL;
		$ret .= '<div class="-forprint">'
			. __garage_recieve_info($shopInfo, $invoiceInfo, $page->title, $page->for)
			. '</div>';
		$ret .= '</div>';
	}


	$ret .= '<style type="text/css">
		.garage-form .col {}
		.garage-form .row.-header {}
		.garage-form .row.-header .col {}
		.garage-form .row.-billheader {margin:0 0 16px 0;border:2px #000 solid;}
		.garage-form address {padding:0;font-style:normal; font-size:0.9em;}
		.garage-form .row.-flex>.col {padding: 8px;}
		.garage-form .title {text-align: right;}
		.garage-form .title>span {display: block; font-size:0.8em;}
		.garage-form .-customer {padding: 8px; border-right:2px #333 solid;}
		.garage-form .-customer .label {display:block; width:5em; float:left; clear:both;}
		.garage-form .-customer .billvalue {display:block; margin-left:5em; }
		.garage-form .-billid {padding: 8px;}
		.garage-form .-billid .label {display: inline-block; width:8em;}
		.garage-form .-billsign {text-align:center;}
		.garage-form .sign  {display:inline-block;width:80%;}
		.garage-form .billfooter {}
		.garage-form.-main address {display: none;}
		.garage-form .container .item {border:2px #333 solid;}
		.garage-form .container .item th {border-right:2px #333 solid;border-bottom:2px #333 solid;}
		.garage-form .container .item th:last-child {border-right:none;}
		.garage-form .container .item td {padding:8px; border:none; border-right:2px #333 solid;}
		.garage-form .container .item td:last-child {border-right:none;}
		.garage-form .container .item tr.-total td {border-top: 2px #333 solid;}
		.garage-form .row.-billsign {display:flex;}
		.garage-form .row.-billsign .billsign {width:28%;margin:8px 8px 0 0; padding:16px; border:2px #333 solid;}
		.garage-form .row.-billsign .billsign:last-child {margin-right:0;}
		.garage-form .garage-form-tran .detail {text-align: left;}
		.garage-form .garage-form-tran .detail span {display: block;}
		
		.garage-form .-forprint {margin: 32px 0;padding: 16px;}
		.garage-form.-info .container {height: 26.0cm;}
		.garage-form.-info .row.-header {height: 2.5cm; margin: 0 0 0.5em 0; padding: 0;}
		.garage-form.-info .row.-billheader {height: 3.5cm; margin: 0 0 0.5em 0; padding: 0;}
		.garage-form.-info .container .item {height: 15.0cm; margin: 0;}
		.garage-form.-info .container .item tr.-empty td {height: 100%;}
		.garage-form.-info .billfooter {margin: 0; padding: 16px; position: absolute; bottom: 0; left: 0; right: 0;}


		@media print {
			.garage-form .col {}
			.garage-form .-forprint {height: 100%; margin: 0; padding: 0;}
			.garage-form .-customer .label {width:2.8em;}
			.garage-form .-customer .billvalue {margin-left:2.8em; }
			.garage-form .-billid .label {width:5.5em;}
			.garage-form .title {font-size:1.0em;}
			.garage-form.-info .container {height: 26.0cm;}
			.garage-form.-info .billfooter {padding: 0;}
			.pagebreak {page-break-after: always; height: 1px;}
		}
		</style>

		<script type="text/javascript">
		var $container = $("#garage-form")
		var urlRefresh = $container.data("url")
		function refreshTran() {
			$.post(urlRefresh,function(html) {
				$container.html(html);
			});
		}

		$("body").on("click","#garage-insurerqt td:not(input[type=checkbox])",function() {
			var urlAddQt = "'.url('garage/job/*/invoice.qt.add/'.$invoiceInfo->invoiceid,array('qt'=>'')).'";
			var $container = $(this).closest("tr");
			var $checkBox = $container.find("input");
			var qtid = $checkBox.val();
			$checkBox.prop("checked", !$checkBox.prop("checked"));
			$.post(urlAddQt+qtid,function(html){
				$container.remove();
				refreshTran();
			});
		});
		</script>';

	//$ret.=print_o($invoiceInfo,'$invoiceInfo');

	return $ret;
}


function __garage_recieve_info($shopInfo,$invoiceInfo,$billheader=NULL,$billfooter=NULL) {
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
	$ret .= '<div class="clear"><b class="label">นามผู้ซื้อ </b><span class="billvalue">'.$invoiceInfo->custname.'</span></div>';
	$ret .= '<div class="clear"><b class="label">ที่อยู่</b><span class="billvalue">'.$invoiceInfo->address.'</span></div>';
	$ret .= '<div class="clear">เลขประจำตัวผู้เสียภาษี '.$invoiceInfo->taxid;
	if ($invoiceInfo->branch) $ret .= ' สาขาลำดับที่ '.$invoiceInfo->branch;
	$ret .= '</div>';
	$ret .= '</div><!-- col -->';

	$ret .= '<div class="col -md-4 -billid">';
	$ret .= '<div class="clear"><b class="label">เลขที่เอกสาร </b><span class="billvalue">'.$invoiceInfo->docno.'</span></div>';
	$ret .= '<div class="clear"><b class="label">วันที่ </b><span class="billvalue">'.sg_date($invoiceInfo->docdate,'d/m/ปปปป').'</span></div>';
	$ret .= '<div class="clear"><b class="label">อัตราภาษีร้อยละ </b><span class="billvalue">'.$invoiceInfo->vatrate.'</span></div>';
	$ret .= '</div><!-- col -->';

	$ret .= '</div><!-- row -->';

	if (empty($billheader)) {
		$ret .= '<br clear="all" />';
		$ret .= '</div><!-- container -->';
		return $ret;
	}

	$ret.=__garage_recieve_view_rcvtr($shopInfo,$invoiceInfo);

	$ret .= '<div class="billfooter">';
	$ret .= '<p class="-sg-text-center">ทางบริษัทจะออกใบเสร็จ/ใบกำกับภาษีเมื่อได้รับชำระหนี้เป็นที่เรียบร้อยแล้ว<br /></p>';

	$ret .= '<div class="-sg-text-center" style="width: 15em; margin: 0 auto;">';
	$ret .= 'ผู้แจ้งยอดหนี้ . . . . . . . . . . . . . . . . . . . . .<br />(<span class="sign"></span>)';
	$ret .= '</div>';

	$ret .= '<div class="-sg-text-right">'.$billfooter.'</div>';
	$ret .= '</div><!-- billfooter -->'._NL;

	$ret .= '</div><!-- container -->';

	$ret .= '<hr class="pagebreak" />'._NL;

	return $ret;
}

function __garage_recieve_view_tran($shopInfo,$invoiceInfo) {
	$ret = '';
	$tables = new Table();
	$tables->addClass('-center');
	$tables->thead = array(
		'เลขเคลม',
		'เลขใบสั่งซ่อม',
		'ยี่ห้อรถ',
		'เลขทะเบียนรถ',
		'รวมเงิน',
		'icons -c1 -hover-parent' => $invoiceInfo->docstatus > 0 ? '<a class="sg-action" href="'.url('garage/api/insurerqt',array('id'=>$invoiceInfo->insurerid,'cond'=>'noinvoice')).'" data-rel="box"><i class="icon -add"></i></a>' : ''
	);

	foreach ($invoiceInfo->trans as $rs) {
		$isRecieved = $rs->docdate != '';

		$ui = new Ui();
		if ($rs->billid) {
			$ui->add('<a href="'.url('garage/billing/view/'.$rs->billid).'"><i class="icon -material">done</i></a>');
		}
		if ($rs->rcvid) {
			$ui->add('<a href="'.url('garage/recieve/view/'.$rs->rcvid).'"><i class="icon -material">attach_money</i></a>');
		}

		// Quation can delete even though it has revieved.
		$ui->add('<a class="sg-action -no-print" href="'.url('garage/job/'.$rs->tpid.'/invoice.qt.remove/'.$rs->qtid).'" data-rel="notify" data-done="load:#main" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a>');
		
		$menu = '<nav class="nav -icons -hover -no-print">'.($ui->count() ? $ui->build() : '').'</nav>';

		$tables->rows[] = array(
			$rs->insuclaimcode,
			$rs->jobno,
			$rs->brandid,
			$rs->plate,
			number_format($rs->replyprice,2),
			$menu,
		);
	}
	$tables->tfoot[]=array('','','','รวมเงิน',number_format($invoiceInfo->rcvTotal,2),'');
	$ret.=$tables->build();
	return $ret;
}



function __garage_recieve_view_rcvtr($shopInfo,$invoiceInfo) {
	$vatRate=$invoiceInfo->vatrate;

	$tables = new Table();
	$tables->addClass('garage-form-tran -center');
	$tables->thead=array('center -no'=>'ลำดับที่<br />Item','รายการ<br />Description','amt -qty'=>'จำนวน<br />Quantity','money -price'=>'ราคาต่อหน่วย<br />Unit Price','money -total'=>'จำนวนเงิน<br />Amount');

	$no=$subTotal=$vatSubTotal=0;
	$vatCase=1;

	foreach ($invoiceInfo->trans as $rs) {
		$qty=1;
		switch ($vatCase) {
			case 2:
				// Case 2
				$unitVat=round($rs->replyprice*$vatRate/(100+$vatRate),2);
				$unitPrice=$rs->replyprice-$unitVat;
				break;
			
			default:
				// Case 1 and Other
				$unitPrice=round($rs->replyprice/(1+$vatRate/100),2);
				$unitVat=$rs->replyprice-$unitPrice;
				break;
		}

		$itemTotal=$unitPrice*$qty;
		$subTotal+=$itemTotal;
		$vatSubTotal+=$unitVat;

		if ($invoiceInfo->showsingle) {
			$desc.='<span class="-claimcode">'.$rs->insuclaimcode.'</span>';
		} else {
			$tables->rows[]=array(
				$invoiceInfo->showno ? ++$no : '<td></td>',
				'<div class="detail">ค่าซ่อมรถยนต์ <span class="-plate">'.$rs->plate.'</span> <span class="-brand">'.$rs->brandid.'</span> '
				. '<span class="-claimcode">'.$rs->insuclaimcode.'</span>'
				. ($invoiceInfo->showinsuno && $rs->insuno ? '<span class="-insuno">'.$rs->insuno.'</span>' : '')
				. '</div>',
				number_format(1.00,2),
				number_format($unitPrice,2),
				number_format($itemTotal,2),
			);
		}
	}
	if ($invoiceInfo->showsingle) {
		$tables->rows[]=array('<td></td>','<div class="detail -showsingle">ค่าซ่อมรถยนต์ '.$desc.'</div>',number_format(1.00,2),number_format($subTotal,2),number_format($subTotal,2));
	}

	// Show empty row
	$tables->rows[]=array('<td></td>','','','','','config'=>array('class'=>'-empty'));

	//$vatSubTotal=round(556.074766,4);

	// Show total
	$vatTotal=round($subTotal*$vatRate/100,2);
	$total=round($subTotal+$vatTotal,2);

	$tables->rows[]=array('<td colspan="3">'.($invoiceInfo->rcvremark ? 'หมายเหตุ '.$invoiceInfo->rcvremark:'').'</td>','รวมราคา',number_format($subTotal,2),'config'=>array('class'=>'-total'));
	$tables->rows[]=array('<td colspan="3"></td>','ภาษีมูลค่าเพิ่ม '.$invoiceInfo->vatrate.'%',number_format($vatTotal,2));
	$tables->rows[]=array('<td colspan="3" style="text-align:center;">('.sg_money2bath($total,2).')</td>','จำนวนเงินรวม',number_format($total,2));
	$ret.=$tables->build();
	//$ret.=print_o($invoiceInfo,'$invoiceInfo');

	return $ret;
}


function __garage_recieve_view_form($shopInfo,$data) {
	$ret .= '<header class="header -box"><nav class="nav -back"><a class="sg-action" href="javascript:void(0)" data-rel="back"><i class="icon -back"></i></a></nav><h3 class="title">แก้ไขใบเสร็จรับเงิน</h3></header>';
	$form=new Form('data',url('garage/recieve/edit/'.$data->rcvid.'/update'));
	$form->addField('rcvdate',array('label'=>'วันที่','type'=>'text','class'=>'-fill sg-datepicker','value'=>sg_date($data->rcvdate,'d/m/Y')));
	$form->addField('rcvcustname',array('label'=>'นามผู้ซื้อ','type'=>'text','class'=>'-fill','value'=>$data->rcvcustname));
	$form->addField('rcvaddr',array('label'=>'ที่อยู่','type'=>'textarea','class'=>'-fill','value'=>$data->rcvaddr,'rows'=>2));
	$form->addField('rcvphone',array('label'=>'โทรศัพท์','type'=>'text','class'=>'-fill','value'=>$data->rcvphone));
	$form->addField('rcvtaxid',array('label'=>'เลขประจำตัวผู้เสียภาษี','type'=>'text','class'=>'-fill','value'=>$data->rcvtaxid,'maxlength'=>13));
	$form->addField('rcvbranch',array('label'=>'สาขาลำดับที่','type'=>'text','class'=>'-fill','value'=>$data->rcvbranch));
	$form->addField('vatrate',array('label'=>'อัตราภาษีร้อยละ','type'=>'text','class'=>'-fill','value'=>$data->vatrate));
	$form->addField('rcvremark',array('label'=>'หมายเหตุ','type'=>'textarea','class'=>'-fill','value'=>$data->rcvremark,'rows'=>3));
	$form->addField('showno',array('type'=>'checkbox','value'=>$data->showno,'options'=>array('1'=>'แสดงลำดับที่')));
	$form->addField('showsingle',array('type'=>'checkbox','value'=>$data->showsingle,'options'=>array('1'=>'รวมรายการ')));
	$form->addField('showinsuno',array('type'=>'checkbox','value'=>$data->showinsuno,'options'=>array('1'=>'แสดงเลขกรมธรรม์')));
	$form->addField(
		'save',
		array(
			'type'=>'button',
			'value'=>'<i class="icon -save -white"></i><span>บันทึก</span>',
			'pretext' => '<a class="btn -link -cancel" href="'.url('garage/recieve/view/'.$data->rcvid).'"><i class="icon -cancel -gray"></i><span>{tr:CANCEL}</span></a>',
			'container' => array('class'=>'-sg-text-right'),
		)
	);

	$ret .= $form->build();

	$ret .= '<p>หมายเหตุ : การแก้ไขข้อมูลบริษัทประกันจะไม่มีผลต่อข้อมูลในใบเสร็จรับเงินที่สร้างไว้แล้ว</p>';
	//$ret.=print_o($data,'$data');
	return $ret;
}
?>