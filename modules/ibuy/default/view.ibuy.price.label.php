<?php
/**
* Create product price label
* Created 2019-06-03
* Modify  2019-06-03
*
* @param Record Set $info
* @return String
*/

$debug = true;

function view_ibuy_price_label($info) {
	$price = R::Model('ibuy.get.price', $info);
	//debugMsg('Sale Price for this user = '.$price);
	//$ret.='i->am='.i()->am.'<br />';
	//$ret.='cfg(ibuy.price.default)='.cfg('ibuy.price.default').'<br />';
	//$ret.=print_o($info,'$info');

	// Show product price label information
	$ret.='<div class="ibuy-price-label ibuy-product-'.($info->available?'':'not-').'available">'._NL.'<table>'._NL;

	// Show list price
	if (cfg('ibuy.price.listprice')) {
		//$ret.=print_o($price,'$price');
		$ret.='<tr class="ibuy-price-listprice'.(i()->am?' ibuy-price-notused':'').'"><td>'.tr('List Price').'</td><td class="ibuy-price-money">';
		$ret.=cfg('ibuy.price.cannotbuy')=='hidden' && i()->am=='' ? '???.??' : number_format($info->listprice,2);
		$ret.='</td><td>บาท</td></tr>'._NL;
	}

	// Show resaler price
	if (cfg('ibuy.price.resaler')) {
		if ($info->resalerprice>0 && in_array(i()->am,array('resaler','franchise'))) $ret.='<tr class="ibuy-price-resaler'.(in_array(i()->am,array('franchise'))?' ibuy-price-notused':'').'"><td>'.tr('Resaler Price').'</td><td class="ibuy-price-money">'.number_format($info->resalerprice,2).'</td><td>บาท</td></tr>'._NL;
	}

	// Show franchise price
	if (cfg('ibuy.price.franchise')) {
		if ($info->retailprice>0 && in_array(i()->am,array('franchise'))) $ret.='<tr class="ibuy-price-franchise"><td>'.tr('Franchise Price').'</td><td class="ibuy-price-money">'.number_format($info->retailprice,2).'</td><td>บาท</td></tr>'._NL;
	}

	// Show sale price
	if ($price > 0 && user_access('buy ibuy product')) {
		//debugMsg('This product can buy for user '.i()->uid);
		$ret .= '<tr class="ibuy-price-sale"><td>'.tr('Price').'</td><td class="ibuy-price-money">';
		$ret .= cfg('ibuy.price.cannotbuy')=='hidden' && i()->am==''?'???.??':number_format($price,2);
		$ret .= '</td><td>บาท</td></tr>'._NL;
	} else if (cfg('ibuy.price.default')) {
		$ret .= '<tr class="ibuy-price-sale"><td>'.tr('Price').'</td><td class="ibuy-price-money">';
		$ret .= cfg('ibuy.price.cannotbuy')=='hidden' && i()->am==''?'???.??':number_format($price,2);
		$ret .= '</td><td>บาท</td></tr>'._NL;
	}
	
	$ret.='</table>'._NL;

	// Show balance
	if (cfg('ibuy.stock.use')) {
		$ret.='<p class="ibuy-balance">'.tr('Balance').' <span class="ibuy-balance-amt">'.number_format($info->balance).'</span> ชิ้น</p>'._NL;
	}
	$ret.='<ul>'.(cfg('ibuy.resaler.discount')>0?($info->isdiscount?'':'<li>ไม่นำมาคำนวณส่วนลด</li>'):'').(cfg('ibuy.franchise.marketvalue')>0?($info->ismarket?'':'<li>ไม่นำมาคำนวณค่าการตลาด</li>'):'').(cfg('ibuy.franchise.franchisor') && user_access('create ibuy paper')?'<li>'.($info->isfranchisor?'':'ไม่').'นำมาคำนวณยอดสำหรับเจ้าของเฟรนส์ไชน์</li>':'').'</ul>'._NL;

	$ret.='</div><!--ibuy-price-label-->';
	return $ret;
}
?>