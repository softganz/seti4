<?php
/**
* View Garage Invoice Information
* Created 2019-10-13
* Modify  2019-10-13
*
* @param Object $self
* @param Int $invoiceId
* @param String $action
* @return String
*/

$debug = true;

function garage_invoice_view($self,$invoiceId,$action=NULL) {
	$shopInfo=R::Model('garage.get.shop');

	if ($invoiceId) {
		$invoiceInfo = R::Model('garage.invoice.get',$shopInfo->shopid,$invoiceId,'{debug:false}');
	}

	new Toolbar($self,'ใบแจ้งหนี้'.($invoiceInfo?' - '.$invoiceInfo->invoiceno:''),'finance',$invoiceInfo);

	if (empty($invoiceInfo->invoiceid)) return '<p class="notify">ไม่มีรายการที่ระบุ</p>';


	switch ($action) {
		case 'gettran' :
			$ret = __garage_invoice_view_tran($shopInfo, $invoiceInfo);
			break;

		default:
			$ret .= '<div class="garage-bill -forprint">'._NL;
			$ret .= '<div id="garage-bill-info" class="garage-bill -info">'._NL;
			$ret .= __garage_invoice_view_info($shopInfo, $invoiceInfo);
			//$ret .= print_o($invoiceInfo, '$invoiceInfo');
			$ret .= '</div>'._NL;
			break;
	}

	return $ret;
}

function __garage_invoice_view_info($shopInfo, $invoiceInfo) {
	$ret = '<address>'.$shopInfo->shopname.'<br />'.$shopInfo->shopaddr.' '.$shopInfo->shopzipcode.' โทร. '.$shopInfo->shopphone.'</address>'._NL;
	$ret .= '<h3>ใบแจ้งหนี้'.($invoiceInfo->invoicestatus<=0?' ( ยกเลิก )':'').'</h3>'._NL;
	$ret .= '<p align="right"><b>เลขที่เอกสาร </b>'.$invoiceInfo->invoiceno.'<br /><b>วันที่ </b>'.sg_date($invoiceInfo->invoicedate,'ว ดดด ปปปป').'</p>'._NL;
	$ret .= '<p><b>แจ้งหนี้ &nbsp;&nbsp;</b>'.$invoiceInfo->insurername.'</p>'._NL;
	$ret .= '<p class="-indent">'.$invoiceInfo->insureraddr.'</p>'._NL;

	$ret .= '<div id="garage-invoice-tran" data-url="'.url('garage/invoice/'.$invoiceInfo->invoiceid.'/gettran').'">'.__garage_invoice_view_tran($shopInfo,$invoiceInfo).'</div>';

	$ret .= '<p class="-sg-text-center" style="margin-top: 2em;">ทางบริษัทจะออกใบเสร็จ / ใบกำกับภาษี เมื่อได้รับชำระหนี้เป็นที่เรียบร้อยแล้ว<br /><br /><br /><br />. . . . . . . . . . . . . . . . . . . . . . . . . . . . . .<br /><br />';
	$ret .= '(<span class="sign"></span>)<br /><br />ผู้แจ้งยอดหนี้</p>';

	$ret .= '<br clear="all" />';


	$ret .= '<style type="text/css">
	.shopname {width:40%; float:right; text-align:center;}
	.billsign {width:40%; float:left; text-align:center; margin:40px 0 40px 0;}
	.billsign.-b2 {float:right;}
	.sign {display:inline-block;width:20em;}
	</style>';
	$ret .= '<script type="text/javascript">
	var $container = $("#garage-invoice-tran")
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
	return $ret;
}

function __garage_invoice_view_tran($shopInfo,$invoiceInfo) {
	$ret='';
	$tables = new Table();
	$tables->addClass('-center');
	$tables->thead = array(
		'เลขเคลม',
		'เลข Job',
		'ยี่ห้อรถ',
		'เลขทะเบียนรถ',
		'รวมเงิน',
		'icons -c1 -hover-parent' => $invoiceInfo->invoicestatus > 0 ? '<a class="sg-action" href="'.url('garage/api/insurerqt',array('id'=>$invoiceInfo->insurerid,'cond'=>'noinvoice')).'" data-rel="box"><i class="icon -add"></i></a>' : ''
	);

	foreach ($invoiceInfo->trans as $rs) {
		$ui = new Ui();
		if ($rs->billid) {
			$ui->add('<a href="'.url('garage/billing/view/'.$rs->billid).'"><i class="icon -material">done</i></a>');
		}
		if ($rs->rcvid) {
			$ui->add('<a href="'.url('garage/recieve/view/'.$rs->rcvid).'"><i class="icon -material">attach_money</i></a>');
		}

		// Quation can delete even though it has revieved.
		$ui->add('<a class="sg-action -no-print" href="'.url('garage/job/'.$rs->tpid.'/invoice.qt.remove/'.$rs->qtid).'" data-rel="notify" data-done="load:#garage-invoice-tran" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a>');
		
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
	$tables->tfoot[]=array('','','','รวมเงิน',number_format($invoiceInfo->total,2),'');
	$ret.=$tables->build();
	return $ret;
}
?>