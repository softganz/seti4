<?php
/**
* Create product sale label
* Created 2019-06-03
* Modify  2019-06-03
*
* @param Record set $info
* @param Int $amt
* @param Boolean $show
* @return String
*/

$debug = true;

function view_ibuy_sale_label($info, $amt = 0, $show = true) {
	//debugMsg('Create Sale Label for is user = '.i()->uid.' i am '.i()->am);
	//debugMsg('SHOW SALE LABEL');
	$price = R::Model('ibuy.get.price', $info);
	$ret = '<div class="ibuy-sale-label ibuy-product-'.($info->available?'':'not-').'available">';
	//$ret.=print_o(i(),'i()');
	if (user_access('buy ibuy product')) {
		if (cfg('ibuy.price.cannotbuy')=='hidden' && i()->am == '') {
			$ret .= '<p>'.cfg('ibuy.message.cannotbuy').'</p>';
		} else if ($info->outofsale == 'O') {
			$ret .= '<p>ขออภัย สินค้าหมด</p>';
		} else if ($info->outofsale == 'Y') {
			$ret.='<p>ขออภัย สินค้างดจำหน่าย</p>';
		} else if ($price == 0) {
			$ret .= '<p>ขออภัย สินค้ายังไม่ได้กำหนดราคาขาย</p>';
		} else if (cfg('ibuy.stock.use') && $info->balance <= 0) {
			$ret .= '<p>ขออภัย สินค้าไม่มีเหลือในคลังสินค้า</p>';
		} else if ($info->outofsale == 'N') {
			$form = new Form(NULL, url('ibuy/cart/add/'.$info->tpid), 'ibuy-add2cart', '-addtocart');

			$form->addField('price',array('type'=>'hidden','value'=>$price));
			if ($amt) {
				$form->addField('amt',array('type'=>'hidden','value'=>$amt));
			} else {
				$max_qty = cfg('ibuy.stock.use') ? $info->balance : 300;
				$optionAmt = array();
				for ($i = 1; $i < 30 && $i <= $max_qty; $i++) {
					if ($i >= $info->minsaleqty) $optionAmt[$i] = $i;
				}
				for ($i = 30; $i <= 100 && $i <= $max_qty; $i = $i + 10) {
					if ($i >= $info->minsaleqty) $optionAmt[$i] = ($i);
				}
				for ($i = 200; $i <= 300 && $i <= $max_qty; $i = $i + 50) {
					if ($i >= $info->minsaleqty) $optionAmt[$i] = $i;
				}
				$form->addField(
					'amt',
					array(
						'type' => 'select',
						'label' => tr('Quantity').':',
						'options' => $optionAmt,
					)
				);
			}
			$form->addField(
				'submit',
				array(
					'type' => 'button',
					'value' => '<i class="icon -material">add_shopping_cart</i><span>{tr:Add to my Cart}</span>',
				)
			);

			$ret .= $form->build();

			if ($show) $ret.='<ul><li><a class="btn -link ibuy-cart-view"  href="'.url('ibuy/cart').'"><i class="icon -material -gray">shopping_cart</i><span>'.tr('View my Cart').'</span></a></li><li><a class="btn -link" href="'.url('ibuy/checkout').'"><i class="icon -material -gray">done_all</i><span>'.tr('Proceed to checkout').'</span></a></li></ul>'._NL;
		} else if (!$info->available) {
			$ret.='สินค้างดจำหน่ายชั่วคราว';
		}
		if (cfg('ibuy.message.salelabel')) $ret.='<div>'.cfg('ibuy.message.salelabel').'</div>';
	} else {
		$ret.='<p class="ibuy-message-cannotbuy">'.SG\getFirst(cfg('ibuy.message.cannotbuy'),'ขออภัย ท่านไม่สามารถซื้อสินค้านี้ได้ กรุณาติดต่อผู้ดูแลเว็บไซท์').'</p>';
	}
	$ret.='</div><!--ibuy-product-label-->';
	
	return $ret;
}
?>