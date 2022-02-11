<?php
/**
* My GoGreen Shop
*
* @param Object $self
* @param Int $shopId
* @return String
*/

$debug = true;

function green_app_my_shop_product($self) {
	$hasShop = 1;

	if (!i()->ok) {
		// Show Login Page
		return R::Page('green.app.my.shop', $self);
	} else if (!$hasShop) {
		// Shop Registers
		$ret .= R::Page('green.shop.register', NULL);
		return $ret;
	}

	new Toolbar($self,'ผลผลิต','none');

	$isViewOnly = $action == 'view';
	$isEditable = true; //$projectInfo->info->isRight;
	$isEdit = $projectInfo->info->isRight && $action == 'edit';


	//$ret = '<h3>ผลผลิต</h3>';
	$ret .= R::View('green.app.my.shop.menu');

	$ret .= '<h3>ผลผลิตที่จะออกมาเร็ว ๆ นี้</h3>';

	$tables = new Table();
	$tables->thead = array('ผลผลิต', 'start -date' => 'เริ่มผลิต', 'stop -date' => 'เก็บเกี่ยว');
	$tables->rows[] = array('ข้าวสังข์หยด','1 พ.ค. 61','1 ธ.ค. 61');
	$ret .= $tables->build();

	$ret .= '<h3>ผลผลิตที่ผ่านมา</h3>';
	$tables = new Table();
	$tables->thead = array('ผลผลิต', 'start -date' => 'เริ่มผลิต', 'stop -date' => 'เก็บเกี่ยว');
	$tables->rows[] = array('ข้าวสังข์หยด','1 พ.ค. 60','1 ธ.ค. 60');
	$ret .= $tables->build();


	if ($isViewOnly) {
		// Do nothing
	} else if ($isEdit) {
		$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -primary -circle48" href="'.url('green/app/my/shop/product',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -save -white"></i></a></div>';
	} else if ($isEditable) {
		$ret .= '<div class="btn-floating -right-bottom"><a class="sg-action btn -floating -circle48" href="'.url('green/app/my/shop/product/add',array('debug'=>post('debug'))).'" data-rel="#main"><i class="icon -addbig -white"></i></a></div>';
	}
	return $ret;
}
?>