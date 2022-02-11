<?php
function project_money_dopaidview($self,$projectInfo,$dopid) {
	$tpid=$projectInfo->tpid;
	$dopaidInfo=R::Model('org.dopaid.doc.get',$dopid);

	$isEdit=true;
	$ret.='<div class="container box project-money -dopaidview">';

	$ret.='<div class="row -header">';
	$ret.='<h3 class="title">ใบสำคัญรับเงิน</h3>'._NL;
	$ret.='</div>';

	$ret.='<div style="text-align:right;">ข้อตกลงเลขที่ <span class="billvalue">'.$dopaidInfo->agrno.'</span> <br />วันที่ '.sg_date($dopaidInfo->paiddate,'ว ดดด ปปปป').'</div>';

	$ret.='<div>ข้าพเจ้า <span class="billvalue">'.$dopaidInfo->fullname.'</span> เลขประจำบัตรประชาชน '.$dopaidInfo->cid.'</div>';
	$ret.='<div class="clear">ที่อยู่ <span class="billvalue">'.$dopaidInfo->address.'</span></div>';
	$ret.='<div class="clear">ได้รับเงินจาก <span class="billvalue">'.$dopaidInfo->orgname.' (เลขประจำตัวผู้เสียภาษี ) ('.$dopaidInfo->projectTitle.')</span></div>';
	$ret.='<div class="clear">ดังรายการต่อไปนี้</div>';

	$tables = new Table();
	$tables->thead=array('รายการ','amt'=>'จำนวนเงิน (บาท)','icons -c2 -no-print'=>'');
	foreach ($dopaidInfo->trans as $rs) {
		if ($isEdit) {
			$menu='<a href="'.url('project/money/'.$tpid.'/dopaidedittr/'.$rs->doptrid).'"><i class="icon -edit -gray"></i></a>';
			$menu.='<a class="sg-action" href="'.url('project/money/'.$tpid.'/dopaiddeltr/'.$rs->doptrid).'" data-rel="notify" data-removeparent="tr" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -cancel -gray"></i></a>';
		};
		$tables->rows[]=array(
											$rs->name.($rs->detail?'<div class="bill-trdetail">'.nl2br($rs->detail).'</div>':''),
											number_format($rs->amt,2),
											$menu,
										);
	}
	$tables->tfoot[]=array('('.sg_money2bath($dopaidInfo->total,2) .')',number_format($dopaidInfo->total,2),'');

	$ret.=$tables->build();

	if ($isEdit) {
		$ret.='<div class="actionbar -project -dopaid -sg-text-right -no-print"><a class="sg-action btn -primary" data-rel="parent" href="'.url('project/money/'.$tpid.'/dopaidaddtr/'.$dopid).'"><i class="icon -addbig -white"></i><span>เพิ่มค่าใช้จ่าย</span></a></div>'._NL;
	}
	//$ret.='<a class="btn -primary" href="'.url('project/money/'.$tpid.'/dopaidaddtr/'.$rs->dopid).'"><i class="icon -addbig -white"></i><span>เพิ่มค่าใช้จ่าย</span></a>';



	//$ret.=__garage_recieve_view_rcvtr($shopInfo,$rcvInfo);

	$ret.='<div class="row -billsign">';
	$ret.='<div class="-footermsg">ข้าพเจ้าขอรับรองว่ารายจ่ายข้างต้นได้จ่ายไปในงานโคงการที่ได้รับสนับสนุนจากกองทุนสนับสนุนการสร้างเสริมสุขภาพ (สสส.) โดยแท้จริง ทั้งนี้ไม่สามารถเรียกใบเสร็จรับเงินได้</div>';
	$ret.='<div class="col -md-6 billsign -sg-text-center">ผู้รับเงิน . . . . . . . . . . . . . . . . . . . . . . . . .<br />(<span class="sign">'.$dopaidInfo->fullname.'</span>)</div>';
	$ret.='<div class="col -md-6 billsign -sg-text-center">ผู้จ่ายเงิน . . . . . . . . . . . . . . . . . . . . . . . . .<br />(<span class="sign"></span>)</div>';
	$ret.='<br clear="all" />';
	$ret.='</div><!-- row -->';

	$ret.='</div><!-- container -->';

	//$ret.=print_o($dopaidInfo,'$dopaidInfo');

	$ret.='<style type="text/css">
	.module-project .box {background-color: #fff;}
	.module-project .box h3 {text-align: center; background-color: transparent; color:#333;}
	.bill-trdetail {color:#666; font-size: 0.9em;}

	@media print {
		.module-project .box {margin:0; padding:0; box-shadow:none; border:none;}
		.module-project .box h3 {color:#000; background-color:#fff;}
		.module-project .-billsign {position:absolute; bottom:0.5cm;}
		.module-project .-footermsg {margin-bottom:1cm;}
		.module-project .bill-trdetail {color:#000; font-size: 0.9em;}
	}
	</style>';
	return $ret;
}
?>