<?php
/**
* Project Join Recieve
* Created 2019-02-21
* Modify  2019-07-30
*
* @param Object $self
* @param Object $projectInfo
* @return String
*/

$debug = true;

function project_join_rcv($self, $projectInfo, $dopid = NULL) {
	if (!$projectInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $projectInfo->tpid;
	$calId = $projectInfo->calid;

	$ret = '';

	$dopaidInfo = R::Model('org.dopaid.doc.get', $dopid);
	if (!$dopaidInfo->dopid)
		return message('error', 'ERROR : ไม่มีข้อมูลใบสำคัญรับเงินตามเงื่อนไขที่ระบุ');

	$right = R::Model('project.join.right', $projectInfo);
	//$ret .= print_o($right, '$right');

	$isEdit = !$dopaidInfo->islock && $right->editJoin;

	if (!$right->accessJoin) return message('error', 'Access Denied');

	$ui = new Ui();
	$dropUi = new Ui();

	$ui->add('<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/view/'.$dopaidInfo->psnid).'" data-rel="box"><i class="icon -material">search</i></a>');

	if ($isEdit) {
		$ui->add('<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv.edit/'.$dopid).'" data-rel="box"><i class="icon -material">edit</i></a>');
	}

	if ($dopaidInfo->islock && $right->unlockRcv) {
		$ui->add('<a class="sg-action" data-rel="box"" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv.lock/'.$dopaidInfo->dopid).'" data-rel="box" title="Mark as not lock - ปลดล็อคใบสำคัญรับเงิน" data-width="480"><i class="icon -material">lock</i></a>');
	} else if (!$dopaidInfo->islock && $right->lockRcv) {
		$ui->add('<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv.lock/'.$dopaidInfo->dopid).'" data-rel="box" title="Mark as lock - ล็อคใบสำคัญรับเงิน" data-width="480"><i class="icon -material">lock_open</i></a>');
	} else {
		$ui->add('<i class="icon -material">'.($dopaidInfo->islock ? 'lock' : 'lock_open').'</i>');
	}


	$ui->add('<a href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv/'.$dopid).'" onclick="sgPrintPage(this.href);return false;"><i class="icon -material">print</i></a>');

	if (!$dopaidInfo->islock && $right->createRcv) {
		$dropUi->add('<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv.delete/'.$dopid).'" data-rel="none" data-done="close" data-callback="projectJoinDeleteRcvCallback" data-title="ลบใบสำคัญรับเงิน" data-confirm="ต้องการลบใบสำคัญรับเงิน กรุณายืนยัน?"><i class="icon -delete"></i><span class="">ลบใบสำคัญรับเงิน</span></a>');
	}
	if ($dropUi->count()) $ui->add(sg_dropbox($dropUi->build()));


	$ret .= '<div id="project-rcv-wrapper">';

	$ret .= '<header class="header -box -hidden">'.($dopaidInfo->psnid ? '<nav class="nav -back"><a class="sg-action" href="javascript:void(0)" data-rel="back"><i class="icon -material">arrow_back</i></a></nav>' : '').'<h3>ใบสำคัญรับเงิน</h3><nav class="nav">'.$ui->build().'</nav></header>';



	// Start show form
	$ret.='<div class="container project-money -dopaidview'.($dopaidInfo->formid ? ' -'.$dopaidInfo->formid : '').'">';

	$ret.='<div class="row -header">';
	$ret.='<h3 class="title -sg-text-center">ใบสำคัญรับเงิน</h3>'._NL;
	$ret.='</div>';


	$ret .= '<div style="text-align:right; margin-bottom: 1.5em;">'
		. (isset($dopaidInfo->docText->paidDocNo) ? ($dopaidInfo->docText->paidDocNo.($dopaidInfo->docText->paidDocNo != '' ? '<br />' : '')) : 'ข้อตกลงเลขที่ <span class="billvalue">'.$projectInfo->info->agrno.'</span><br />')
		. 'วันที่ '.sg_date($dopaidInfo->paiddate,'ว ดดด ปปปป')
		. '</div>';

	$ret.='<div>ข้าพเจ้า <span class="billvalue">'.$dopaidInfo->paidname.'</span> เลขประจำบัตรประชาชน '.$dopaidInfo->cid.'</div>';
	$ret.='<div class="clear">ที่อยู่ <span class="billvalue">'.$dopaidInfo->address.'</span></div>';

	$ret .= '<div class="clear">';
	if ($dopaidInfo->docText->paidFrom) {
		$ret .= $dopaidInfo->docText->paidFrom;
	} else {
		$ret .= 'ได้รับเงินจาก <span class="billvalue">'
			. $dopaidInfo->paiddocfrom
			. ($dopaidInfo->paiddoctagid ? ' (เลขประจำตัวผู้เสียภาษี '.$dopaidInfo->paiddoctagid.') ' : '')
			. '(โครงการ'.$projectInfo->title.')</span>'
			;
	}
	$ret .= '</div>';
	$ret.='<div class="clear">ดังรายการต่อไปนี้</div>';

	$tables = new Table();
	$tables->thead=array('no' => 'ลำดับ', 'detail' => 'รายการ','amt -hover-parent'=>'จำนวนเงิน (บาท)');
	$no = 0;
	foreach ($dopaidInfo->trans as $rs) {
		if ($isEdit) {
			$menu = '<nav class="iconset -hover -no-print">';
			$menu .= '<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv.tr.edit/'.$rs->doptrid).'" data-rel="#for-add"><i class="icon -material -gray">edit</i></a>';
			$menu .= '<a class="sg-action" href="'.url('project/join/'.$tpid.'/'.$calId.'/rcv.tr.delete/'.$rs->doptrid).'" data-rel="replace:#project-rcv-wrapper" data-ret="'.url('project/join/'.$tpid.'/'.$calId.'/rcv/'.$dopid).'" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a>';
			$menu .= '</nav>';
		};
		$tables->rows[]=array(
											++$no,
											$rs->name.($rs->detail?'<div class="bill-trdetail">'.nl2br($rs->detail).'</div>':''),
											number_format($rs->amt,2)
											.$menu,
										);
	}
	$tables->tfoot[]=array('<td></td>','('.sg_money2bath($dopaidInfo->total,2) .')',number_format($dopaidInfo->total,2));

	$ret.=$tables->build();

	if ($isEdit) {
		$ret.='<div id="for-add" class="actionbar -project -dopaid -sg-text-right -no-print"><a class="sg-action btn -primary" data-rel="replace:#for-add" href="'.url('project/join/'.$tpid.'/'.$calId.'/addrcvtr/'.$dopid).'"><i class="icon -material -white">add</i><span>เพิ่มค่าใช้จ่าย</span></a></div>'._NL;
	}
	//$ret.='<a class="btn -primary" href="'.url('project/money/'.$tpid.'/dopaidaddtr/'.$rs->dopid).'"><i class="icon -addbig -white"></i><span>เพิ่มค่าใช้จ่าย</span></a>';



	//$ret.=__garage_recieve_view_rcvtr($shopInfo,$rcvInfo);

	$ret.='<div class="row -billsign">';
	$ret.='<div class="-footermsg">';
		if ($dopaidInfo->docText->paidFooter) {
		$ret .= $dopaidInfo->docText->paidFooter;
	} else {
		$ret .= 'ข้าพเจ้าขอรับรองว่ารายจ่ายข้างต้นได้จ่ายไปในงานโครงการที่ได้รับทุนสนับสนุนจาก'.$dopaidInfo->paiddocfrom.' โดยแท้จริง ทั้งนี้ไม่สามารถเรียกใบเสร็จรับเงินได้';
	}
	$ret .= '</div>';
	$ret .= '<div class="col -md-6 billsign -sg-text-center">ผู้รับเงิน . . . . . . . . . . . . . . . . . . . . . . . . .<br />(<span class="sign">'.$dopaidInfo->paidname.'</span>)</div>';
	$ret .= '<div class="col -md-6 billsign -sg-text-center">ผู้จ่ายเงิน . . . . . . . . . . . . . . . . . . . . . . . . .<br />'
		. '(<span class="sign">'
		. SG\getFirst($dopaidInfo->docText->paidByName, $dopaidInfo->paiddocbyname)
		. '</span>)'
		. '</div>';
	$ret.='<br clear="all" />';
	$ret.='</div><!-- row -->';

	$ret.='</div><!-- container -->';

	//$ret.=print_o($dopaidInfo,'$dopaidInfo');
	//$ret.=print_o($projectInfo,'$projectInfo');

	$ret.='<style type="text/css">
	.module-project .box {background-color: #fff;}
	.module-project .box h3 {text-align: center; background-color: transparent; color:#333;}
	.bill-trdetail {color:#666; font-size: 0.9em;}
	.module-project .-footermsg {margin-bottom: 16px;}
	.billsign .sign {min-width: 12em; display: inline-block;}

	@media print {
		.module-project {margin: 0; padding: 0;}
		.module-project .page.-content, .module-project .page.-primary, .module-project .page.-main {margin: 0; padding:0;}
		.module-project h3 {font-size: 20pt; color:#000; background-color:#fff; font-weight: bold;}
		.module-project .-billsign {position:absolute; bottom:0.5cm;}
		.module-project .-footermsg {margin-bottom:1cm;}
		.module-project .bill-trdetail {color:#000; font-size: 0.9em;}
		.module-project table.item {margin-top: 1cm; display: block;}
		table.item td.col-detail {width:100%;}
		table.item>tbody>tr>td {padding:8px;}
		table.item>thead>tr>th {padding:8px; white-space: nowrap;}
		table.item>tfoot>tr>td {padding:8px;}
	}
	</style>';

	$ret .= '<script type="text/javascript">
	function projectJoinDeleteRcvCallback() {
		$.post(window.location.href, function(html) {
			$("#main").html(html)
		})
	}
	</script>';
	$ret .= '<!-- project-rcv-wrapper --></div>';

	return $ret;
}
?>