<?php
function garage_billing_view($self, $billid, $action = NULL, $trid = NULL) {
	$shopInfo = R::Model('garage.get.shop');

	if ($billid) {
		$billingInfo = R::Model('garage.billing.get',$shopInfo->shopid,$billid,'{debug:false}');
	}

	new Toolbar($self,'ใบวางบิล'.($billingInfo?' - '.$billingInfo->billno:''),'finance',$billingInfo);

	if (empty($billid)) return '<p class="notify">ไม่มีรายการที่ระบุ</p>';


	switch ($action) {
		case 'gettran' :
			$ret = __garage_billing_view_tran($shopInfo,$billingInfo);
			break;

		default:

			$ret .= '<div id="garage-billing-view" class="garage-bill -billing -forprint">'._NL;
			$ret .= __garage_billing_info($shopInfo,$billingInfo);
			$ret .= '</div>'._NL;
			break;
	}


	//$ret.=print_o($jobInfo,'$jobInfo');
	//$ret.=print_o($billingInfo,'$billingInfo');
	return $ret;
}

function __garage_billing_info($shopInfo,$billingInfo) {
	$ret .= '<section class="-header">';
	$ret .= '<address>'.$shopInfo->shopname.'<br />'.$shopInfo->shopaddr.' '.$shopInfo->shopzipcode.'<br />โทร. '.$shopInfo->shopphone.'</address>'._NL;
	$ret .= '<h3 class="-title">ใบวางบิล'.($billingInfo->billstatus<=0?' ( ยกเลิก )':'').'</h3>'._NL;

	$ret .= '<div class="-info">';
	$ret .= '<p>เรื่อง <b>ขอวางบิลซ่อมรถยนต์</b></p>'._NL;
	$ret .= '<p>เรียน <b>ผู้จัดการ '.$billingInfo->insurername.'</b></p>'._NL;
	$ret .= '</div>';
	$ret .= '<div class="-date">';
	$ret .= '<p class="-bill-id">เลขที่ <b>'.$billingInfo->billno.'</b></p>';
	$ret .= '<p class="-bill-date">วันที่ '.sg_date($billingInfo->billdate,'ว ดดด ปปปป').'</p>'._NL;
	$ret .= '</div>';

	$ret .= '<div class="-description"><p class="-indent">ลูกค้า <b>'.$billingInfo->insurername.'</b> วางบิลซ่อมรถยนต์ มีรายละเอียดดังต่อไปนี้ </p></div>'._NL;
	$ret .= '</section><!-- -header -->';

	$ret .= '<section class="-tran">'.__garage_billing_view_tran($shopInfo,$billingInfo).'</section>';

	$ret .= '<section class="-footer">';
	$ret .= '<p class="-shopname">ขอแสดงความนับถือ<br /><br />'.$shopInfo->shopname.'</p>';
	$ret .= '<div class="-sign -co-2">'
		. '<div><span class="-signname">(<span></span>)</span><span>ผู้รับวางบิล</span><br /><span>วันที่รับวางบิล &nbsp;....../......./......<span><br /><br /><span>วันที่นัดจ่ายเงิน ....../......./......</span></div>'
		. '<div><span class="-signname">(<span></span>)</span><span>ผู้วางบิล</span></div>';
	$ret .= '</section><!-- -footer -->';

	$ret .= '<style type="text/css">
	.shopname {width:40%; float:right; text-align:center;}
	.billsign {width:40%; float:left; text-align:center; margin:40px 0 40px 0;}
	.billsign.-b2 {float:right;}
	.sign {display:inline-block;width:10em;}
	</style>';
	$ret .= '<script type="text/javascript">
	var urlRefresh="'.url('garage/billing/view/'.$billingInfo->billid.'/gettran').'";
	function refreshTran() {
		$.get(urlRefresh,function(html) {
			$("#garage-billing-tran").replaceWith(html);
		});
	}

	$("body").on("click","#garage-insurerqt td:not(input[type=checkbox])",function() {
		var urlAddQt="'.url('garage/billing/edit/'.$billingInfo->billid.'/addqt/').'";
		var $container=$(this).closest("tr");
		var $checkBox=$container.find("input");
		var qtid=$checkBox.val();
		$checkBox.prop("checked", !$checkBox.prop("checked"));
		$.get(urlAddQt+qtid,function(html){
			$container.remove();
			refreshTran();
		});
	});
	</script>';
	return $ret;
}

function __garage_billing_view_tran($shopInfo,$billingInfo) {
	$ret='';
	$tables = new Table();
	$tables->id='garage-billing-tran';
	$tables->addClass('-center');
	$tables->thead = array(
		'เลขเคลม',
		'เลข Job',
		'ยี่ห้อรถ',
		'เลขทะเบียนรถ',
		'รวมเงิน',
		'icons -c1 -hover-parent' => $billingInfo->billstatus > 0 ? '<a class="sg-action -no-print" href="'.url('garage/api/insurerqt',array('id'=>$billingInfo->insurerid,'cond'=>'nobill')).'" data-rel="box"><i class="icon -add"></i></a>' : ''
	);

	foreach ($billingInfo->qt as $rs) {
		$ui = new Ui();
		
		if ($rs->rcvid) {
			$ui->add('<a href="'.url('garage/recieve/'.$rs->rcvid).'"><i class="icon -material">attach_money</i></a>');
		}
		// Quation can delete even though it has revieved.
		$ui->add('<a class="sg-action -no-print" href="'.url('garage/billing/edit/'.$billingInfo->billid.'/delqt/'.$rs->qtid).'" data-rel="none" data-callback="refreshTran" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a>');

		$menu = '<nav class="nav -icons -hover -no-print" style="width: 60px;">'.($ui->count() ? $ui->build() : '').'</nav>';
		$tables->rows[] = array(
			$rs->insuclaimcode,
			$rs->jobno,
			$rs->brandid,
			$rs->plate,
			number_format($rs->replyprice,2),
			$menu,
		);
	}
	$tables->tfoot[]=array('','','','รวมเงิน',number_format($billingInfo->billTotal,2),'');
	$ret.=$tables->build();
	return $ret;
}
?>