<?php
/**
 * Proceed shoping cart to checkout
 *
 * Add product item into database and clear shoping cart
 * @return location order status page
 */
function ibuy_cart_proceed_quick($self) {
	$self->theme->title=tr('Proceed to checkout');
	$post=(object)post();
	$uid=i()->uid;
	$error=array();

	$simulate=debug('simulate');

	$cartinfo = R::Model('ibuy.cart.get');

	if (empty($cartinfo->amt)) $error[]='ไม่มีรายการสินค้าในตะกร้า';

	if ($error) return message('error',$error);

	if ($post->proceedcard) {
		if ($cartinfo->shipping && !$post->shipping) $error=message('error','กรุณายืนยันว่าท่านจะต้องชำระค่าขนส่งต้นทางด้วยตนเอง');
		if (!$error) {
			$order->uid=$uid;
			$order->orderdate=date('U');
			$order->subtotal=$cartinfo->subtotal;
			$order->shipping=$cartinfo->shipping;
			$order->remark=strip_tags(post('remark'));

			// calculate next order no
			$stmt='SELECT `value` `lastNo` FROM %variable% WHERE `name`="ibuy.lastorderno" LIMIT 1';
			$lastNo=mydb::select($stmt)->lastNo;
			$digit=cfg('ibuy.orderdigit');
			$orderSep=cfg('ibuy.ordersep');
			if ($orderSep) {
				list($orderYear,$orderMonth,$orderNo)=explode($orderSep,$lastNo);
			} else {
				$orderYear=substr($lastNo,0,2);
				$orderMonth=substr($lastNo,2,2);
				$orderNo=substr($lastNo,4);
			}
			$currentYear=substr(date('Y')+543,2);
			if ($orderYear!= $currentYear) {
				$orderYear=substr(date('Y')+543,2);
				$orderNo=0;
			}
			if ($orderMonth!= date('m')) {
				$orderMonth=date('m');
				$orderNo=0;
			}
			//$ret.='lastNo='.$lastNo.'<br />';

			$nextNo=$orderNo+1;

			// Increment digit if next order no greater than current digit
			if ($nextNo>=pow(10,$digit)) {
				$digit++;
				cfg_db('ibuy.orderdigit',(string) $digit);
			}

			// Generate next order no
			if ($orderSep) {
				$nextOrderNo=$orderYear.$orderSep.$orderMonth.$orderSep.sprintf('%0'.$digit.'d',$nextNo);
			} else {
				$nextOrderNo=$orderYear.$orderMonth.sprintf('%0'.$digit.'d',$nextNo);
			}
			//$ret.='nextOrderNo='.$nextOrderNo.'<br />';

			$order->orderno=$nextOrderNo;

			// Calculate discount
			$order->discount=0;
			if ($cartinfo->discount_summary>0 && $post->discount=='yes') {
			//$order->discount=$cartinfo->discount_summary<$order->subtotal?$cartinfo->discount_summary:$order->subtotal;
				$order->discount=$cartinfo->discount_summary<$cartinfo->discount_yes?$cartinfo->discount_summary:$cartinfo->discount_yes;
				if (!$simulate) mydb::query('UPDATE %ibuy_customer% SET `discount`=`discount`-:discount WHERE `uid`=:uid LIMIT 1',':discount',$order->discount,':uid',$order->uid);
			}
			$order->total=$order->balance=$cartinfo->total-$order->discount+$cartinfo->shipping;
			$order->leveldiscount=$cartinfo->leveldiscount-$order->discount;
			$order->marketvalue=$cartinfo->marketvalue-$order->discount;
			$order->franchisorvalue=$cartinfo->franchisorvalue;

			$order->shipcode=$post->shipcode;
			$order->shipto=strip_tags($post->shipto);
			if (empty($order->shipto) && $order->shipcode==14) $order->shipto='EMS ด่วนพิเศษ';
			else if (empty($order->shipto) && $order->shipcode==13) $order->shipto='ไปรษณีย์ลงทะเบียน';

			// Add order information to order
			$stmt='INSERT INTO %ibuy_order%
								(`uid`, `orderno`, `orderdate`, `subtotal`, `discount`, `shipping`, `total`, `leveldiscount`, `marketvalue`, `franchisorvalue`, `balance`, `shipcode`, `shipto`, `remark`)
							VALUES
								(:uid, :orderno, :orderdate, :subtotal, :discount, :shipping, :total, :leveldiscount, :marketvalue, :franchisorvalue, :balance, :shipcode, :shipto, :remark)';

			if ($simulate) {
				$ret.='<p>'.$stmt.'</p>';
				$ret.=print_o($order,'$order');
			} else {
				mydb::query($stmt,$order);
			}
			//$ret.=mydb()->_query;
			if (mydb()->_error) return $ret.message('error','เกิดความผิดพลาดในกระบวนการบันทึกข้อมูลการซื้อสินค้า กรุณาติดต่อผู้ดูแลระบบ');

			// Add order transaction to ordertr
			$order->oid=$ordertr->oid=mydb()->insert_id;

			// Update last order no
			cfg_db('ibuy.lastorderno',$nextOrderNo);

			foreach ($cartinfo->items as $rs) {
				$ordertr->tpid=is_numeric($rs->tpid)?$rs->tpid:0;
				$ordertr->amt=$rs->amt;
				$ordertr->price=$rs->price;
				$ordertr->subtotal=$rs->subtotal;
				$ordertr->discount=$rs->discount;
				$ordertr->total=$rs->total;
				$ordertr->leveldiscount=$rs->leveldiscount;
				$ordertr->marketvalue=$rs->marketvalue;
				$stmt='INSERT INTO %ibuy_ordertr%
								(`oid` , `tpid` , `amt` , `price` , `subtotal` , `discount` , `total` , `leveldiscount` , `marketvalue`)
							VALUES
								(:oid , :tpid , :amt , :price , :subtotal , :discount , :total , :leveldiscount , :marketvalue)';
				if ($simulate) {
					$ret.='<p>'.$stmt.'</p>';
				} else {
					mydb::query($stmt,$ordertr);
					if (cfg('ibuy.stock.use')) mydb::query('UPDATE %ibuy_product% SET `balance`=`balance`-:amt WHERE `tpid`=:tpid LIMIT 1',':tpid',$rs->tpid,':amt',$rs->amt);
					if (i()->ok && $ordertr->tpid) R::Model('reaction.add',$ordertr->tpid,'IBUY.BUY');
				}
			}

			if (!$simulate) {
				// Add discount to user discount
				if ($cartinfo->resalerdiscount) {
					mydb::query('UPDATE %ibuy_customer% SET `discount`=`discount`+:discount WHERE `uid`=:uid LIMIT 1',':discount',$cartinfo->resalerdiscount,':uid',$order->uid);
				}
				// Add to log file
				ibuy_model::log('keyword=order','kid='.$order->oid,'status=0','created='.$order->orderdate,'detail=บันทึกการสั่งซื้อสินค้า','amt='.$order->total,'process=1');
				$_SESSION['message']=message('status','บันทึกรายการสั่งซื้อสินค้าเรียบร้อย');

				// Send mail to buyer and admin

				//$ret.=print_o($_POST,'$_POST').print_o($order,'$order');
				ibuy_model::empty_cart($uid);
				location('ibuy/status/order');
			}
		}
	}


	$ret.='กรุณายืนยันการสั่งซื้อสินค้าเพื่อทำการบันทึกข้อมูลการสั่งซื้อสินค้า หรือ ยกเลิกเพื่อกลับไปเลือกซื้อสินค้าเพิ่มเติม';

	$form=new Form('',url(q()),'ibuy-proceed','sg-form ibuy-proceed');
	$form->addData('checkValid',true);

	if ($cartinfo->discount_summary>0) {
		$alway_use_discount=0;//cfg('ibuy.alway_use_discount');
		$form->addField(
							'discount',
							array(
								'type'=>'checkbox',
								'name'=>'discount',
								'options'=>array('yes'=>'ต้องการใช้ส่วนลดในการสั่งซื้อสินค้าครั้งนี้'),
								'value'=>$alway_use_discount?'yes':'',
								'readonly'=>$alway_use_discount?'true':'',
								'attr'=>$alway_use_discount?'onclick="return false;"':'',
								'pretext'=>'<p>ท่านมียอดส่วนลดสะสมที่สามารถนำมาลดในการสั่งซื้อสินค้าครั้งนี้ จำนวน <strong>'.number_format($cartinfo->discount_summary,2).'</strong> บาท</p>'
								)
							);
		/*
		if (cfg('ibuy.alway_use_discount')) {
			$form->discount->value='yes';
			$form->discount->readonly=true;
			$form->discount->attr='onclick="return false;"';
		}
		*/
	}

	$form->addField(
						'remark',
						array(
							'type'=>'textarea',
							'name'=>'remark',
							'label'=>'หมายเหตุ หรือ สั่งสินค้าที่ไม่อยู่ในรายการสินค้า',
							'class'=>'-fill',
							'rows'=>3,
							'value'=>htmlspecialchars($post->remark),
								'description'=>'ท่านสามารถเขียนหมายเหตุหรือสั่งซื้อสินค้าที่ไม่อยู่ในรายการสินค้าในช่องด้านบน'
							)
						);

	$form->addField(
						'shipcode',
						array(
							'label'=>'วิธีการส่งสินค้า:',
							'type'=>'radio',
							'name'=>'shipcode',
							'options'=>array('14'=>'EMS ด่วนพิเศษ', '13'=>'ไปรษณีย์ลงทะเบียน', '12'=>'ขนส่งเอกชน: <input name="shipto" id="edit-shipto" class="form-text" value="'.htmlspecialchars($post->shipto).'" placeholder="ชื่อขนส่ง" type="text">'),
							'value'=>SG\getFirst($post->shipcode,'14')
							)
						);

	if ($cartinfo->shipping) {
		$form->addField(
						'shipping',
						array(
							'type'=>'checkbox',
							'label'=>'เงื่อนไขในการสั่งซื้อสินค้า:',
							'name'=>'shipping',
							'require'=>true,
							'options'=>array('yes'=>'<strong>ข้าพเจ้าขอยืนยันการสั่งซื้อสินค้า'.(cfg('ibuy.shipping.lower')==0?' และ':'ที่มียอดรวมน้อยกว่า <span style="color:red;">'.number_format(cfg('ibuy.shipping.lower'),2).' บาท</span>').' ข้าพเจ้าจะต้องจ่ายค่าขนส่งต้นทางเป็นเงิน <span style="color:red;">'.number_format(cfg('ibuy.shipping.price'),2).' บาท</span></strong>'),
							)
						);
	}
	if (user_access('buy ibuy product')) {
		$form->addField(
							'proceedcard',
							array(
								'type' => 'button',
								'name' => 'proceedcard',
								'value' => '<i class="icon -material">done_all</i><span>ยืนยันการสั่งซื้อ</span>',
								'pretext' => '<a class="btn -link -cancel" href="'.url('ibuy/product').'"><i class="icon -material -gray">keyboard_arrow_left</i><span>ซื้อสินค้าต่อ</span></a>',
								'container' => '{class: "-sg-text-right"}',
							)
						);
		$ret .= $form->build();
		$ret.=cfg('ibuy.message.proceed');
	}
	if ($error) $ret.=$error;

	$ret.=__ibuy_cart_proceed_cart($cartinfo);

	$tables = new Table();
	$tables->caption='รายละเอียดการสั่งซื้อสินค้า';
	$tables->thead=array('รายละเอียด','amt'=>'จำนวน','หน่วย');
	$tables->rows[]=array('จำนวนสินค้าทั้งสิ้น',$cartinfo->amt,'รายการ');
	$tables->rows[]=array('สินค้าที่สามารถลดราคาได้',number_format($cartinfo->discount_yes,2),'บาท');
	$tables->rows[]=array('สินค้าที่ไม่สามารถลดราคาได้',number_format($cartinfo->discount_no,2),'บาท');
	$tables->rows[]=array('รวมราคาสินค้าทั้งสิ้น','<strong><big>'.number_format($cartinfo->subtotal,2).'</big></strong>','บาท');
	if ($cartinfo->discount_summary>0 && cfg('ibuy.alway_use_discount')) {
		$discount=$cartinfo->discount_summary<$cartinfo->discount_yes?$cartinfo->discount_summary:$cartinfo->discount_yes;
	} else {
		$discount=0;
	}
	$tables->rows[]=array('หักส่วนลดสะสม',number_format(-$discount,2),'บาท');
	if ($cartinfo->shipping) $tables->rows[]=array('รวมค่าขนส่ง',number_format($cartinfo->shipping,2),'บาท');
	$tables->rows[]=array('คงเหลือจำนวนเงินที่ต้องชำระ','<strong><big>'.number_format($cartinfo->total-$discount+$cartinfo->shipping,2).'</big></strong>','บาท');

	$ret .= $tables->build();

	if ($simulate) {
		$ret.=print_o($cartinfo,'$cartinfo');
	}
	$ret.='<script type="text/javascript">
	$("#edit-shipto").click(function() {
		$("#edit-shipcode-3").prop("checked", true)
		});
	</script>';
	//$ret.=print_o($post,'$post').print_o($cartinfo,'$cartinfo');
	return $ret;
}

/**
 * List item in shoping cart in table
 */
function __ibuy_cart_proceed_cart($cartinfo) {
	$tables = new Table();
	$tables->thead=array('','รหัสสินค้า','รายการ','amt'=>'จำนวน','money'=>'ราคา/หน่วย','money subtotal'=>'รวม','money discount'=>'ส่วนลด*','money total'=>'รวมทั้งหมด');
	foreach ($cartinfo->items as $rs) {
		unset($row);
		$row[]='<a class="sg-action" data-title="ลบสินค้าออกจากตะกร้า" data-confirm="ต้องการลบรายการสินค้าในตะกร้า กรุณายืนยัน?" href="'.url('ibuy/cart/'.$rs->tpid.'/delete').'" data-rel="refresh"><i class="icon -delete"></i></a>';
		$row[]=$rs->tpid;
		$row[]='<a href="'.url('ibuy/'.$rs->tpid).'">'.$rs->title.'</a><br />'.(cfg('ibuy.stock.use')?($rs->balance>=$$rs->amt?'In Stock':'Not in stock; order now and we\'ll deliver when available'):'สินค้าพร้อมส่ง').(cfg('ibuy.resaler.discount')>0?' , '.($rs->isdiscount?'':'ไม่นำมา').'คำนวณส่วนลด':'').(cfg('ibuy.franchise.marketvalue')>0?' , '.($rs->ismarket?'':'ไม่นำมา').'คำนวณค่าการตลาด':'');
		$row[]='<strong>'.($rs->amt>0?number_format($rs->amt):'').'</strong>';
		$row[]=$rs->price>0?number_format($rs->price,2):'';
		$row[]=$rs->subtotal>0?number_format($rs->subtotal,2):'';
		$row[]=$rs->discount>0?'('.number_format($rs->discount,2).')':'';
		$row[]=number_format($rs->total,2);
		$tables->rows[]=$row;
	}
	if ($cartinfo->shipping) {
		$tables->rows[]=array('','','<strong>ค่าขนส่ง</strong>','','','','',number_format($cartinfo->shipping,2));
	}
	$tables->rows[]=array('','','<strong>รวมทั้งสิ้น</strong>','<strong>'.$cartinfo->amt.'</strong>','','<strong>'.number_format($cartinfo->subtotal,2).'</strong>',$cartinfo->discount>0?'('.number_format($cartinfo->discount,2).')':'','<strong>'.number_format($cartinfo->total+$cartinfo->shipping,2).'</strong>');

	$ret .= $tables->build();

	if ($cart->discount_summary) $ret.='<p>หมายเหตุ :</p><ul><li><strong>ส่วนลด*</strong> - ส่วนลดเงินสดที่จะสามารถนำไปใช้ในการสั่งซื้อสินค้าครั้งต่อไป</li></ul>';
	return $ret;
}
?>