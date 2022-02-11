<?php
/**
* Garage : Job Transaction
* Created 2020-10-08
* Modify  2020-10-08
*
* @param Object $self
* @param Int $var
* @return String
*
* @usage module/{$Id}/method
*/

$debug = true;

function garage_job_tran($self, $jobInfo, $action = NULL, $tranId = NULL) {
	$jobId = $jobInfo->tpid;
	$isEdit = $jobInfo->is->editable;

	$ret = '<section id="garage-job-tran" class="garage-job -tran" data-url="'.url('garage/job/'.$jobId.'/tran').'">';

	$jobTranInfo = NULL;
	if ($action == 'edit' && !empty($tranId)) {
		$jobTranInfo = R::Model('garage.job.tr.get',$jobId,$tranId);
		$ret .= '<script type="text/javascript">currentRepairInfo={"priceA":'.$jobTranInfo->priceA.',"priceB":'.$jobTranInfo->priceB.',"priceC":'.$jobTranInfo->priceC.',"priceD":'.$jobTranInfo->priceD.'}</script>';
		//$ret.=print_o($jobTranInfo,'$jobTranInfo');
	}

	if (empty($jobTranInfo->datecmd)) $jobTranInfo->datecmd = date('Y-m-d');
	if (empty($jobTranInfo->qty)) $jobTranInfo->qty = 1;

	$ret .= '<form id="garage-job-tr-new" class="sg-form" method="post" action="'.url('garage/job/'.$jobInfo->tpid.'/info/tran.save/'.$tranId).'" data-checkvalid="true" data-rel="notify" data-done="load->replace:#garage-job-tran">'._NL;
	$ret.='<input type="hidden" name="trid" value="'.$jobTranInfo->jobtrid.'" />'._NL;

	$tables = new Table();
	$tables->addClass('-garage-job-tran'.($action?' -'.$action:''));
	$tables->thead = array(
		'date'=>'วันที่',
		'code -nowrap'=>'รหัส',
		'รายการ',
		'center'=>'ระดับ',
		'amt'=>'จำนวน',
		'price -money'=>'ราคา',
		'total -money'=>'จำนวนเงิน',
		'icons -hover-parent'=>'<a class="-no-print" href="javascript:viod(0)" onClick="$(\'.item-repair\').hide();$(this).closest(\'tr\').hide();return false;" title="คลิกเพื่อปิด"><i class="icon -material">visibility</i></a>'
	);

	$damagecodeList = mydb::select('SELECT * FROM %garage_damage%')->items;
	$damagecodeOptions = '<option value="">???</option>';
	foreach ($damagecodeList as $v) {
		$damagecodeOptions .= '<option value="'.$v->damagecode.'" '.($jobTranInfo->damagecode == $v->damagecode?'selected="selected"':'').' data-pretext="'.$v->pretext.'">'.$v->damagecode.' : '.$v->damagename.'</option>';
	}

	if ($isEdit) {
		$tables->rows[] = array(
			'<input class="form-text sg-datepicker -fill" type="text" name="datecmd" value="'.sg_date($jobTranInfo->datecmd,'d/m/Y').'" size="5" />',
			'<input id="repairid" type="hidden" name="repairid" value="'.$jobTranInfo->repairid.'" />'
			.'<input id="repaircode" class="form-text sg-autocomplete -fill -require" type="text" name="repaircode" value="'.$jobTranInfo->repaircode.'" placeholder="รหัสสั่งซ่อม-อะไหล่" size="5" data-query="'.url('garage/api/repaircode').'" data-item="20" data-altfld="repairid" data-select=\'{"repaircode":"code","repairname":"name","price":"priceA"}\' data-select-name="repairname" data-callback="garageRepairCodeSelect" data-class="-repaircode" data-width="400" />',
			'<input id="repairname" class="form-text -fill" type="text" name="repairname" value="'.$jobTranInfo->repairname.'" placeholder="รายละเอียด" />',
			'<select id="damagecode" class="form-select -damagecode -fill" name="damagecode">'.$damagecodeOptions.'</select>',
			'<input id="qty" class="form-text -fill -numeric" type="text" name="qty" value="'.$jobTranInfo->qty.'" placeholder="0" size="1" />',
			'<input id="price" class="form-text -fill -money" type="text" name="price" value="'.$jobTranInfo->price.'" placeholder="0.00" size="4" />',
			'<input id="totalsale" class="form-text -fill -money" type="text" name="totalsale" value="'.$jobTranInfo->totalsale.'" placeholder="0.00" size="5" readonly="readonly" />',
			'',
			'config'=>array('class'=>'-input -no-print'),
		);

		$tables->rows[] = array(
			'<td colspan="6"></td>',
			/*
			'<td colspan="2" align="right">'
			.'<span class="inputblock">[ ส่วนลด <input class="form-text -numeric" type="text" name="discountrate" value="" placeholder="0" size="1" /> % '
			.'<input class="form-text -numeric" type="text" name="discountamt" value="" placeholder="0.00" size="6" /> บาท ]</span>'
			.'<span class="inputblock">[ VAT <input class="form-text -numeric" type="text" name="vatrate" value="" placeholder="0" size="1" /> % '.
			'<input class="form-text -numeric" type="text" name="vatamount" value="" placeholder="0.00" size="6" /> บาท ]</span>'
			.'<span class="inputblock">[ ส่วนเพิ่ม <input class="form-text -numeric" type="text" name="incrate" value="" placeholder="0" size="1" /> % '
			.'<input class="form-text -numeric" type="text" name="incprice" value="" placeholder="0.00" size="6" /> บาท ]</span>'
			.'</td>',
			*/
			'<td align="center"><button class="btn -primary" type="submit" style="white-space:nowrap"><i class="icon -save -white"></i><span>บันทึก'.($action=='edit'?'แก้ไข':'รายการ').'</span></button>'
			.($action=='edit'?'<br /><a class="sg-action" href="'.url('garage/job/'.$jobId.'/tran').'" data-rel="#garage-job-tran">ยกเลิกแก้ไข</a>':'')
			.'</td>',
			'',
			'config'=>array('class'=>'-input -no-print'),
		);
	}




	// Display transaction

	if (1||empty($action) || $action=='repair') {
		if ($jobInfo->command) {
			$tables->rows[]=array('<th colspan="8">รายการสั่งซ่อม</th>','config'=>array('class'=>'item-repair'));
			$tables->rows[]='<header>';
			foreach ($jobInfo->command as $rs) {
				$menu='';
				if ($isEdit) {
					$dropUi=new Ui(NULL,'ui-nav');
					$dropUi->add('<a class="sg-action" href="'.url('garage/job/'.$jobId.'/tran/edit/'.$rs->jobtrid).'" data-rel="replace:#garage-job-tran" data-done="modeto: 0,0" data-callback="garageJobTranEditClick"><i class="icon -edit"></i><span>แก้ไขรายการ</span></a>');
					$dropUi->add('<a class="sg-action" href="'.url('garage/job/'.$jobId.'/info/tran.delete/'.$rs->jobtrid).'" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-rel="none" data-removeparent="tr"><i class="icon -cancel"></i><span>ลบรายการ</span></a>');
					$ui=new Ui('span');
					$ui->add('<a class="sg-action" href="'.url('garage/job/'.$jobId.'/tran/edit/'.$rs->jobtrid).'" data-rel="replace:#garage-job-tran" data-callback="garageJobTranEditClick"><i class="icon -edit"></i></a>');
					$ui->add('<a class="sg-action" href="'.url('garage/job/'.$jobId.'/info/tran.moveup/'.$rs->jobtrid).'" data-rel="notify" data-done="load->replace:#garage-job-tran"><i class="icon -up"></i></a>');
					$ui->add('<a class="sg-action" href="'.url('garage/job/'.$jobId.'/info/tran.movedown/'.$rs->jobtrid).'" data-rel="notify" data-done="load->replace:#garage-job-tran"><i class="icon -down"></i></a>');
					$menu .= '<nav class="nav iconset -hover -no-print">'
						.$ui->build()
						.sg_dropbox($dropUi->build())
						.'</nav>';
				}

				$config=array();
				$config['class']='item-repair';
				if ($tranId && $tranId==$rs->jobtrid) $config['class'].=' -highlight';
				$tables->rows[] = array(
					sg_date($rs->datecmd,'d/m/ปปปป'),
					$rs->repaircode,
					$rs->repairname,
					$rs->damagecode,
					number_format($rs->qty),
					number_format($rs->price,2),
					number_format($rs->totalsale,2)
					,$menu,
					'config'=>$config,
				);
			}
			$tables->rows[]=array('','','รวมค่าแรง','','','',number_format($jobInfo->totalservice,2),'','config'=>array('class'=>'item-repair subfooter'));
		} else {
			$tables->rows[]=array('<th colspan="8">รายการสั่งซ่อม</th>');
			$tables->rows[]=array('<td colspan="8" align="center">ไม่มีรายการ</td>');
		}
	}


	// Show part
	if (1||empty($action) || $action == 'part') {
		$tables->rows[] = array(
			'<td colspan="8"></td>',
			'config'=>array('class'=>'item-part')
		);

		if ($jobInfo->part) {
			$tables->rows[]=array('<th colspan="8">รายการอะไหล่</th>','config'=>array('class'=>'item-part'));
			//$tables->rows[]='<header>';
			$tables->rows[]=array('<th>วันที่</th>','<th>รหัส</th>','<th>รายการ</th>','<th></th>','<th>จำนวน</th>','<th>ราคา</th>','<th>จำนวนเงิน</th>','icons -hover-parent'=>'<th><a class="-no-print" href="javascript:viod(0)" onClick="$(\'.item-part\').hide();$(this).closest(\'tr\').hide();return false;" title="คลิกเพื่อปิด"><i class="icon -material">visibility</i></a></th>');

			foreach ($jobInfo->part as $rs) {
				$menu='';
				if ($isEdit) {
					$dropUi = new Ui(NULL,'ui-nav');
					$dropUi->add('<a class="sg-action" href="'.url('garage/job/'.$jobId.'/tran/edit/'.$rs->jobtrid).'" data-rel="replace:#garage-job-tran" data-callback="garageJobTranEditClick"><i class="icon -edit"></i><span>แก้ไขรายการ</span></a>');
					$dropUi->add('<a class="sg-action" href="'.url('garage/job/'.$jobId.'/info/tran.delete/'.$rs->jobtrid).'" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-rel="none" data-removeparent="tr"><i class="icon -delete"></i><span>ลบรายการ</span></a>');

					$ui=new Ui('span');
					$ui->add('<a class="sg-action" href="'.url('garage/job/'.$jobId.'/tran/edit/'.$rs->jobtrid).'" data-rel="replace:#garage-job-tran" data-callback="garageJobTranEditClick"><i class="icon -edit"></i></a>');
					$ui->add('<a class="sg-action" href="'.url('garage/job/'.$jobId.'/info/tran.moveup/'.$rs->jobtrid).'" data-rel="notify" data-done="load->replace:#garage-job-tran"><i class="icon -up"></i></a>');
					$ui->add('<a class="sg-action" href="'.url('garage/job/'.$jobId.'/info/tran.movedown/'.$rs->jobtrid).'" data-rel="notify" data-done="load->replace:#garage-job-tran"><i class="icon -down"></i></a>');
					$ui->add('<a class="sg-action -wait" href="'.url('garage/job/'.$jobId.'/info/part.wait/'.$rs->jobtrid).'" data-rel="notify" data-done="load->replace:#garage-job-tran"><i class="icon -material">watch_later</i></a>');
					$menu .= '<nav class="nav iconset -hover -no-print">'
						.$ui->build()
						.sg_dropbox($dropUi->build())
						.'</nav>';
				}

				$config['class'] = 'item-part'.($rs->wait ? ' -wait' : '');

				if ($tranId && $tranId==$rs->jobtrid) $config['class'].=' -highlight';
				$tables->rows[] = array(
					sg_date($rs->datecmd,'d/m/ปปปป'),
					$rs->repaircode,
					$rs->repairname,
					'',
					number_format($rs->qty),
					number_format($rs->price,2),
					number_format($rs->totalsale,2),
					$menu,
					'config'=>$config,
				);
			}
			$tables->rows[]=array('','','รวมค่าอะไหล่','','','',number_format($jobInfo->totalpart,2),'','config'=>array('class'=>'item-part subfooter'));
		} else {
			$tables->rows[]=array('<th colspan="8">รายการอะไหล่</th>','config'=>array('class'=>'item-part'));
			$tables->rows[]=array('<td colspan="8" align="center">ไม่มีรายการ</td>');
		}
	}

	if (1||empty($action) || $action=='wage') {
		$tables->rows[]=array('<td colspan="8"></td>','config'=>array('class'=>'item-wage'));
		if ($jobInfo->wage) {
			$tables->rows[]=array('<th colspan="8">รายการค่าแรงอื่น ๆ</th>','config'=>array('class'=>'item-wage'));
			//$tables->rows[]='<header>';
			$tables->rows[]=array('<th>วันที่</th>','<th>รหัส</th>','<th>รายการ</th>','<th></th>','<th>จำนวน</th>','<th>ราคา</th>','<th>จำนวนเงิน</th>','icons -hover-parent'=>'<th><a href="javascript:viod(0)" onClick="$(\'.item-wage\').hide();$(this).closest(\'tr\').hide();return false;" title="คลิกเพื่อปิด"><i class="icon -material">visibility</i></a></th>');

			foreach ($jobInfo->wage as $rs) {
				$menu='';
				if ($isEdit) {
					$dropUi=new Ui(NULL,'ui-nav');
					$dropUi->add('<a class="sg-action" href="'.url('garage/job/'.$jobId.'/tran/edit/'.$rs->jobtrid).'" data-rel="replace:#garage-job-tran" data-callback="garageJobTranEditClick"><i class="icon -edit"></i><span>แก้ไขรายการ</span></a>');
					$dropUi->add('<sep>');
					$dropUi->add('<a class="sg-action" href="'.url('garage/job/'.$jobId.'/info/tran.delete/'.$rs->jobtrid).'" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-rel="none" data-removeparent="tr"><i class="icon -delete"></i><span>ลบรายการ</span></a>');

					$ui=new Ui('span');
					$ui->add('<a class="sg-action" href="'.url('garage/job/'.$jobId.'/tran/edit/'.$rs->jobtrid).'" data-rel="replace:#garage-job-tran" data-callback="garageJobTranEditClick"><i class="icon -edit"></i></a>');
					$ui->add('<a class="sg-action" href="'.url('garage/job/'.$jobId.'/info/tran.moveup/'.$rs->jobtrid).'" data-rel="notify" data-done="load->replace:#garage-job-tran"><i class="icon -up"></i></a>');
					$ui->add('<a class="sg-action" href="'.url('garage/job/'.$jobId.'/info/tran.movedown/'.$rs->jobtrid).'" data-rel="notify" data-done="load->replace:#garage-job-tran"><i class="icon -down"></i></a>');
					$ui->add('<a class="sg-action" href="'.url('garage/job/'.$jobId.'/info/tran.delete/'.$rs->jobtrid).'" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?" data-rel="notify" data-done="load->replace:#garage-job-tran"><i class="icon -cancel"></i></a>');

					$menu.='<nav class="nav iconset -hover -no-print">'
						.$ui->build()
						//.sg_dropbox($dropUi->build())
						.'</nav>';
				}

				$config['class']='item-wage';
				if ($tranId && $tranId==$rs->jobtrid) $config['class'].=' -highlight';
				$tables->rows[] = array(
					sg_date($rs->datecmd,'d/m/ปปปป'),
					$rs->repaircode,
					$rs->repairname,
					'',
					number_format($rs->qty),
					number_format($rs->price,2),
					number_format($rs->totalsale,2),
					$menu,
					'config'=>$config,
				);
			}
			$tables->rows[]=array('','','รวมค่าแรงอื่น ๆ','','','',number_format($jobInfo->totalWage,2),'','config'=>array('class'=>'item-wage subfooter'));
		} else {
			$tables->rows[]=array('<th colspan="8">รายการค่าแรง</th>','config'=>array('class'=>'item-wage'));
			$tables->rows[]=array('<td colspan="8" align="center">ไม่มีรายการค่าแรง</td>');
		}
	}

	$ret.=$tables->build();
	$ret.='</form>';

	//$ret.=print_o($jobInfo,'$jobInfo');
	if (!$isEdit) {
		$ret.='<style type="text/css">
		.item.-garage-job-tran thead {display:none;}
		</style>';
	}

	$ret .= '</section><!-- garage-job-tran -->';
	return $ret;
}
?>