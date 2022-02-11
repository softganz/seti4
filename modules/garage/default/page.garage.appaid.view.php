<?php
function garage_appaid_view($self, $paidInfo) {
	$shopInfo = R::Model('garage.get.shop');

	new Toolbar($self,'จ่ายชำระหนี้'.($paidInfo ? ' - '.$paidInfo->paidno:''),'finance',$paidInfo);

	if ($paidInfo->count() == 0) return '<p class="notify">ไม่มีรายการที่ระบุ</p>';

	$ret .= '<div id="garage-appaid-view" class="garage-bill -appaid -forprint" data-url="'.url('garage/appaid/'.$paidInfo->paidid).'">'._NL;
	$ret .= __garage_appaid_info($shopInfo,$paidInfo);
	$ret .= '</div>'._NL;

	return $ret;
}

function __garage_appaid_info($shopInfo,$paidInfo) {
	$ret .= '<section class="-header">';
	$ret .= '<address>'.$shopInfo->shopname.'<br />'.$shopInfo->shopaddr.' '.$shopInfo->shopzipcode.'<br />โทร. '.$shopInfo->shopphone.'</address>'._NL;
	$ret .= '<h3 class="-title">ใบจ่ายชำระหนี้</h3>'._NL;
	$ret .= '<div class="-info">';
	$ret .= '<p><b class="label">ผู้จำหน่าย </b>'.$paidInfo->apname.'</p>';
	$ret .= '</div>'._NL;

	$ret .= '<div class="-date">';
	$ret .= '<p><b class="label">เลขที่ </b>'.$paidInfo->paidno.'</p>';
	$ret .= '<p><b class="label">วันที่ </b>'.sg_date($paidInfo->paiddate,'ว ดดด ปปปป').'</p>'._NL;
	$ret .= '</div>';
	$ret .= '</section><!-- -header -->';

	$ret .= '<section class="-tran">';
	$ret.=__garage_appaid_view_tran($shopInfo,$paidInfo);
	$ret .= '</section>';

	$ret .= '<section class="-footer">';
	$ret .= '<div class="-sign -co-2">'
		. '<div><span class="-signname">(<span></span>)</span><span>ผู้จ่ายเงิน</span><span>....../.........../......</span></div>'
		. '<div><span class="-signname">(<span></span>)</span></span><span>ผู้รับเงิน</span><span>....../.........../......</span></div>'
		. '</div>';
	$ret .= '</section>';

	head('<script type="text/javascript">
	var urlRefresh="'.url('garage/appaid/'.$paidInfo->paidid).'";
	function refreshTran() {
		$.get(urlRefresh,function(html) {
			$("#garage-appaid-view").replaceWith(html);
		});
	}

	$(document).on("click","#garage-insurerqt td:not(input[type=checkbox])",function() {
		var urlAddQt="'.url('garage/info/'.$paidInfo->paidid.'/appaid.tran.save/').'";
		var $container=$(this).closest("tr");
		var $checkBox=$container.find("input");
		var rcvid=$checkBox.val();
		$checkBox.prop("checked", !$checkBox.prop("checked"));
		console.log("Click "+urlAddQt+rcvid)
		$.get(urlAddQt+rcvid,function(html){
			$container.remove();
			refreshTran();
		});
	});
	</script>'
	);
	return $ret;
}

function __garage_appaid_view_tran($shopInfo,$paidInfo) {
	$ret='';
	$tables = new Table();
	$tables->id='garage-appaid-tran';
	$tables->addClass('-center');
	$tables->thead=array('ใบรับสินค้า','วันที่','จำนวนเงิน','ยอดคงค้าง','ยอดจ่าย','<a class="sg-action" href="'.url('garage/api/apmast',array('id'=>$paidInfo->apid,'cond'=>'nopaid')).'" data-rel="box"><i class="icon -add"></i></a>');

	foreach ($paidInfo->apmast as $rs) {
		$menu='<a class="sg-action -no-print" href="'.url('garage/info/'.$paidInfo->paidid.'/appaid.tran.remove/'.$rs->rcvid).'" data-rel="none" data-done="load->replace:#garage-appaid-view" data-title="ลบรายการ" data-confirm="ต้องการลบใบเสนอราคานี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a>';
		$tables->rows[]=array(
			$rs->rcvno,
			$rs->rcvdate,
			number_format($rs->grandtotal,2),
			number_format($rs->grandtotal,2),
			number_format($rs->grandtotal,2),
			$menu,
		);
	}
	$tables->tfoot[]=array('','','','รวมเงิน',number_format($paidInfo->grandtotal,2),'');
	$ret.=$tables->build();
	return $ret;
}
?>