<?php
/**
* Green :: My Organic Organization
* Created 2020-09-04
* Modify  2020-12-03
*
* @param Object $options
* @return Object Ui
*/

$debug = true;

function view_green_my_select_org($options = '{}') {
	$defaults = '{debug: false, retUrl: null, "title": "เลือกกลุ่ม", "btnText": "เลือกกลุ่ม"}';
	$options = SG\json_decode($options, $defaults);

	$isAdmin = is_admin('green');
	$shopGetPara = array();
	if ($isAdmin && $options->show == 'all') {
	} else if ($isAdmin && $options->search) {
		$shopGetPara['search'] = $options->search;
	} else {
		$shopGetPara['my'] = '*';
	}

	$myShopList = R::Model('green.shop.get', $shopGetPara, '{debug: false, limit: "*"}');

	$cardUi = new Ui(NULL, 'ui-card green-select -org');
	$cardUi->header('<h3>'.$options->title.'</h3>', '{class: "box -hidden"}', array('preText' => _HEADER_BACK));

	foreach ($myShopList as $rs) {
		$shopUrl = url('green/my/shop/'.$rs->shopid);
		$shopBanner = $rs->logo;
		$address = SG\implode_address($rs);
		$cardStr = '<div class="header"><h3>'.$rs->name.'</h3></div>'
			. '<div class="detail">'
			. '<div class="-banner"><a href="'.$shopUrl.'"><img class="-logo" src="'.$shopBanner.'" width="48" height="48" /></a></div>'
			. '<div class="-detail">'
			. ($address ? $address : 'ไม่ระบุที่อยู่')
			. '<br />'
			. ($rs->phone ? 'โทร : '.$rs->phone : 'ไม่ระบุเบอร์โทร')
			. '</div>'
			. '</div><!-- detail -->'
			. '<nav class="nav -card -sg-text-center"><span class="sg-action btn -primary -fill" style="pointer-events: none;"><i class="icon -material">done</i><span>'.$options->btnText.'</span></span></nav>';

		$cardOption = array(
			'class' => 'sg-action',
		);
		if ($options->href) {
			$href = preg_replace('/\$id/', $rs->shopid, $options->href);
			$cardOption['href'] = $href;
		}
		if ($options->{'data-rel'}) $cardOption['data-rel'] = $options->{'data-rel'};
		if ($options->{'data-done'}) $cardOption['data-done'] = $options->{'data-done'};
		if ($options->{'data-webview'}) $cardOption['data-webview'] = $rs->name;
		$cardUi->add(
			$cardStr,
			$cardOption
		);
	}

	//$ret .= $cardUi->build();

	//debugMsg($myShopList,'$myShopList');
	//$ret .= print_o($_SESSION, '$_SESSION');
	//$ret .= print_o($shopInfo, '$shopInfo');
	//debugMsg($options,'$options');
	return $cardUi;
}
?>