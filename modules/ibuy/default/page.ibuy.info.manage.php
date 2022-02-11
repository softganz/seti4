<?php
/**
* iBuy Controller
* Created 2018-12-24
* Modify  2019-05-30
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function ibuy_info_manage($self, $productInfo) {
	if (!($productId = $productInfo->tpid)) return message('error', 'PROCESS ERROR');

	$isAdmin = user_access('administer ibuys');
	$isOfficer = $isAdmin || user_access('access ibuys customer');
	$isEdit = $productInfo->right->isEdit;

	$ret .= '<header class="header -box -hidden">'._HEADER_BACK.'<h3>'.$productInfo->title.'</h3></header>';

	$inlineAttr['class'] = 'ibuy-info-manage sg-view -co-2';
	if ($isEdit) {
		$inlineAttr['class'] .= ' sg-inline-edit';
		$inlineAttr['data-update-url'] = url('ibuy/'.$productId.'/info/field');
		$inlineAttr['data-tpid'] = $productId;
		//$inlineAttr['data-refresh-url'] = url('ibuy/'.$tpid,array('debug'=>post('debug')));
		if (debug('inline')) $inlineAttr['data-debug'] = 'inline';
	}

	$ret .= '<div '.sg_implode_attr($inlineAttr).'>';

	$ret .= '<div class="-sg-view">';

	$ret .= '<div style="padding: 4px; margin: 16px 0; font-weight: bold; font-size: 1.1em;">'.SG\inlineedit(array('group'=>'topic','fld'=>'title', 'class' => '-fill'),$productInfo->title,$isEdit).'</div>';

	$photoAlbumUi = new Ui(NULL,'ui-album -justify-left');
	$photoAlbumUi->addId('photo-album');
	foreach ($productInfo->photos as $photoItem) {
		$photoInfo = model::get_photo_property($photoItem->file);

		$photoNavUi = new Ui('span');
		if ($isEdit) {
			$photoNavUi->add('<a class="sg-action" href="'.url('ibuy/'.$productId.'/info/photo.delete/'.$photoItem->fid).'" data-rel="notify" data-done="remove:parent .ui-album>.ui-item" data-title="ลบภาพ" data-confirm="ต้องการลบภาพนี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a>');
		}
		$photo_alt = $photoItem->title;
		$photoAlbumUi->add('<a class="sg-action" data-group="photo'.$actionId.'" href="'.$photoInfo->_src.'" data-rel="img" title="'.htmlspecialchars($photo_alt).'">'
			. '<img class="photoitem -'.($photoInfo->_size->width > $photoInfo->_size->height ? 'wide' : 'tall').'" src="'.$photoInfo->_url.'" width="100" />'
			. '</a>'
			. '<nav class="nav -icons -hover">'.$photoNavUi->build().'</nav>',
			'{class: "-sg-128 -hover-parent"}'
		);
	}
	$photoAlbumUi->add(
		'<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('ibuy/'.$productId.'/info/photo.upload').'" data-rel="#photo-album" data-before="li" data-class="ui-item -sg-128 -hover-parent"><input type="hidden" name="tagname" value="photo" /><span class="btn -link fileinput-button"><i class="icon -material">add_a_photo</i><input type="file" name="photo[]" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span><input class="-hidden" type="submit" value="upload" /></form>',
		'{class: "-upload -sg-128 -hover-parent"}'
	);

	$ret .= $photoAlbumUi->build();

	$tables = new Table();
	$tables->addClass('-center');
	$tables->addClass('ibuy-price-table -admin');
	foreach (cfg('ibuy.price.use') as $key => $item) {
		$tables->thead['money -'.$key] = $item->label;
		$tables->rows[0][] = SG\inlineedit(
			array('group'=>'product','fld'=>$key, 'trid' => $productId, 'value' => $productInfo->info->{$key}, 'ret'=>'money'),
			number_format($productInfo->info->{$key},2),
			$isEdit,
			'money'
		);
			//number_format($rs->{$key},2);
	}

	$ret .= $tables->build();

	$ret .= '<b>รายละเอียดสินค้า</b>';
	$ret .= SG\inlineedit(array('group'=>'revision','fld'=>'body', 'trid' => $productInfo->info->revid, 'ret'=>'nl2br'),$productInfo->info->body,$isEdit,'textarea');

	//$ret .= nl2br($productInfo->info->body);

	$ret .= '</div><!-- -sg-view -->';

	$ret .= '<div class="-sg-view">';
	$ret .= '<h5>สินค้าทำงาน</h5>';
	$ret .= '<div>'
		. SG\inlineedit(array('group'=>'product','fld'=>'outofsale','value'=>$productInfo->info->outofsale),'N:มีขาย',$isEdit,'radio')
		. SG\inlineedit(array('group'=>'product','fld'=>'outofsale','value'=>$productInfo->info->outofsale),'O:หมด',$isEdit,'radio')
		. SG\inlineedit(array('group'=>'product','fld'=>'outofsale','value'=>$productInfo->info->outofsale),'Y:ยกเลิก',$isEdit,'radio')
		. '</div>';
	$ret .= '</div><!-- -sg-view -->';

	$ret .= '</div><!-- sg-view -->';

	$ret .= '<style type="text/css">
	.sg-inline-edit .inline-edit-field.-money {min-width: 30px;}
	.sg-inline-edit .inline-edit-field.-textarea>span {min-height: 120px;}
	</style>';
	//$ret .= print_o($productInfo, '$productInfo');
	return $ret;
}
?>