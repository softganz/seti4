<?php
/**
 * Print order form
 *
 * @param Integer $oid
 * @return String
 */
function ibuy_admin_orderform($self,$oid) {
	$self->theme->title='ใบสั่งสินค้า';
	$self->theme->sidebar=R::Page('ibuy.admin.menu');

	$action=post('act');

	$stmt='SELECT o.*, f.`custname`, u.`name`, f.`custaddress`, f.`custzip`,
					f.`custphone`, f.`custattn`, f.`shippingby`
				FROM %ibuy_order% o
					LEFT JOIN %users% u ON u.uid=o.uid
					LEFT JOIN %ibuy_customer% f ON f.uid=o.uid
				WHERE oid=:oid ORDER BY oid DESC LIMIT 1';
	$order=mydb::select($stmt,':oid',$oid);

	$ui=new Ui(NULL,'ui-nav');
	$ui->add('<a class="btn" href="'.url('ibuy/admin/orderform/'.$oid).'">ใบสั่งสินค้า</a>');
	$ui->add('<a class="btn" href="'.url('ibuy/admin/orderform/'.$oid,array('act'=>'label')).'">จ่าหน้าซอง</a>');
	$ui->add('<a class="btn" href="'.url('ibuy/order/'.$oid).'">สถานะใบสั่งสินค้า</a>');
	$ui->add('<a class="btn -primary" href="javascript:window.print()"><i class="icon -print -white"></i><span>พิมพ์</span></a>');
	$navbar.='<nav class="nav -page -no-print"><header class="header -hidden"><h3>ใบสั่งสินค้า</h3></header>'._NL.$ui->build().'</nav>';
	$self->theme->navbar=$navbar;

	switch ($action) {
		case 'label' :
			$self->theme->title='&nbsp;';
			$ret.='<div class="ibuy__customerlabel">';
			$ret.='<p class="ibuy__customerlabel--sendto"><strong>กรุณาส่ง<br /><br />'.$order->custname.'<br />'.$order->custattn.'<br />'.$order->custaddress.' '.$order->custzip.'<br />โทร '.$order->custphone.'</strong>'.($order->shipto?'<br />('.$order->shipto.')':'').'</p>';
			$ret.='<p class="ibuy__customerlabel--sender"><strong>จาก<br />'.cfg('ibuy.shop.address').'</strong></p>';
			$ret.='</div>'._NL;
			return $ret;
			break;
		case 'cleartr' :
			$trid=post('trid');
			if ($trid) {
				mydb::query('UPDATE %ibuy_ordertr% SET `amt`=0, `subtotal`=0, `total`=0, `discount`=0, `leveldiscount`=0, `marketvalue`=0 WHERE `otrid`=:trid LIMIT 1',':trid',$trid);
				$order=ibuy_model::calculate_order($oid);
				mydb::query('UPDATE %ibuy_order% SET `subtotal`=:subtotal, `discount`=:discount, `total`=:total, `leveldiscount`=:leveldiscount, `marketvalue`=:marketvalue, `franchisorvalue`=:franchisorvalue WHERE `oid`=:oid LIMIT 1',$order);
			}
			break;
		case 'removetr' :
			$trid=post('trid');
			if ($trid) {
				mydb::query('DELETE FROM %ibuy_ordertr% WHERE `otrid`=:trid LIMIT 1',':trid',$trid);
				$order=ibuy_model::calculate_order($oid);
				mydb::query('UPDATE %ibuy_order% SET `subtotal`=:subtotal, `discount`=:discount, `total`=:total, `leveldiscount`=:leveldiscount, `marketvalue`=:marketvalue, `franchisorvalue`=:franchisorvalue WHERE `oid`=:oid LIMIT 1',$order);
			}
			break;

		case 'edittr' :
			$trid=post('trid');
			if ($trid>=0) {
				$post=post('tr');
				if ($post) {
					$post['otrid']=$trid>0?$trid:NULL;
					$post['oid']=$oid;
					$stmt='INSERT INTO %ibuy_ordertr%
								(`otrid`,`oid`,`tpid`,`amt`,`price`,`subtotal`,`discount`,`total`,`leveldiscount`,`marketvalue`)
								VALUE
								(:otrid,:oid,:tpid,:amt,:price,:subtotal,:discount,:total,:leveldiscount,:marketvalue)
								ON DUPLICATE KEY UPDATE `tpid`=:tpid,`amt`=:amt,`price`=:price,`subtotal`=:subtotal,`discount`=:discount,`total`=:total,`leveldiscount`=:leveldiscount,`marketvalue`=:marketvalue';
					mydb::query($stmt,$post);
					//$ret.=mydb()->_query;
					$order=ibuy_model::calculate_order($oid);
					mydb::query('UPDATE %ibuy_order% SET `subtotal`=:subtotal, `discount`=:discount, `total`=:total, `leveldiscount`=:leveldiscount, `marketvalue`=:marketvalue, `franchisorvalue`=:franchisorvalue WHERE `oid`=:oid LIMIT 1',$order);
					//$ret.=print_o($post,'$post');
					location('ibuy/order/'.$oid);
				}
				$ret.=__ibuy_admin_orderform_form($trid);
			}
			//$ret.=print_o(post(),'$post');
			return $ret;
			break;

	case 'addtr' :
		$ret.=__ibuy_admin_orderform_form(0,$oid);
		return $ret;
		break;

	}

	$ret.='<h1 class="web--title">'.cfg('web.title').'</h1>';

	$stmt='SELECT o.*,t.title FROM %ibuy_ordertr% o LEFT JOIN %topic% t ON t.tpid=o.tpid WHERE o.oid=:oid ORDER BY t.title ASC';
	$ordertr=mydb::select($stmt,':oid',$oid);

	$ret.='<div class="ibuy__orderform--header"><p>เลขที่ใบสั่งสินค้า: <strong>'.$order->orderno.'</strong><span class="orderdate"> วันที่: <strong>'.sg_date($order->orderdate,'ว ดดด ปปปป H:i').'</strong></span><br />วิธีการจัดส่ง: <strong>'.SG\getFirst($order->shipto,$order->shippingby).'</strong><br />ที่อยู่ในการจัดส่ง<br /><strong>'.$order->custname.'</strong><br /><strong>'.$order->custattn.'</strong> (โทร '.$order->custphone.')<br /><strong>'.$order->custaddress.' '.$order->custzip.'</strong></div>'._NL;


	$tables = new Table();
	$tables->addClass('ibuy__orderform--tr');
	$tables->header=array('amt'=>'จำนวน','รหัสสินค้า','detail'=>'รายการ','money price'=>'ราคา','money total'=>'รวมเงิน');
	foreach ($ordertr->items as $trrs) {
		$tables->rows[]=array(
											$trrs->amt,
											$trrs->tpid,
											SG\getFirst($trrs->description,$trrs->title),
											number_format($trrs->price,2),
											number_format($trrs->total,2)
											);
		$total+=$trrs->amt*$trrs->price;
	}
	$tables->rows[]=array('','','รวม','',number_format($order->subtotal,2));
	$tables->rows[]=array('','','ส่วนลด','',($order->discount>0?'-':'').number_format($order->discount,2));
	$tables->rows[]=array('','','ค่าขนส่ง','',number_format($order->shipping,2));
	$tables->rows[]=array('','','รวมทั้งสิ้น','','<big>'.number_format($order->total,2).'</big>');

	$ret .= $tables->build();

	$ret.='<div class="ibuy__orderform--remark"><strong>หมายเหตุ:</strong>'.sg_text2html($order->remark).'</div>';
	$ret.='<div class="ibuy__orderform--footer">'.cfg('ibuy.orderform.footer').'</div>';
	return $ret;
}

function __ibuy_admin_orderform_form($trid, $oid = NULL) {
	$ret = '<header class="header -box"><h3 class="title">แก้ไขรายการ</h3></header>';
	$rs=mydb::select('SELECT tr.*, t.`title` FROM %ibuy_ordertr% tr LEFT JOIN %topic% t USING(`tpid`) WHERE `otrid`=:trid LIMIT 1',':trid',$trid);

	$form = new Form('tr', url('ibuy/admin/orderform/'.SG\getFirst($rs->oid,$oid)), 'ibuy-edittr', 'sg-form');
	$form->config->attr='data-rel="#main" data-done="close"';

	$form->act=array('type'=>'hidden','name'=>'act','value'=>'edittr');
	$form->trid=array('type'=>'hidden','name'=>'trid','value'=>$trid);

	$form->tpid=array('type'=>'text','label'=>'รหัสสินค้า','value'=>$rs->tpid);
	$form->addField(
		'title',
		array(
			'type' => 'text',
			'label' => 'สินค้า',
			'class' => '-fill',
			'value' => htmlspecialchars($rs->title),
		)
	);

	$form->amt=array('type'=>'text','label'=>'จำนวน','value'=>$rs->amt);
	$form->price=array('type'=>'text','label'=>'ราคา','value'=>$rs->price);
	$form->subtotal=array('type'=>'text','label'=>'รวมเงิน','value'=>$rs->subtotal);
	$form->discount=array('type'=>'text','label'=>'ส่วนลด','value'=>$rs->discount);
	$form->total=array('type'=>'text','label'=>'รวมทั้งสิ้น','value'=>$rs->total);

	$form->leveldiscount=array('type'=>'text','label'=>'ราคาสำหรับคำนวณส่วนลดระดับราคา','value'=>$rs->leveldiscount);
	$form->marketvalue=array('type'=>'text','label'=>'ราคาสำหรับคำนวณส่วนลดค่าการตลาด','value'=>$rs->marketvalue);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done_all</i><span>{tr:SAVE}</span>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();


	//$ret.=print_o($rs,'$rs');
	return $ret;
}
?>