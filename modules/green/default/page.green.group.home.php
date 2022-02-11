<?php
/**
* Green :: Group Home
*
* @return String
*/

$debug = true;

function green_group_home($self) {
	$getSearch = post('q');
	$ret = '';

	$toolbar = new Toolbar($self,'กลุ่ม @Green Smile');
	$toolbarNav = new Ui();
	$toolbarNav->add('<a href="'.url('green/group').'"><i class="icon -material">people</i><span>กลุ่ม</span></a>');
	$toolbarNav->add('<a class="sg-action" href="'.url('green/shop/*/land').'" data-webview="แปลงการผลิต"><i class="icon -material">nature</i><span>แปลงผลิต</span></a>');
	$toolbar->addNav('main', $toolbarNav);

	$shopListPara = NULL;
	if ($getSearch) {
		$shopListPara->search = $getSearch;
	}

	$shopList = R::Model('green.shop.get', $shopListPara, '{debug: false, order: "`standard` DESC, CONVERT(o.`name` USING tis620) ASC", limit: "*"}');

	$ret .= '<section>';
	$form = new Form(NULL, url('green/group'), NULL, 'sg-form');
	$form->addData('rel', '#main');
	$form->addField(
		'q',
		array(
			'type' => 'text',
			'class' => '-fill',
			'value' => $getSearch,
			'placeholder' => 'ค้นชื่อกลุ่ม',
			'posttext' => '<div class="input-append"><span><button class="btn" type="submit"><i class="icon -material">search</i></button></span></div>',
			'container' => '{class: "-group"}',
		)
	);

	$ret .= $form->build();

	$standardCard = new Ui('div a', 'ui-card -shop -sg-flex');
	$standardCard->header('<h3>กลุ่มที่ได้รับการรับรองมาตรฐาน</h3>');

	$noneStandardCard = new Ui('div a', 'ui-card -shop -sg-flex');
	$noneStandardCard->header('<h3>กลุ่มที่ยังไม่ได้รับการรับรองมาตรฐาน</h3>');

	foreach ($shopList as $rs) {
		$shopUrl = url('green/shop/'.$rs->shopid);
		$shopBanner = $rs->logo;
		$cardStr = '<img class="-logo" src="'.$shopBanner.'?1" width="64" height="64" />'
			. '<h3 class="-title">'.$rs->name.'</h3>'
			. '<div class="-detail">'.SG\implode_address($rs).'<br />โทร : '.$rs->phone.'</div>';

		if ($rs->standard) {
			$standardCard->add(
				$cardStr,
				array('href'=>$shopUrl, 'class'=>'sg-action -sg-flex', 'data-webview' => true, 'data-webview-title' => htmlspecialchars($rs->name))
			);
		} else {
			$noneStandardCard->add(
				$cardStr,
				array('href'=>$shopUrl, 'class'=>'sg-action -sg-flex', 'data-webview' => true, 'data-webview-title' => htmlspecialchars($rs->name))
			);
		}
	}

	$ret .= $standardCard->build();

	$ret .= $noneStandardCard->build();

	//$ret .= print_o($shopList,'$shopList');
	$ret .= '</section>';

	return $ret;
}
?>