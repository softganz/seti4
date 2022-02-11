<?php
/**
* My GoGreen Shop
*
* @param Object $self
* @param Int $shopId
* @return String
*/

$debug = true;

function ibuy_green_my_shop($self, $shopId = NULL, $action = NULL) {
	//if (is_numeric($action)) {$shopId = $action; unset($action);}

	if (!i()->ok) {
		// Show Login Page
		R::View('toolbar',$self,'@Secure Log in','none');
		$ret = R::View('signform', '{time:-1, rel: "box", signret: "ibuy/green/my/shop"}');
		$ret .= '<style type="text/css">
		.toolbar.-main h2 {text-align: center;}
		.form.signform .form-item {margin: 0 auto 16px; position: relative; max-width: 280px;}
		.form.signform label {position: absolute; left: 8px; color: #666; font-style: italic; font-size: 0.9em; font-weight: normal;}
		.form.signform .form-text, .form.signform .form-password {padding-top: 24px;}
		.module-ibuy.-softganz-app .form-item.-edit-cookielength {display: none;}
		.login.-normal h3 {display: none;}
		</styel>';
		return $ret;
	}

	$isAdmin = user_access('administer ibuys');

	$myShopList = R::Model('ibuy.shop.get', array('my' => '*'), '{debug: false, limit: "*"}');


	if (!$myShopList) {
		// Shop Registers
		$ret .= '<section class="-sg-text-center"><p class="notify">ยังไม่ได้เปิดร้านค้าบน Green Smile<br /></p><p style="padding: 64px 0;"><a class="sg-action btn -primary" href="'.url('ibuy/my/shop/create').'" data-rel="box" data-width="640">สร้างกลุ่ม/ร้านค้าใหม่บน Green Smile</a></p>';
		//$ret .= R::Page('ibuy.green.shop.register', NULL);
		$ret .= '<p>** กรณีที่ท่านเป็นสมาชิกของกลุ่ม/เครือข่ายที่ได้สร้างกลุ่ม/ร้านค้าไว้ในระบบแล้ว ท่านสามารถแจ้งผู้ดูแลกลุ่ม/เครือข่ายให้เพิ่มชื่อของท่านเข้าเป็นสมาชิกของกลุ่ม/เครือข่ายเพื่อจัดการข้อมูลร่วมกัน โดยไม่จำเป็นต้องสร้างกลุ่ม/ร้านค้าใหม่ **</p>';
		$ret .= '</section>';
		return $ret;
	}



	R::View('toolbar',$self,'My Shop @Green Smile','ibuy.green.my.shop');


	//setcookie('shopid',1316,time()+365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));
	//return;

	if (count($myShopList) == 1) {
		//$ret .= 'HAVE 1 Shop';
		$shopId = reset($myShopList)->shopid;
		setcookie('shopid',$shopId,time()+365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));
		$_SESSION['shopid'] = $shopId;
	} else if ($shopId > 0) {
		//$ret .= 'SET to shopid '.$shopId;
		setcookie('shopid',$shopId,time()+365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));
		$_SESSION['shopid'] = $shopId;
	} else {
		//$ret .= 'SET Shop from _SESSION';
		$shopId = $_SESSION['shopid'];
	}

	// Select Shop
	if (!$shopId || post('selectshop')) {


		if ($isAdmin && post('selectshop') == '*') {
			$stmt = 'SELECT
				o.`orgid` `shopid`, o.`name`, o.`phone`
				, "https://communeinfo.com/themes/default/logo-green.png" `logo`
				FROM %ibuy_shop% of
					LEFT JOIN %db_org% o ON o.`orgid` = of.`shopid`
				GROUP BY of.`shopid`
				ORDER BY `shopid` DESC';
			$myShopList = mydb::select($stmt)->items;
		}
		$cardUi = new Ui(NULL, 'ui-card -shop');

		foreach ($myShopList as $rs) {
			$shopUrl = url('ibuy/green/my/shop/'.$rs->shopid);
			$shopBanner = $rs->logo;
			$cardStr = '<div class="-banner"><a href="'.$shopUrl.'"><img class="-logo" src="'.$shopBanner.'" width="96" height="96" /></a></div>'
				. '<h3 class="-title"><a href="'.$shopUrl.'">'.$rs->name.'</a></h3>'
				. '<div class="-detail">'.$rs->address.'<br />โทร : '.$rs->phone.'</div>'
				. '<nav class="nav -card -sg-text-center"><a class="btn -primary" href="'.url('ibuy/green/my/shop/'.$rs->shopid).'"><i class="icon -material">done</i><span>เลือกร้านค้า</span></a></nav>';
			$cardUi->add($cardStr, '{class: "-sg-flex"}');
		}

		$ret .= $cardUi->build();

		return $ret;
	}

	if (!$isAdmin && !$myShopList[$shopId]) {
		$shopId = NULL;
		setcookie('shopid',$shopId,time()-3600,cfg('cookie.path'),cfg('cookie.domain'));
		location('ibuy/green/my/shop');
	}

	//if (!$myShopList[$ShopId]) return $ret.'ERROR : No Shop';





	$shopInfo = R::Model('ibuy.shop.get', 'my', '{debug: false}');

	R::View('toolbar',$self, $shopInfo->name.' @Green Smile','ibuy.green.my.shop');
	
	$isViewOnly = $action == 'view';
	$isEditable = $shopInfo->info->isEdit;
	$isEdit = $shopInfo->info->isRight && $action == 'edit';

	switch ($action) {
		case 'create' :
			break;

		default :

			if (empty($shopInfo)) $shopInfo = $shopId;
			if (empty($shopInfo)) $action = 'home';
			else if (empty($action)) $action = 'shop.view';

			$argIndex = 3; // Start argument

			//debugMsg(func_get_args(), '$args');
			//debugMsg('PAGE IBUY/SHOP ShopId = '.$shopId.' , Action = ibuy.green.my.'.$action.' , ArgIndex = '.$argIndex.' , Arg = '.func_get_arg($argIndex));

			$ret = R::Page(
				'ibuy.green.my.'.$action,
				$self,
				$shopInfo,
				func_get_arg($argIndex),
				func_get_arg($argIndex+1),
				func_get_arg($argIndex+2),
				func_get_arg($argIndex+3),
				func_get_arg($argIndex+4)
			);

			//debugMsg('TYPE = '.gettype($ret));
			if (is_null($ret)) $ret = 'ERROR : PAGE NOT FOUND';

			break;
	}

	return $ret;
}
?>