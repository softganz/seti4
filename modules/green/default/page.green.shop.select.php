<?php
/**
* My GoGreen Shop
*
* @param Object $self
* @param Int $shopId
* @return String
*/

$debug = true;

function green_shop_select($self, $shopId = NULL) {
	$getRefUrl = post('ref');

	$isAdmin = user_access('administer ibuys');

	if (!i()->ok) return R::View('signform', '{showTime: false, time: -1}');

	$myShopList = R::Model('green.shop.get', array('my' => '*'), '{debug: false, limit: "*"}');

	//setcookie('shopid',1316,time()+365*24*60*60,cfg('cookie.path'),cfg('cookie.domain'));
	//return;
	//debugMsg($myShopList, '$myShopList');


	// If not have shop then create new shop
	if (!$myShopList) {
		// Shop Registers
		$ret .= '<section id="ibut-shop-verify" class="card -margin -sg-text-center"><p style="padding: 32px 0;"><big>ยินดีต้อนรับ <b>'.i()->name.'</b> เข้าสู่ระบบการจัดการข้อมูล  <b>"สวนยางยั่งยืน"</b></big><br /><br />ท่านยังไม่เคยใช้งานมาก่อน ต้องการเริ่มใช้งานหรือไม่?<br /></p><p style="padding: 32px 0;"><a class="sg-action btn -primary" href="#ibuy-shop-create" data-rel="parent:p" data-width="640">เริ่มใช้งาน => สร้างองค์กร/หน่วยงาน</a></p>';
		$ret .= '<p style="padding: 32px;">** กรณีที่ท่านเป็นสมาชิกของกลุ่ม/เครือข่ายที่ได้สร้างกลุ่ม/องค์กร/หน่วยงาน/ร้านค้าไว้ในระบบแล้ว ท่านสามารถแจ้งผู้ดูแลกลุ่ม/เครือข่ายให้เพิ่มชื่อของท่านเข้าเป็นสมาชิกของกลุ่ม/เครือข่ายเพื่อจัดการข้อมูลร่วมกัน โดยไม่จำเป็นต้องสร้างกลุ่ม/ร้านค้าใหม่ **</p>';
		$ret .= '</section>';


		//$ret .= '<section class="-sg-text-center"><p class="notify" style="margin: 32px; padding: 32px;">ท่านยังไม่ได้สร้างเครือข่ายหรือร้านค้าบน Green Smile<br /></p><p style="padding: 64px 0;"><a class="sg-action btn -primary" href="'.url('green/my/shop/create', array('ref' => $getRefUrl)).'" data-rel="box" data-width="480">สร้างเครือข่าย/ร้านค้า บน Green Smile</a></p>';
		//$ret .= '<p>** กรณีที่ท่านเป็นสมาชิกของเครือข่าย/ร้านค้าที่ได้สร้างไว้ในระบบแล้ว ท่านสามารถแจ้งผู้ดูแลเครือข่าย/ร้านค้าให้เพิ่มท่านเป็นสมาชิกเพื่อจัดการข้อมูลร่วมกัน โดยไม่จำเป็นต้องสร้างเครือข่าย/ร้านค้าใหม่ **</p>';
		$ret .= '</section>';

		$form = new Form(NULL, url('ibuy/shop/create'), 'ibuy-shop-create', 'sg-form');
		$form->addData('checkValid', true);
		$form->addData('rel', 'notify');
		$form->addData('done', 'reload');

		$form->addField(
			'name',
			array(
				'type' => 'text',
				'label' => 'ชื่อเครือข่าย/ร้านค้า',
				'class' => '-fill',
				'require' => true,
				'value' => htmlspecialchars($name),
				'placeholder' => 'ระบุชื่อเครือข่าย หรือ ร้านค้า'
			)
		);

		$form->addField(
			'save',
			array(
				'type' => 'button',
				'value' => '<i class="icon -material">done</i><span>สร้างเครือข่าย/ร้านค้า</span>',
				'container' => '{class: "-sg-text-right"}',
			)
		);

		$ret .= '<div id="ibut-shop-create" class="-hidden">'.$form->build().'</div>';
		return $ret;
	}


	// If has one shop, then auto select that shop
	if (count($myShopList) == 1) {
		//$ret .= 'HAVE 1 Shop';
		$shopId = reset($myShopList)->shopid;
		$_SESSION['shopid'] = $shopId;
	} else if ($shopId > 0) {
		$_SESSION['shopid'] = $shopId;
		//$ret .= 'SET to shopid '.$shopId;
	} else {
		//$ret .= 'SET Shop from _COOKIE';
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
			$shopSelectUrl = url('green/shop/select/'.$rs->shopid);
			$shopRetUrl = url($retUrl);
			$shopBanner = $rs->logo;
			$cardStr = '<div class="-banner -sg-text-center">'
				. '<img class="-logo" src="'.$shopBanner.'" width="96" height="96" />'
				. '</div>'
				. '<h3 class="-title">'.$rs->name.'</h3>'
				//. '<div class="-detail"></div>'
				. '<nav class="nav -card -sg-text-center">'
				. '<a class="sg-action btn -primary" href="'.$shopSelectUrl.'" data-rel="none" data-done="reload"><i class="icon -material">done</i><span>เลือก</span></a>'
				. '</nav>';

			$cardUi->add(
				$cardStr,
				'{class: "sg-action -sg-flex", href: "'.$shopSelectUrl.'", "data-rel": "none", "data-done": "reload"}'
			);
			/*
			$shopUrl = url('green/my/shop/'.$rs->shopid, array('ref' => $getRefUrl));
			$shopBanner = $rs->logo;
			$cardStr = '<div class="-banner"><a href="'.$shopUrl.'">'
				. '<img class="-logo" src="'.$shopBanner.'" width="96" height="96" /></a></div>'
				. '<h3 class="-title"><a href="'.$shopUrl.'">'.$rs->name.'</a></h3>'
				. '<div class="-detail"></div>'
				. '<nav class="nav -card -sg-text-center"><a class="sg-action btn -primary" href="'.url('green/shop/select/'.$rs->shopid).'" data-rel="reload"><i class="icon -material">done</i><span>เลือกหน่วยงาน/ร้านค้า</span></a></nav>';
			$cardUi->add($cardStr, '{class: "-sg-flex"}');
			*/
		}

		$ret .= $cardUi->build();

		return $ret;
	}

	if (!$isAdmin && !$myShopList[$shopId]) {
		$shopId = NULL;
		setcookie('shopid',$shopId,time()-3600,cfg('cookie.path'),cfg('cookie.domain'));
		//location('green/my/shop');
		//$ret .= 'SHOP SELECT ERROR';
	}

	//if (!$myShopList[$ShopId]) return $ret.'ERROR : No Shop';
	//$ret .= print_o($myShopList, '$myShopList');
	return true;
}
?>