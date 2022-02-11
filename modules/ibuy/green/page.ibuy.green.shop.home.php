<?php
/**
* GoGreen Spp Shop Home
*
* @return String
*/

$debug = true;

function ibuy_green_shop_home($self) {
	$ret = '';

	R::View('toolbar',$self,'ร้านค้า @Green Smile','ibuy.green.shop');

	$shopList = R::Model('ibuy.shop.get', NULL, '{debug: false, order: "`standard` DESC, CONVERT(o.`name` USING tis620) ASC", limit: "*"}');

	$ret .= '<section>';

	$cardUi = new Ui('div a', 'ui-card -shop -sg-flex');

	foreach ($shopList as $rs) {
		if (!$rs->standard) continue;
		$shopUrl = url('ibuy/green/shop/'.$rs->shopid);
		$shopBanner = $rs->logo;
		$cardStr = '<img class="-logo" src="'.$shopBanner.'?1" width="64" height="64" />'
			. '<h3 class="-title">'.$rs->name.'</h3>'
			. '<div class="-detail">'.SG\implode_address($rs).'<br />โทร : '.$rs->phone.'</div>';
		$cardUi->add(
			$cardStr,
			array('href'=>$shopUrl, 'class'=>'sg-action -sg-flex', 'data-webview' => true, 'data-webview-title' => htmlspecialchars($rs->name))
		);
	}

	$ret .= $cardUi->build();

	//$ret .= print_o($shopList,'$shopList');
	$ret .= '</section>';

	return $ret;
}
?>