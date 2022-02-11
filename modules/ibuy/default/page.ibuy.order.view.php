<?php
/**
* Module Method
* Created 2019-08-01
* Modify  2019-08-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function ibuy_order_view($self, $orderInfo) {
	$orderId = $orderInfo->oid;

	$isEdit = user_access('administer ibuys');
	$isAccess = $isEdit || (i()->ok && i()->uid == $orderInfo->info->uid);

	if (!$isAccess) return message('error','Access denied');

	$ret = '';

	$ret .= '<p class="notify">รายการสั่งซื้อสินค้าของ <strong>'.$orderInfo->info->name.'</strong> หมายเลขใบสั่งซื้อ <strong>'.$orderInfo->info->orderno.'</strong> เมื่อวันที่ <strong>'.sg_date($orderInfo->info->orderdate,'ว ดด ปปปป H:i').' น.</strong> อยู่ในสถานะ <strong>'.ibuy_define::status_text($orderInfo->info->status).'</strong></p>';


	// Change order status
	if ($isEdit) {
		$tabs_process = '<ul class="tabs-process">';
		foreach (ibuy_define::status_text() as $status_key=>$status_text) {
			if ($status_key == 20) continue;
			$tabs_process .= '<li><span>'.$status_text.'</span><input type="radio" name="status" value="'.$status_key.'" '.($status_key == $orderInfo->info->status?'checked="checked"':'').'/></li>';
		}
		$tabs_process.='</ul>';

		$form = new Form(NULL, url('ibuy/order/'.$orderId.'/status.update'), 'ibuy-confirm', 'sg-form -no-print');
		$form->addData('rel', 'none');
		$form->addData('done', 'notify: บันทึกสถานะเรียบร้อย | load');

		$form->addText($tabs_process);

		$form->addField(
						'confirm',
						array(
							'type' => 'button',
							'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
							'pretext' => 'บันทึกข้อความ : <input type="text" name="message" size="50" maxlength="140" class="form-text" />&nbsp;&nbsp;',
							'posttext' => ' <a class="btn" href="'.url('ibuy/admin/orderform/'.$orderId).'"><i class="icon -material">print</i><span>พิมพ์ใบสั่งสินค้า</span></a> <a class="btn -link" href="'.url('ibuy/status/monitor').'"><i class="icon -material">keyboard_arrow_left</i><span>Back to monitor</span></a>',
						)
					);

		$ret .= $form->build();
	}



	$ret .= '<div class="ibuy-order-header sg-inline-edit -sg-flex" data-update-url="'.url('ibuy/admin/update').'">';

	$ret .= '<div class="ibuy-order-description"><p>ใบสั่งซื้อหมายเลข : <strong>'.$orderInfo->info->orderno.'</strong><br />ร้าน : <strong>'.$orderInfo->info->custname.'</strong> สั่งโดย : <strong>'.$orderInfo->info->name.'</strong><br />ที่อยู่ : <strong>'.$orderInfo->info->custaddress.'</strong> <strong>'.$orderInfo->info->custzip.'</strong><br />ชื่อผู้ติดต่อ : <strong>'.$orderInfo->info->custattn.'</strong> โทรศัพท์ : <strong>'.$orderInfo->info->custphone.'</strong><br />ส่งสินค้าทาง : <strong>'.SG\getFirst($orderInfo->info->shipto,$orderInfo->info->shippingby).'</strong></p></div>'._NL;

	$ret .= '<div class="ibuy-order-remark">';
	$ret .= '<p><strong>หมายเลข EMS/เลขที่ส่งของ : </strong>'.view::inlineedit(array('group'=>'order','fld'=>'emscode','tr'=>$orderId),$orderInfo->info->emscode,$isEdit).'<br />วันที่ส่งของ '.view::inlineedit(array('group'=>'order','fld'=>'emsdate','tr'=>$orderId,'ret'=>'date:ว ดดด ปปปป', 'datetype'=>'bigint'),$orderInfo->info->emsdate,$isEdit,'datepicker').'</p>';

	$ret .= '<p><strong>หมายเหตุ : </strong></p>'.view::inlineedit(array('group'=>'order','fld'=>'remark','tr'=>$orderId,'ret'=>'html','button'=>'บันทึก'),$orderInfo->info->remark,$isEdit,'textarea').'';
	$ret .= '</div>'._NL;

	$ret .= '</div><!-- ibuy-order-header -->';

	$tables = new Table();
	$tables->caption = 'รายการสินค้า';
	$tables->header = array('no'=>'ลำดับ','รหัส','detail'=>'รายการ','amt'=>'จำนวน','money price'=>'ราคา','money discount'=>'ส่วนลด','money total'=>'รวมเงิน','');

	foreach ($orderInfo->trans as $rs) {
		$ui = new Ui();
		if ($isEdit) {
			$ui->add('<a class="sg-action" href="'.url('ibuy/'.$rs->tpid).'" data-rel="box" data-width="640">รายละเอียดสินค้า</a>');
			$ui->add('<a class="sg-action" href="'.url('ibuy/admin/orderform/'.$orderId,array('act'=>'edittr','trid'=>$rs->otrid)).'" data-rel="box" data-width="640" title="แก้ไข">แก้ไข</a>');
			$ui->add('<a class="sg-action" href="'.url('ibuy/admin/orderform/'.$orderId,array('act'=>'cleartr','trid'=>$rs->otrid)).'" data-confirm="คุณต้องการยกเลิกการสั่งสินค้ารายการนี้ แน่ใจหรือไม่ กรุณายืนยัน?" data-rel="#main" data-ret="'.url('ibuy/order/'.$orderId).'">ยกเลิกรายการ</a>');
			$ui->add('<a class="sg-action" href="'.url('ibuy/admin/orderform/'.$orderId,array('act'=>'removetr','trid'=>$rs->otrid)).'" data-confirm="คุณต้องการลบรายการนี้ แน่ใจหรือไม่ กรุณายืนยัน?" data-rel="#main" data-ret="'.url('ibuy/order/'.$orderId).'">ลบรายการ</a>');
		}
		$menu = '<nav>'.($ui->count() ? sg_dropbox($ui->build()) : '').'</nav>';

		$tables->rows[] = array(
			++$no,
			$rs->tpid,
			'<a class="sg-action" href="'.url('ibuy/'.$rs->tpid).'" data-rel="box" data-width="640">'.SG\getFirst($rs->description,$rs->title).'</a>',
			$rs->amt,
			number_format($rs->price,2),
			number_format($rs->discount,2),
			number_format($rs->total,2),
			$menu,
		);
		$total += $rs->amt * $rs->price;
	}

	if ($isEdit) {
		$tables->rows[]=array('<td></td>','','<a class="sg-action btn -link" href="'.url('ibuy/admin/orderform/'.$orderId,array('act'=>'addtr')).'" data-rel="box" data-width="640"><i class="icon -material">add_circle</i><span>เพิ่มสินค้า</span></a>','','','','','');
	}

	$tables->rows[]=array('<td></td>','','รวม','','','',number_format($orderInfo->info->subtotal,2),'');
	$tables->rows[]=array('<td></td>','','ส่วนลด','','','',($orderInfo->info->discount > 0 ? '-' : '').number_format($orderInfo->info->discount,2),'');

	$tables->rows[]=array(
		'<td></td>',
		'',
		'ค่าขนส่ง',
		'',
		'',
		'',
		number_format($orderInfo->info->shipping,2),
		$isEdit ? '<a class="sg-action" href="'.url('ibuy/order/'.$orderId.'/transport.add').'" data-rel="box" data-width="480"><i class="icon -material">add_circle_outline</i></a>' : '',
	);

	$tables->rows[]=array('<td></td>','','รวมทั้งสิ้น','','','','<big>'.number_format($orderInfo->info->total,2).'</big>','');


	$ret .= '<div class="ibuy-status-order">'.$tables->build().'</div>';



	// Show order status list

	$no = 0;

	$tables = new Table();
	$tables->caption = 'บันทึกสถานะ';
	$tables->header = array('no' => 'ลำดับ','โดย','date' => 'วันที่');
	foreach ($orderInfo->logs as $lrs) {
		$tables->rows[] = array(
			++$no,
			$lrs->name,
			date('Y-m-d H:i',$lrs->created),
			'config'=>array('class'=>'status-'.$lrs->status)
		);

		$tables->rows[]=array(
			'<td></td>',
			'<td colspan="3">'.$lrs->detail.'</td>',
			'config'=>array('class'=>'status-'.$lrs->status)
		);
	}
	$ret .= '<div class="ibuy-status-log">'.$tables->build().'</div>';

	return $ret;
}
?>