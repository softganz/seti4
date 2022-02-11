<?php
/**
* Garage : AP Recieve View
* Created 2019-02-28
* Modify  2020-10-21
*
* @param Object $self
* @param Object $rcvInfo
* @return String
*
* @usage garage/aprcv/{$rcvId}
*/

$debug = true;

function garage_aprcv_view($self, $rcvInfo) {
	$rcvId = $rcvInfo->rcvid;

	$shopInfo = R::Model('garage.get.shop');

	if ($rcvInfo->count() == 0) return message('ERROR','PROCESS ERROR:Invalid Document');
	else if (!R::Model('garage.right',$shopInfo, 'INVENTORY', $rcvInfo->shopid)) return message('ERROR','Access Denied');

	$ret = '<div id="garage-aprcv-view" class="garage-bill -aprcv -forprint" data-url="'.url('garage/aprcv/'.$rcvId).'">';
	$ret .= __garage_recieve_info($shopInfo,$rcvInfo,'ใบรับของ');
	$ret .= '</div>';
	//$ret.=print_o($rcvInfo,'$rcvInfo');

	return $ret;
}


function __garage_recieve_info($shopInfo,$rcvInfo,$billheader=NULL,$billfooter=NULL) {
	$ret.='<section class="-header -box">';
	$ret.='<address><b>'.$shopInfo->shopname.'</b><br />'.$shopInfo->shopaddr.' '.$shopInfo->shopzipcode.'<br />เลขประจำตัวผู้เสียภาษี '.$shopInfo->taxid.'<br />โทร. '.$shopInfo->shopphone.'</address>'._NL;
	$ret.='<h3 class="-title">'.$billheader.'</h3>'._NL;

	$ret.='<div class="-info">';
	$ret.='<p><b class="label">นามผู้ขาย </b><span class="billvalue">'.$rcvInfo->apname.'</span></p>';
	$ret.='<p><b class="label">ที่อยู่</b><span class="billvalue">'.$rcvInfo->rcvaddr.'</span></p>';
	//$ret.='<b>โทรศัพท์</b> '.$rcvInfo->rcvphone.'<br />';
	//$ret.='เลขประจำตัวผู้เสียภาษี '.$rcvInfo->rcvtaxid;
	if ($rcvInfo->rcvbranch) $ret.=' สาขาลำดับที่ '.$rcvInfo->rcvbranch;
	$ret.='</div><!-- -info -->';

	$ret.='<div class="-date">';
	$ret.='<p><b class="label">เลขที่เอกสาร </b><span class="billvalue">'.$rcvInfo->rcvno.'</span></p>';
	$ret.='<p><b class="label">วันที่ </b><span class="billvalue">'.sg_date($rcvInfo->rcvdate,'d/m/ปปปป').'</span></p>';
	$ret.='<p><b class="label">อัตราภาษีร้อยละ </b><span class="billvalue">'.$rcvInfo->vatrate.'</span></p>';
	$ret.='<p><b class="label">เลขที่อ้างอิง </b><span class="billvalue">'.$rcvInfo->refno.'</span></p>';
	$ret.='</div><!-- -date -->';
	$ret.='</section><!-- -header -->';


	if (empty($billheader)) {
		return $ret;
	}

	$ret.=__garage_recieve_view_rcvtr($rcvInfo);

	return $ret;
}

function __garage_recieve_view_rcvtr($rcvInfo) {
	$rcvId=$rcvInfo->rcvid;
	$isEdit=true;

	$ret='';

	$jobTranInfo=NULL;
	if ($action=='edit' && !empty($trid)) {
		$jobTranInfo=R::Model('garage.job.tr.get',$tpid,$trid);
		$ret.='<script type="text/javascript">currentRepairInfo={"priceA":'.$jobTranInfo->priceA.',"priceB":'.$jobTranInfo->priceB.',"priceC":'.$jobTranInfo->priceC.',"priceD":'.$jobTranInfo->priceD.'}</script>';
		//$ret.=print_o($jobTranInfo,'$jobTranInfo');
	}

	if (empty($jobTranInfo->datecmd)) $jobTranInfo->datecmd=date('Y-m-d');
	if (empty($jobTranInfo->qty)) $jobTranInfo->qty=1;




	$ret.='<form id="garage-job-tr-new" class="sg-form" method="post" action="'.url('garage/info/'.$rcvId.'/aprcv.tran.save').'" data-checkvalid="true" data-rel="notify" data-done="load->replace:#garage-aprcv-view">'._NL;
	$ret.='<input type="hidden" name="trid" value="'.$jobTranInfo->jobtrid.'" />'._NL;

	$tables = new Table();
	$tables->addClass('-garage-job-tran'.($action?' -'.$action:''));
	$tables->thead = array(
		'ใบสั่งซ่อม',
		'code -nowrap' => 'รหัส',
		'รายการ','amt'=>'จำนวน',
		'money -price'=>'ราคา',
		'money -discountrate'=>'%ส่วนลด',
		'money -discountamt'=>'เงินส่วนลด',
		'money -total'=>'จำนวนเงิน',
		'icons -c1 -hover-parent'=>'',
	);

	if ($isEdit) {
		$tables->rows[]=array(
			'<input id="jobid" type="hidden" name="jobid" value="" /><input id="refname" class="form-text sg-autocomplete -fill" type="text" name="refname" value="" placeholder="เลขที่ใบสั่งซ่อม" size="7" data-query="'.url('garage/api/job',array('show' => 'notreturned')).'" data-altfld="jobid" data-select="label" />',
			'<input id="repairid" type="hidden" name="stkid" value="'.$jobTranInfo->repairid.'" />'
			.'<input id="repaircode" class="form-text sg-autocomplete -fill" type="text" name="repaircode" value="'.$jobTranInfo->repaircode.'" placeholder="รหัสสินค้า" size="5" data-query="'.url('garage/api/repaircode',array('type'=>2)).'" data-item="20" data-altfld="repairid" data-select=\'{"repaircode":"code","repairname":"name","price":"priceA"}\' data-select-name="repairname" data-callback="garageRepairCodeSelect" data-class="-repaircode" data-width="400" />',
			'<input id="repairname" class="form-text -fill" type="text" name="repairname" value="'.$jobTranInfo->repairname.'" placeholder="รายละเอียด" />',
			'<input id="qty" class="form-text -require -numeric" type="text" name="qty" value="'.$jobTranInfo->qty.'" placeholder="0" size="3" />',
			'<input id="price" class="form-text -fill -money" type="text" name="price" value="'.$jobTranInfo->price.'" placeholder="0.00" size="4" />',
			'<input id="discountrate" class="form-text -money" type="text" name="discountrate" value="'.$jobTranInfo->discountrate.'" placeholder="0.00" size="6" />',
			'<input id="discountamt" class="form-text -money" type="text" name="discountamt" value="'.$jobTranInfo->discountamt.'" placeholder="0.00" size="6" />',
			'<input id="totalsale" class="form-text -fill -money" type="text" name="totalsale" value="'.$jobTranInfo->totalsale.'" placeholder="0.00" size="5" readonly="readonly" />',
			'<button class="btn -primary" type="submit" style="white-space:nowrap"><i class="icon -save -white"></i><span class="-hidden">บันทึก'.($action=='edit'?'แก้ไข':'รายการ').'</span></button>',
			'config'=>array('class'=>'-input -no-print'),
		);
		/*
		$tables->rows[]=array(
			'<td colspan="9">'
			.'<label>ส่วนลด <input id="discountrate" class="form-text -number" type="text" name="discountrate" value="'.$jobTranInfo->discountrate.'" placeholder="0" size="2" /> %</label> '
			.'<label>ภาษีมูลค่าเพิ่ม <input id="vatrate" class="form-text -number" type="text" name="vatrate" value="'.$jobTranInfo->vatrate.'" placeholder="0" size="2" /> %</label> '
			.'</td>',
			'config'=>array('class'=>'-input -no-print'),
		);
		*/
		$tables->rows[] = '<tr><td colspan="9"><span class="highlight" style="color: #f00;">** กรณีรับของเพื่อเป็นต้นทุนของใบสั่งซ่อมโดยตรง ไม่ต้องระบุรหัสสินค้า ระบบจะทำการตัดยอดทั้งจำนวนรับไปเป็นต้นทุนของใบสั่งซ่อมโดยไม่ทำรายการข้อมูลสต็อก **</span></td></tr>';
	}

	// Display transaction

	foreach ($rcvInfo->items as $rs) {
		$isRecieve = $rs->qty >= 0;
		$ui = new Ui(NULL,'ui-nav');
		if ($isEdit) {
			$ui->add('<a class="sg-action" href="'.url('garage/info/'.$rcvId.'/aprcv.tran.remove/'.$rs->stktrid).'" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-rel="notify" data-done="remove:parent tr"><i class="icon -material -gray">cancel</i></a>');
			//$menu .= '<a title="TranId = '.$rs->stktrid.($rs->lotid ? ' LotId = '.$rs->lotid : '').'"><i class="icon -material">help</i></a>';
		}

		$menu = '<nav class="nav -icons -hover">'.$ui->build().'</nav>';

		$total = $rs->qty * $rs->price - $rs->discountamt;
		$tables->rows[] = array(
			'<a href="'.url('garage/job/'.$rs->tpid).'">'.$rs->jobno.'</a>',
			$rs->stkcode,
			SG\getFirst($rs->description,$rs->stkname),
			number_format($rs->qty),
			$isRecieve ? number_format($rs->price,2) : '<span class="-disabled">('.number_format($rs->price,2).')</span>',
			$rs->discountrate > 0 ? ($isRecieve ? $rs->discountrate : '<span class="-disabled">('.$rs->discountrate.')</span>') : '',
			$rs->discountamt > 0 ? ($isRecieve ? number_format($rs->discountamt,2) : '<span class="-disabled">('.number_format($rs->discountamt,2).')</span>') : '',
			$isRecieve ? number_format($total,2) : '<span class="-disabled">('.number_format($rs->total,2).')</span>',
			$menu,
			'config'=>array('class'=>'item-part'),
		);
	}
	$tables->rows[]=array('','','','<td colspan="4">รวมเงิน</td>',number_format($rcvInfo->subtotal,2),'','config'=>array('class'=>'item-part subfooter'));
	$tables->rows[]=array('','','','<td colspan="4">ส่วนลด</td>','-'.number_format($rcvInfo->discountamt,2),'','config'=>array('class'=>'item-part subfooter'));
	$tables->rows[]=array('','','','<td colspan="4">ภาษีมูลค่าเพิ่ม ('.$rcvInfo->vatrate.'%)</td>',number_format($rcvInfo->vatamt,2),'','config'=>array('class'=>'item-part subfooter'));
	$tables->rows[]=array('','','','<td colspan="4">รวมเงินทั้งสิ้น</td>',number_format($rcvInfo->grandtotal,2),'','config'=>array('class'=>'item-part subfooter'));

	$ret.=$tables->build();
	$ret.='</form>';

	//$ret.=print_o($rcvInfo,'$rcvInfo');

	$ret.='<script type="text/javascript">$("#refname").focus();</script>';
	if (!$isEdit) {
		$ret.='<style type="text/css">
		.item.-garage-job-tran thead {display:none;}
		</style>';
	}
	$ret.='<style type="text/css">
	#damagecode {width: 3em;}
	.item.-garage-job-tran thead .icon {display:none;}
	.item-part.subfooter>td:nth-child(4) {text-align:left;}
	</style>';

	return $ret;
}


?>