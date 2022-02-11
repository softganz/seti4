<?php
function garage_order_view($self, $orderInfo) {
	$orderId = $orderInfo->ordid;
	$shopInfo = R::Model('garage.get.shop');

	if ($orderInfo->count() == 0) return message('ERROR','PROCESS ERROR:Invalid Document');
	else if (!R::Model('garage.right',$shopInfo, 'INVENTORY', $orderInfo->shopid)) return message('ERROR','Access Denied');


	$ret .= '<div id="garage-order-view" class="garage-bill -order -forprint" data-url="'.url('garage/order/'.$orderId).'">';
	$ret .= __garage_recieve_info($shopInfo,$orderInfo,'ใบสั่งของ');
	$ret .= '</div>';

	//$ret .= print_o($orderInfo,'$orderInfo');
	return $ret;
}


function __garage_recieve_info($shopInfo,$orderInfo,$billheader = NULL,$billfooter = NULL) {
	$ret .= '<section class="-header">';
	$ret .= '<address><b>'.$shopInfo->shopname.'</b><br />'.$shopInfo->shopaddr.' '.$shopInfo->shopzipcode.'<br />เลขประจำตัวผู้เสียภาษี '.$shopInfo->taxid.'<br />โทร. '.$shopInfo->shopphone.'</address>'._NL;
	$ret .= '<h3 class="-title">'.$billheader.'</h3>'._NL;

	$ret .= '<div class="-info">';
	$ret .= '<p><b class="label">นามผู้ขาย </b><span class="billvalue">'.$orderInfo->apname.'</span></p>';
	$ret .= '<p><b class="label">ที่อยู่</b><span class="billvalue">'.$orderInfo->apaddr.'</span></p>';
	if ($orderInfo->rcvphone) $ret .= '<p><b class="label">โทรศัพท์</b> '.$orderInfo->rcvphone.'</p>';
	$ret .= '</div><!-- -info -->';

	$ret .= '<div class="-date">';
	$ret .= '<p><b class="label">เลขที่เอกสาร </b><span class="billvalue">'.$orderInfo->ordno.'</span></p>';
	$ret .= '<p><b class="label">วันที่ </b><span class="billvalue">'.sg_date($orderInfo->orddate,'d/m/ปปปป').'</span></p>';
	//$ret .= '<div class="clear"><b class="label">อัตราภาษีร้อยละ </b><span class="billvalue">'.$orderInfo->vatrate.'</span></div>';
	$ret .= '</div><!-- -date -->';
	$ret .= '</section>';

	$ret .= __garage_recieve_view_rcvtr($shopInfo,$orderInfo);

	return $ret;
}

function __garage_recieve_view_rcvtr($shopInfo,$orderInfo) {
	$orderId=$orderInfo->ordid;
	$isEdit=true;

	$ret='';

	$jobTranInfo=NULL;
	/*
	if ($action=='edit' && !empty($trid)) {
		$jobTranInfo=R::Model('garage.job.tr.get',$tpid,$trid);
		$ret .= '<script type="text/javascript">currentRepairInfo={"priceA":'.$jobTranInfo->priceA.',"priceB":'.$jobTranInfo->priceB.',"priceC":'.$jobTranInfo->priceC.',"priceD":'.$jobTranInfo->priceD.'}</script>';
		//$ret.=print_o($jobTranInfo,'$jobTranInfo');
	}
	*/

	if (empty($jobTranInfo->qty)) $jobTranInfo->qty=1;




	$ret .= '<form id="garage-job-tr-new" class="sg-form" method="post" action="'.url('garage/info/'.$orderId.'/order.tran.save').'" data-checkvalid="true" data-rel="notify" data-done="load->replace:#garage-order-view">'._NL;
	//$ret .= '<input type="hidden" name="trid" value="'.$jobTranInfo->jobtrid.'" />'._NL;
	$tables = new Table();
	$tables->addClass('-garage-job-tran'.($action?' -'.$action:''));
	$tables->thead=array('รหัสสินค้า','รายการ','amt'=>'จำนวน','price -money'=>'ราคา','total -money'=>'จำนวนเงิน', 'a -hover-parent' => '');


	if ($isEdit) {
		$tables->rows[]=array(
			'<input id="repairid" type="hidden" name="stkid" value="'.$jobTranInfo->repairid.'" />'
			.'<input id="repaircode" class="form-text sg-autocomplete -fill -require" type="text" name="repaircode" value="'.$jobTranInfo->repaircode.'" placeholder="รหัสสินค้า" size="5" data-query="'.url('garage/api/repaircode',array('type'=>2)).'" data-item="20" data-altfld="repairid" data-select=\'{"repaircode":"code","repairname":"name"}\' data-select-name="repairname" data-callback="garageRepairCodeSelect" data-class="-repaircode" data-width="400" />',
			'<input id="repairname" class="form-text -fill" type="text" name="repairname" value="'.$jobTranInfo->repairname.'" placeholder="รายละเอียด" />',
			'<input id="qty" class="form-text -require -numeric" type="text" name="qty" value="'.$jobTranInfo->qty.'" placeholder="0" size="3" />',
			'<input id="price" class="form-text -fill -money" type="text" name="price" value="'.$jobTranInfo->price.'" placeholder="0.00" size="4" />',
			'<input id="totalsale" class="form-text -fill -money" type="text" name="totalsale" value="'.$jobTranInfo->totalsale.'" placeholder="0.00" size="5" readonly="readonly" />',
			'<button class="btn -primary" type="submit" style="white-space:nowrap"><i class="icon -save -white"></i><span class="-hidden">บันทึก'.($action=='edit'?'แก้ไข':'รายการ').'</span></button>',
			'config'=>array('class'=>'-input -no-print'),
		);
	}

	// Display transaction

	if ($orderInfo->items) {
		foreach ($orderInfo->items as $rs) {
			$isRecieve=$rs->qty>=0;
			$ui = new Ui(NULL,'ui-nav');
			$ui->addConfig('nav', '{class: "nav -icons -hover"}');
			$ui->add('<a class="sg-action" href="'.url('garage/info/'.$orderId.'/order.tran.remove/'.$rs->ordtrid).'" data-rel="notify" data-done="load->replace:#garage-order-view" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -material">cancel</i></a>');

			$tables->rows[]=array(
				$rs->stkcode,
				$rs->stkname,
				number_format($rs->qty),
				$isRecieve?number_format($rs->price,2):'',
				($isRecieve ? number_format($rs->total,2) : ''),
				$ui->build(),
				'config' => array('class' => 'item-part'),
			);
		}
		//$tables->rows[]=array('','','<td colspan="2">รวมเงิน</td>',number_format($orderInfo->subtotal,2),'','config'=>array('class'=>'item-part subfooter'));
		//$tables->rows[]=array('','','<td colspan="2">ส่วนลด ('.$orderInfo->discountrate.'%)</td>',number_format($orderInfo->discountamt,2),'','config'=>array('class'=>'item-part subfooter'));
		//$tables->rows[]=array('','','<td colspan="2">ภาษีมูลค่าเพิ่ม ('.$orderInfo->vatrate.'%)</td>',number_format($orderInfo->vatamt,2),'','config'=>array('class'=>'item-part subfooter'));
		$tables->rows[]=array('','','<td colspan="2">รวมเงินทั้งสิ้น</td>',number_format($orderInfo->total,2),'','config'=>array('class'=>'item-part subfooter'));
	} else {
		//$tables->rows[]=array('<td colspan="8" align="center">ไม่มีรายการ</td>');
	}

	$ret.=$tables->build();
	$ret .= '</form>';

	$ret .= '<script type="text/javascript">$("#refname").focus();</script>';
	if (!$isEdit) {
		$ret .= '<style type="text/css">
		.item.-garage-job-tran thead {display:none;}
		</style>';
	}
	$ret .= '<style type="text/css">
	#damagecode {width: 3em;}
	.item.-garage-job-tran thead .icon {display:none;}
	.item-part.subfooter>td:nth-child(3) {text-align:left;}
	</style>';

	return $ret;
}


?>