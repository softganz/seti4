<?php
/**
* My GoGreen Shop
*
* @param Object $self
* @param Int $shopId
* @return String
*/

$debug = true;

function green_my_shop_select($self, $shopId = NULL, $retUrl = NULL) {
	$retUrl = SG\getFirst($retUrl, post('ret'));

	$isAdmin = user_access('administer ibuys');

	if ($shopId) {
		$_SESSION['shopid'] = $shopId;
		return;
	}

	$ret = '<header class="header">'._HEADER_BACK.'<h3>เลือกเครือข่าย/ร้านค้า</h3></header>';

	$myShopList = R::Model('green.shop.get', array('my' => '*'), '{debug: false, limit: "*"}');


	// Select Shop
	if ($isAdmin && post('selectshop') == '*') {
		$stmt = 'SELECT
			o.`orgid` `shopid`, o.`name`, o.`phone`
			, "https://communeinfo.com/themes/default/logo-green.png" `logo`
			FROM %ibuy_shop% of
				LEFT JOIN %db_org% o ON o.`orgid` = of.`shopid`
			GROUP BY of.`shopid`
			ORDER BY CONVERT(`name` USING tis620) ASC';
		$myShopList = mydb::select($stmt)->items;
	}
	$cardUi = new Ui(NULL, 'ui-card -shop');

	foreach ($myShopList as $rs) {
		$shopSelectUrl = url('green/shop/select/'.$rs->shopid);
		$shopRetUrl = url($retUrl);
		$shopBanner = $rs->logo;
		$cardStr = '<div class="-banner -sg-text-center">'
			. '<img class="-logo" src="'.$shopBanner.'" width="96" height="96" />'
			. '</div>'
			. '<h3 class="-title">'.$rs->name.'</h3>'
			//. '<div class="-detail"></div>'
			. '<nav class="nav -card -sg-text-center">'
			. '<a class="sg-action btn -primary" href="'.$shopSelectUrl.'" data-rel="none" data-done="close | reload:'.$shopRetUrl.'"><i class="icon -material">done</i><span>เลือก</span></a>'
			. '</nav>';

		$cardUi->add(
			$cardStr,
			'{class: "sg-action -sg-flex", href: "'.$shopSelectUrl.'", "data-rel": "none", "data-done": "close | reload:'.$shopRetUrl.'"}'
		);
	}

	$ret .= $cardUi->build();

	return $ret;

	//if (!$myShopList[$ShopId]) return $ret.'ERROR : No Shop';
	//$ret .= print_o($myShopList, '$myShopList');
	return true;
}
?>