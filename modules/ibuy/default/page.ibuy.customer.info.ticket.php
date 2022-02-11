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

function ibuy_customer_info_ticket($self, $customerInfo, $ticketId) {
	if (!($customerId = $customerInfo->custid)) return message('error', 'PROCESS ERROR');

	$ticketInfo = R::Model('ibuy.ticket.get', $ticketId);

	if (!$ticketInfo) return message('error', 'PROCESS ERROR');

	$isEdit = $customerInfo->RIGHT & _IS_ADMIN;

	$currentUrl =  url('ibuy/customer/'.$customerId.'/info.ticket/'.$ticketId);

	$headerUi = new Ui();
	$headerDrop = new Ui();
	if ($ticketInfo->info->status == 'Complete') {
		$headerUi->add('<a class="btn -info" data-rel="notify"><i class="icon -material -white">check_circle_outline</i><span>COMPLETED</span></a>');
		$headerDrop->add('<a class="sg-action" href="'.url('ibuy/customer/'.$customerId.'/info/ticket.status/'.$ticketId, array('status' => 'Open')).'" data-rel="notify" data-done="load->replace:#ibuy-customer-info-ticket:'.$currentUrl.'"><i class="icon -material">restore</i><span>Restore</span></a>');
	} else {
		$headerUi->add('<a class="sg-action btn -primary" href="'.url('ibuy/customer/'.$customerId.'/info/ticket.status/'.$ticketId,array('status'=>'Complete')).'" data-rel="notify" data-done="load->replace:#ibuy-customer-info-ticket:'.$currentUrl.'"><i class="icon -material">check_circle</i><span>Complete</span></a>');
		$headerDrop->add('<a class="sg-action" href="'.url('ibuy/customer/'.$customerId.'/info/ticket.status/'.$ticketId, array('status' => 'Complete')).'" data-rel="notify" data-done="load->replace:#ibuy-customer-info-ticket:'.$currentUrl.'"><i class="icon -material">check_circle</i><span>Complete</span></a>');
	}

	$headerUi->add('<a class="sg-action btn -link" href="'.url('ibuy/customer/'.$customerId).'" data-rel="box->clear" data-width="640" data-webview="'.htmlspecialchars($customerInfo->name).'"><i class="icon -material">person</i><span class="-sg-is-desktop">ลูกค้า</span></a>');
	$headerUi->add('<a class="sg-action btn -link" href="'.url('ibuy/customer/'.$customerId.'/info.map').'" data-rel="box" data-width="640" data-height="640" data-refresh="no" data-webview="'.$customerInfo->name.'"><i class="icon -material -customer-pin'.($customerInfo->info->location ? ' -active' : '').'">person_pin</i><span class="-sg-is-desktop">แผนที่</span></a>');


	$headerDrop->add('<a class="sg-action" href="'.url('ibuy/customer/'.$customerId.'/info/ticket.remove/'.$ticketId).'" data-rel="notify" data-done="back | load->replace:#ibuy-customer-view" data-title="ลบรายการนี้" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -material">delete</i><span>Trash</span></a>');

	$headerUi->add(sg_dropbox($headerDrop->build()));

	$ret = '<section id="ibuy-customer-info-ticket" class="ibuy-customer-info-ticket sg-view -co-2" data-url="'.url('ibuy/customer/'.$customerId.'/info.ticket/'.$ticketId).'">';
	$ret .= '<header class="header -box -app-hide-header">'._HEADER_BACK.'<h3>'.$ticketInfo->problem.'</h3><nav class="nav">'.$headerUi->build().'</nav></header>';


	$ret .= '<div class="-sg-view">';


	$form = new Form(NULL, url('ibuy/customer/'.$customerId.'/info/ticket.reply/'.$ticketId), NULL, 'sg-form');
	$form->addData('checkValid', true);
	$form->addData('rel', 'notify');
	$form->addData('done', 'load->replace:#ibuy-customer-info-ticket:'.$currentUrl);

	$form->addField('issnid', array('type' => 'hidden', 'value' => $ticketInfo->info->issnid));

	$form->addField(
		'detail',
		array(
			'type' => 'textarea',
			'class' => '-fill',
			'rows' => 3,
			'require' => 'true',
			'value' => '',
			'placeholder' => 'เพิ่มบันทึกข้อความ',
		)
	);

	$form->addField(
		'save',
		array(
			'type' => 'button',
			'value' => '<i class="icon -material">done</i><span>{tr:SAVE}</span>',
			'container' => '{class: "-sg-text-right"}',
		)
	);

	$ret .= $form->build();

	$threadCard = new Ui('div', 'ui-card');

	$ticketInfo->thread[] = $ticketInfo->info;

	foreach ($ticketInfo->thread as $rs) {
		$isEditItem = $isEdit || i()->uid == $rs->uid;

		$ui = new Ui();
		if ($isEditItem) {
			$ui->add('<a class="sg-action" href="'.url('ibuy/customer/'.$customerId.'/service.form/'.$rs->tickid).'" data-rel="box" data-width="640" data-done=""><i class="icon -material -gray">edit</i></a>');
		}

		if ($isEditItem && !empty($rs->thread)) {
			$ui->add('<a class="sg-action" href="'.url('ibuy/customer/'.$customerId.'/info/ticket.thread.remove/'.$rs->tickid).'" data-rel="notify" data-done="remove:parent .ui-card>.ui-item" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a>');
		}
		$menu = '<nav class="nav -icons -hover -top-right">'.$ui->build().'</nav>';
		//$ret .= '<nav class="nav -header -sg-text-right">'.$headerUi->build().'</nav>';

		$photoAlbumUi = new Ui(NULL,'ui-album -justify-left');
		foreach (explode(',',$rs->photos) as $photoItem) {
			list($fid,$photofile)=explode('|', $photoItem);
			if (!$fid) continue;

			$photoInfo=model::get_photo_property($photofile);

			$photoNavUi = new Ui('span');
			if ($isEditItem) {
				$photoNavUi->add('<a class="sg-action" href="'.url('ibuy/customer/'.$customerId.'/info/photo.delete/'.$fid).'" data-rel="notify" data-done="remove:parent .ui-album>.ui-item" data-title="ลบภาพ" data-confirm="ต้องการลบภาพนี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a>');
			}
			$photo_alt = $item->title;
			$photoAlbumUi->add('<a class="sg-action" data-group="photo'.$actionId.'" href="'.$photoInfo->_src.'" data-rel="img" title="'.htmlspecialchars($photo_alt).'">'
				. '<img class="photoitem -'.($photoInfo->_size->width > $photoInfo->_size->height ? 'wide' : 'tall').'" src="'.$photoInfo->_url.'" width="200" />'
				. '</a>'
				. '<nav class="nav -icons -hover">'.$photoNavUi->build().'</nav>',
				'{class: "-hover-parent"}'
			);
		}

		$cardUi = new Ui();
		if ($isEditItem) {
			$cardUi->add('<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('ibuy/customer/'.$customerId.'/info/photo.upload/'.$rs->tickid).'" data-rel="#ticket-'.$rs->tickid.' .ui-album" data-append="li"><input type="hidden" name="tagname" value="" /><span class="btn -link fileinput-button"><i class="icon -material">photo_camera</i><span>ถ่ายภาพ</span><input type="file" name="photo[]" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span><input class="-hidden" type="submit" value="upload" /></form>');
		}

		$threadCard->add(
			'<div class="header"><b>'.$ticketInfo->problem.'@'.sg_date($rs->created, _DATE_FORMAT).'</b>'
			. '</div>'
			. $menu
			. '<div class="detail">'
			. ($rs->productname ? '<h5>สินค้า : '.$rs->productname.'</h5>' : '')
			. nl2br($rs->detail)
			//. print_o($rs,'$rs')
			. '</div>'
			. $photoAlbumUi->build(true)
			. '<nav class="nav -card">'.$cardUi->build().'</nav>',

			'{id: "ticket-'.$rs->tickid.'" ,class: "-hover-parent"}'
		);
	}

	$ret .= $threadCard->build();

	$ret .= '</div>';

	$ret .= '<div class="-sg-view">';

	$ret .= '<header class="header">'._HEADER_BACK.'<h6><a class="sg-action" href="'.url('ibuy/customer/'.$customerId).'" data-rel="box->clear" data-width="640" data-webview="'.htmlspecialchars($customerInfo->name).'">'.$customerInfo->name.'</a></h6></header>'
		. '<div>'
		. $ticketInfo->info->status.'<br />'
		. 'By '.$ticketInfo->info->posterName.'<br />'
		. 'Created '.sg_date($ticketInfo->info->created, cfg('dateformat')).'<br />'
		. 'Updated '. ($ticketInfo->info->lastAction ? sg_date($ticketInfo->info->lastAction, cfg('dateformat')) : 'None')
		. '</div>';
	$ret .= '<header class="header">'._HEADER_BACK.'<h6>Services</h6></header>';

	$ret .= '</div>';

	//$ret .= print_o($ticketInfo, '$ticketInfo');
	//$ret .= print_o($customerInfo, '$customerInfo');

	$ret .= '</section>';

	return $ret;
}
?>