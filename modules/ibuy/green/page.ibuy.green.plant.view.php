<?php
/**
* Module Method
* Created 2019-11-01
* Modify  2019-11-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function ibuy_green_plant_view($self, $plantId) {
	$ret = '';

	$plantId = ($plantInfo = R::Model('ibuy.plant.get', $plantId)) ? $plantInfo->plantid : NULL;
	$shopInfo = R::Model('ibuy.shop.get', $plantInfo->info->orgid);

	$isShopAdmin = $shopInfo->is->orgadmin;

	R::View('toolbar',$self,'จองผลิตภัณฑ์ @Green Smile','ibuy.green.shop', $plantInfo);

	if (empty($plantId)) return message('error', 'PROCESS ERROR:Data Not Found.');

	$ret .= '<header class="header -hidden">'._HEADER_BACK.'<h3>จองผลิตภัณฑ์</h3></header>';

	$ret .= '<div class="ibuy-plant-view">';

	$ret .= '<div class="ibuy-product-header"><h2>'.$plantInfo->info->productname.' ('.$plantInfo->info->categoryName.')'.'</h2></div>';



	$photoStr = '';
	foreach ($plantInfo->photos as $item) {
		$photoStrItem = '';
		$ui = new Ui('span');

		if ($item->type == 'photo') {
			//$ret.=print_o($item,'$item');
			$photoInfo=model::get_photo_property($item->file);

			if ($isItemEdit) {
				$ui->add('<a class="sg-action" href="'.url('ibuy/my/info/photo.delete/'.$item->fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="this" data-removeparent="li"><i class="icon -cancel"></i></a>');
			}

			$photo_alt = $item->title;
			$photoStrItem .= '<li class="ui-item -hover-parent">';

			$photoStrItem .= '<nav class="nav -icons -hover -top-right -no-print">'.$ui->build().'</nav>';

			$photoStrItem .= '<a class="sg-action" data-group="photo'.$actionId.'" href="'.$photoInfo->_src.'" data-rel="img" title="'.htmlspecialchars($photo_alt).'">';
			$photoStrItem .= '<img class="photoitem -'.($photoInfo->_size->width > $photoInfo->_size->height ? 'wide' : 'tall').'" src="'.$photoInfo->_url.'" width="200" />';
			//$photoStrItem .= ' />';
			$photoStrItem .= '</a>';

			$photoStrItem .= '</li>'._NL;

			$photoStr .= $photoStrItem;

		}
	}


	$ret .= '<div class="ibuy-product-photo">'._NL
		. '<ul id="ibuy-plant-photo-'.$plantInfo->info->plantid.'" class="ui-album">'._NL
		. $photoStr
		. '</ul>'._NL
		. '</div><!-- ibuy-product-photo -->'._NL;



	$ret .= '<div class="ibuy-product-detail">';

	$ret .= '<div class="header"><h2>'.$plantInfo->info->productname.' ('.$plantInfo->info->categoryName.')'.'</h2></div>';

	$ret .= '<p>เริ่มลงแปลง '.($plantInfo->info->startdate ? sg_date($plantInfo->info->startdate, 'ว ดด ปปปป') : '').' '
		. 'วันเก็บเกี่ยว '.($plantInfo->info->cropdate ? sg_date($plantInfo->info->cropdate, 'ว ดด ปปปป') : '').'<br />'
		. 'ปริมาณผลผลิต <b>'.$plantInfo->info->qty.'</b> '.$plantInfo->info->unit.'<br />'
		. 'ปริมาณคงเหลือ <b>'.$plantInfo->info->balance.'</b> '.$plantInfo->info->unit.'<br />'
		. '</p>'
		. ($plantInfo->info->detail ? '<p>'.nl2br($plantInfo->info->detail).'</p>' : '');

	$ret .= '<div class="ibuy-book-label">'
		. '<div class="-normal-price">ราคาขาย <span class="-money">'.number_format($plantInfo->info->saleprice,2).'</span> บาท</div>'
		. '<div class="-book-price">ราคาจอง <span class="-money">'.number_format($plantInfo->info->bookprice,2).'</span> บาท</div>';

	$form = new Form(NULL, url('ibuy/my/info/book.save/'.$plantId), NULL, 'sg-form -book-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'close | load');

	if ($plantInfo->info->balance > 0) {
		$form->addField(
			'qty',
			array(
				'type' => 'text',
				'label' => 'จำนวนจอง',
				'value' => 1,
				'pretext' => '<div class="input-prepend -nowrap"><span><a id="ibuy-plant-down" class="btn -link" href="javascript:void(0)"><i class="icon -material">remove</i></a></span></div>',
				'posttext' => '<div class="input-append -nowrap"></span><span>กก.</span><span><a id="ibuy-plant-up" class="btn -link" href="javascript:void(0)"><i class="icon -material">add</i></a></div>',
				'container' => '{class: "-group"}',
			)
		);

		$form->addField(
			'save',
			array(
				'type' => 'button',
				'class' => '',
				'value' => '<i class="icon -material">done_all</i><span>จองเลย</span>',
			)
		);
	} else {
		$form->addText('<span class="btn -link -fill"><i class="icon -material -gray">cancel</i><span>ปิดรับการจอง</span></span>');
	}

	$ret .= $form->build();

	$ret .= '</div><!-- ibuy-book-label -->';

	$ret .= '</div><!-- ibuy-product-detail -->';



	$ret .= '<div class="ibuy-product-shop"><h5><a href="'.url('ibuy/green/shop/'.$plantInfo->shopId).'">'.$plantInfo->info->shopName.'</a></h5>'
		.('<span class="btn standard -'.str_replace(' ', '',strtolower($plantInfo->info->standard)).' -'.strtolower($plantInfo->info->approved).'">'.$plantInfo->info->standard.'<br />( '.$plantInfo->info->approved.' )</span>')
		. '<address>ที่อยู่ '.$shopInfo->info->address.'<br />'
		. 'โทร. '.$shopInfo->info->phone
		. '</address>';

	$ret .= '<h5>รายการจอง</h5>';
	$stmt = 'SELECT fb.*, fp.`unit`, u.`username` `bookByUsername`, u.`name` `bookByName`
		FROM %ibuy_farmbook% fb
			LEFT JOIN %ibuy_farmplant% fp USING(`plantid`)
			LEFT JOIN %users% u ON u.`uid` = fb.`uid`
		WHERE fb.`plantid` = :plantid';

	$dbs = mydb::select($stmt, ':plantid', $plantId, ':uid', i()->uid);

	$myBookUi = new Ui();
	foreach ($dbs->items as $rs) {
		if ($rs->uid == i()->uid || $isShopAdmin) {
			$myBookUi->add('<span class="profile-photo"><img src="'.model::user_photo($rs->bookByUsername).'" width="100%" height="100%" /></span>'
				. ($rs->uid == i()->uid ? 'ฉันจอง ' : '')
				. '@'.sg_date($rs->created, _DATE_FORMAT)
				. ' จำนวน '.$rs->qty.' '.$rs->unit
				. ($isShopAdmin ? '<a class="sg-action btn -link" href="'.url('ibuy/my/info/book.remove/'.$rs->bookid).'" data-rel="notify" data-done="load" data-title="ลบรายการจอง" data-confirm="ลบรายการจองนี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a>' : '')
			);
		}
	}
	$ret .= $myBookUi->count() ? $myBookUi->build() : 'ยังไม่มีใครจอง';

	$ret .= '</div>';






	//$ret .= print_o($dbs, '$dbs');
	//$ret .= print_o($plantInfo, '$plantInfo');
	//$ret .= print_o($shopInfo, '$shopInfo');

	$ret .= '</div>';

	$ret .= '<script type="text/javascript">
	$("#ibuy-plant-down").click(function() {
		if ($("#edit-qty").val() > 1) {
			$("#edit-qty").val($("#edit-qty").val() - 1)
		}
	});
	$("#ibuy-plant-up").click(function() {
		$("#edit-qty").val(parseInt($("#edit-qty").val())	 + 1)
	});

	</script>';

	return $ret;
}
?>