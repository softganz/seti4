<?php
function ibuy_shop_toolbar($self,$shopId) {
	if (!cfg('ibuy.showshoptoolbar')) return NULL;
	//$self->theme->title='Shop '.$shopId;
	$ui=new ui(NULL,'ui-nav');
	$ui->add('<a href="'.url('ibuy/shop').'">ร้านค้า</a>');
	$ui->add('<a href="'.url('ibuy/shop'.($shopId?'/'.$shopId:'')).'">หน้าร้าน</a>');
	$ui->add('<a href="'.url('ibuy/shop',array('shop'=>$shopId)).'">สินค้า</a>');
	$ui->add('<a href="'.url('ibuy/shop/manage').'">จัดการหน้าร้าน</a>'); //.($shopId?'/'.$shopId:'')
	if (user_access('administer ibuys')) $ui->add('<a href="'.url('ibuy/admin').'">จัดการระบบ</a>');
	$ret.='<nav class="nav -module -ibuy">'.$ui->build().'</nav>';
	$ret.='<form class="search-box" method="get" action="'.url('ibuy').'"><input type="text" name="q" id="search-box" size="20" value="'.$_REQUEST['q'].'" placeholder="ชื่อสินค้า ยี่ห้อ"><button class="btn" type="submit"><i class="icon -search"></i></button></form>';
	$self->theme->toolbar=$ret;
}
?>