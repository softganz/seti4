<?php
/**
* Garage :  Requisition View
* Created 2019-02-28
* Modify  2020-10-21
*
* @param Object $self
* @param Object $reqInfo
* @return String
*
* @usage garage/req/{$reqId}
*/

$debug = true;

function garage_req_view($self, $reqInfo) {
	$reqId = $reqInfo->reqid;
	$shopInfo = R::Model('garage.get.shop');

	if ($reqInfo->count() == 0) return message('ERROR','PROCESS ERROR:Invalid Document');
	else if (!R::Model('garage.right',$shopInfo, 'INVENTORY', $reqInfo->shopid)) return message('ERROR','Access Denied');

	$ret = '<div id="garage-req-view" class="garage-bill -req -forprint" data-url="'.url('garage/req/'.$reqId).'">';
	$ret .= __garage_recieve_info($shopInfo,$reqInfo,'ใบเบิกของ');
	$ret .= '</div>';
	//$ret .= print_o($reqInfo,'$reqInfo');
	return $ret;
}


function __garage_recieve_info($shopInfo, $reqInfo, $billheader = NULL, $billfooter = NULL) {
	$ret .= '<section class="-header">';
	$ret.='<address><b>'.$shopInfo->shopname.'</b><br />'.$shopInfo->shopaddr.' '.$shopInfo->shopzipcode.'<br />เลขประจำตัวผู้เสียภาษี '.$shopInfo->taxid.'<br />โทร. '.$shopInfo->shopphone.'</address>'._NL;
	$ret.='<h3 class="-title">'.$billheader.'</h3>'._NL;

	$ret.='<div class="-info">';
	$ret.='<p><b class="label">นามผู้เบิก </b><span class="billvalue">'.$reqInfo->apname.'</span></p>';
	$ret.='</div>';
	$ret.='<div class="-date">';
	$ret.='<p><b class="label">เลขที่เอกสาร </b><span class="billvalue">'.$reqInfo->reqno.'</span></p>';
	$ret.='<p><b class="label">วันที่ </b><span class="billvalue">'.sg_date($reqInfo->reqdate,'d/m/ปปปป').'</span></p>';
	$ret.='<p><b class="label">ใบสั่งซ่อม </b><span class="billvalue"><a href="'.url('garage/job/'.$reqInfo->tpid).'">'.$reqInfo->jobno.'</a></span></p>';
	$ret.='</div>';
	$ret .= '</section>';


	if (empty($billheader)) {
		return $ret;
	}

	$ret .= __garage_recieve_view_reqtr($reqInfo);

	return $ret;
}

function __garage_recieve_view_reqtr($reqInfo) {
	$reqId = $reqInfo->reqid;
	$isEdit = true;

	$ret = '';

	$jobTranInfo = NULL;
	if ($action == 'edit' && !empty($trid)) {
		$jobTranInfo = R::Model('garage.job.tr.get',$tpid,$trid);
		$ret .= '<script type="text/javascript">currentRepairInfo={"priceA":'.$jobTranInfo->priceA.',"priceB":'.$jobTranInfo->priceB.',"priceC":'.$jobTranInfo->priceC.',"priceD":'.$jobTranInfo->priceD.'}</script>';
		//$ret.=print_o($jobTranInfo,'$jobTranInfo');
	}

	if (empty($jobTranInfo->datecmd)) $jobTranInfo->datecmd = date('Y-m-d');
	if (empty($jobTranInfo->qty)) $jobTranInfo->qty = 1;

	$ret .= '<form id="garage-job-tr-new" class="sg-form" method="post" action="'.url('garage/info/'.$reqId.'/req.tran.save').'" data-checkvalid="true" data-rel="notify" data-done="load->replace:#garage-req-view">'._NL;
	$ret .= '<input type="hidden" name="trid" value="'.$jobTranInfo->jobtrid.'" />'._NL;
	$tables = new Table();
	$tables->addClass('-garage-job-tran'.($action?' -'.$action:''));
	$tables->thead=array('รหัส','รายการ','amt'=>'จำนวน','price -money'=>'ต้นทุน','total -money -hover-parent'=>'จำนวนเงิน');

	$damagecodeList=mydb::select('SELECT * FROM %garage_damage%')->items;
	$damagecodeOptions='<option value="">???</option>';
	foreach ($damagecodeList as $v) {
		$damagecodeOptions.='<option value="'.$v->damagecode.'" '.($jobTranInfo->damagecode==$v->damagecode?'selected="selected"':'').' data-pretext="'.$v->pretext.'">'.$v->damagecode.' : '.$v->damagename.'</option>';
	}
	if ($isEdit) {
		$tables->rows[]=array(
			'<input id="repairid" type="hidden" name="stkid" value="'.$jobTranInfo->repairid.'" />'
			.'<input id="repaircode" class="form-text sg-autocomplete -fill -require" type="text" name="repaircode" value="'.$jobTranInfo->repaircode.'" placeholder="รหัสสินค้า" size="5" data-query="'.url('garage/api/repaircode',array('type'=>2)).'" data-item="20" data-altfld="repairid" data-select=\'{"repaircode":"code","repairname":"name","price":"priceA"}\' data-select-name="repairname" data-callback="garageRepairCodeSelect" data-class="-repaircode" data-width="400" />',
			'<input id="repairname" class="form-text -fill" type="text" name="repairname" value="'.$jobTranInfo->repairname.'" placeholder="รายละเอียด" />',
			'<input id="qty" class="form-text -require -numeric" type="text" name="qty" value="'.$jobTranInfo->qty.'" placeholder="0" size="3" />',
			'<td colspan="3"><button class="btn -primary" type="submit" style="white-space:nowrap"><i class="icon -save -white"></i><span class="-hidden">บันทึก'.($action=='edit'?'แก้ไข':'รายการ').'</span></button></td>',
			'config'=>array('class'=>'-input -no-print'),
		);
	}

	// Display transaction

	if ($reqInfo->items) {
		foreach ($reqInfo->items as $rs) {
			$ui = new Ui(NULL,'ui-nav');
			$ui->addConfig('nav', '{class: "nav -icons -hover"}');
			$ui->add('<a class="sg-action" href="'.url('garage/info/'.$reqId.'/req.tran.remove/'.$rs->stktrid).'" data-rel="notify" data-done="remove:parent tr" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -material">cancel</i></a>');

			$tables->rows[]=array(
				$rs->stkcode,
				$rs->stkname,
				number_format($rs->qty),
				number_format($rs->price,2),
				number_format($rs->total,2)
				.$ui->build(),
				'config'=>array('class'=>'item-part'),
			);
		}
		$tables->rows[] = array(
			'',
			'',
			'<td colspan="2">รวมต้นทุน</td>',
			number_format($reqInfo->costTotal,2),
			'config'=>array('class'=>'item-part subfooter')
		);
	} else {
		//$tables->rows[]=array('<td colspan="8" align="center">ไม่มีรายการ</td>');
	}

	$ret.=$tables->build();
	$ret.='</form>';

	//$ret.=print_o($jobInfo,'$jobInfo');
	$ret.='<script type="text/javascript">$("#refname").focus();</script>';
	if (!$isEdit) {
		$ret.='<style type="text/css">
		.item.-garage-job-tran thead {display:none;}
		</style>';
	}


	return $ret;
}


?>