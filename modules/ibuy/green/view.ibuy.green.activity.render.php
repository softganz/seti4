<?php
/**
 * Rander note unit
 *
 * @param Record Set $activityInfo
 * @return String
 */

$debug = false;

function view_ibuy_green_activity_render($activityInfo, $options = '{}') {
	$defaults = '{debug:false, showEdit: true, page: "web"}';
	$options = sg_json_decode($options,$defaults);
	$debug = $options->debug;

	$isOwner = i()->uid == $activityInfo->uid;
	$isEdit = $options->showEdit && (is_admin('ibuy') || $isOwner);

	//$ret .= print_o($options,'$options');

	$isAdmin = is_admin('ibuy');
	$cameraStr = $options->page == 'app' ? 'ถ่ายภาพ' : 'อัพโหลดภาพถ่าย';

	list($x, $doType) = explode(',', $activityInfo->tagname);

	$isPlant = $activityInfo->tagname == 'GREEN,PLANT' && $activityInfo->plantid;

	$headerUi = new Ui();
	$dropUi = new Ui();
	if ($isEdit) {
		$dropUi->add('<a class="sg-action" href="'.url('ibuy/my/info/activity.delete/'.$activityInfo->msgid).'" title="ลบรายการบันทึกนี้ทิ้ง" data-rel="notify" data-title="ลบรายการบันทึก" data-confirm="ลบรายการบันทึกนี้ทิ้ง กรุณายืนยัน?" data-done="remove:parent .ui-card>.ui-item"><i class="icon -delete"></i><span>ลบบันทึกกิจกรรม</span></a>');
	}

	if ($dropUi->count()) $headerUi->add(sg_dropbox($dropUi->build()));

	if ($activityInfo->orgid) {
		$posterUrl = url('ibuy/green/shop/'.$activityInfo->orgid, array('ref' => $options->page == 'app' ? 'app' : NULL));
	} else {
		$posterUrl = url('ibuy/green/', array('u' => $activityInfo->uid, 'ref' => $options->page == 'app' ? 'app' : NULL));
	}
	$posterLink = '<a class="sg-action" href="'.$posterUrl.'" data-webview="'.$activityInfo->posterName.'">';

	if ($activityInfo->locJson) $locInfo = json_decode($activityInfo->locJson);
	$locationName = $activityInfo->locname
		. ($locInfo->locname ? ($activityInfo->locname ? '('.$locInfo->locname.')' : $locInfo->locname) : '');



	// Create Photo Album
	$photoAlbum = new Ui(NULL, 'ui-album -justify-left');
	$photoAlbum->addId('ibuy-activity-photo-'.$activityInfo->msgid);
	if ($activityInfo->photoList) {
		foreach (explode(',',$activityInfo->photoList) as $photoItem) {
			list($fid,$photofile)=explode('|', $photoItem);
			if (!$fid) continue;

			$photoInfo=model::get_photo_property($photofile);

			$Ui = new Ui('span');
			$Ui->addConfig('nav', '{class: "nav -icons -hover"}');
			if ($isEdit) {
				$Ui->add('<a class="sg-action -no-print" href="'.url('ibuy/my/info/activity.photo.delete/'.$activityInfo->msgid,array('fid' => $fid)).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="none" data-removeparent="li"><i class="icon -material">cancel</i></a>');
			}

			$photoAlbum->add(
				'<img class="photoitem -'.($photoInfo->_size->width > $photoInfo->_size->height ? 'wide' : 'tall').'" src="'.$photoInfo->_url.'" />'
				. $Ui->build(),
				array(
					'id' => 'ibuy-activity-photo-'.$fid,
					'class' => 'sg-action -hover-parent',
					'href' => $photoInfo->_url,
					'data-rel' => 'img',
					'data-group' => 'imed-'.$activityInfo->seq,
					'onclick' => '',
				)
			);
		}
	}

	list($treeModule, $treeTagName) = explode(',', $activityInfo->tagname);

	if ($isOwner) {
		$photoAlbum->add(
			'<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('ibuy/my/info/photo.upload/'.$activityInfo->msgid).'" data-rel="#ibuy-activity-photo-'.$activityInfo->msgid.'" data-before="li">'
			. '<input type="hidden" name="module" value="'.$treeModule.'" />'
			. '<input type="hidden" name="tagname" value="'.$treeTagName.'" />'
			. '<span class="btn -link fileinput-button"><i class="icon -material">photo_camera</i>'
			. '<span class="-sg-is-desktop">'.$cameraStr.'</span>'
			. '<input type="file" name="photo[]" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span>'
			. '<input class="-hidden" type="submit" value="upload" />'
			. '</form>',
			array('class' => '-upload-btn')
		);
	}



	// Create Header
	$ret .= '<div class="header">'
		. '<span class="profile">'
		. $posterLink
		. '<img class="poster-photo" src="'.model::user_photo($activityInfo->username).'" width="32" height="32" alt="" />'
		. '<span class="poster-name">'.$activityInfo->posterName.'</span>'
		. '</a> '
		. '<span class="-visit-patient">'
		. ($activityInfo->tagname ? ' {tr:'.$doType.'}' : '')
		. ($activityInfo->landname ? '<a class="sg-action" href="'.url('ibuy/green/land/'.$activityInfo->landid.'/activity').'"> @'.$activityInfo->landname.'</a>' : '')
		. ($activityInfo->locname ? ' @'.$activityInfo->locname : '')
		. ($locInfo->location ? '<i class="icon -material">place</i>' : '')
		. ($activityInfo->staytime ? ' '.$activityInfo->staytime.' นาที' : '')
		.'</span><!-- -visit-patient -->'
		. '<span class="timestamp"> เมื่อ '
		. sg_date($activityInfo->created,'ว ดด ปป H:i'). ' น. '
		. '</span><!-- timestamp -->'._NL
		. '</span><!-- profile -->'._NL
		. ($headerUi->count() ? '<nav class="nav -header -sg-text-right">'.$headerUi->build().'</nav>'._NL : '')
		. '</div><!-- header -->'._NL;



	//$ret .= print_o($activityInfo,'$activityInfo').print_o($locInfo, '$locInfo');


	$ret .= '<div class="detail'.($isPlant ? ' sg-view -co-2' : '').'">'._NL;

	if ($isPlant) {
		$form = new Form(NULL, url('ibuy/my/info/book.save/'.$activityInfo->plantid), NULL, 'sg-form -book-form');
		$form->addData('checkValid', true);
		$form->addData('rel', 'notify');
		$form->addData('done', 'load->replace:#ibuy-activity-'.$activityInfo->msgid.':'.url('ibuy/green/activity/render/'.$activityInfo->msgid));

		if ($activityInfo->balance > 0) {
			$form->addField(
				'qty',
				array(
					'type' => 'text',
					'label' => 'จำนวนจอง',
					'id' => NULL,
					'value' => 1,
					'attr' => array('data-balance' => $activityInfo->balance),
					'pretext' => '<div class="input-prepend -nowrap"><span><a id="ibuy-plant-down" class="btn -link -ibuy-plant-down" href="javascript:void(0)"><i class="icon -material">remove</i></a></span></div>',
					'posttext' => '<div class="input-append -nowrap"></span><span>กก.</span><span><a id="ibuy-plant-up" class="btn -link -ibuy-plant-up" href="javascript:void(0)"><i class="icon -material">add</i></a></div>',
					'container' => '{class: "-group"}',
				)
			);

			$form->addField(
				'save',
				array(
					'type' => 'button',
					'class' => '-fill',
					'value' => '<i class="icon -material">done_all</i><span>จองเลย</span>',
				)
			);
		} else {
			$form->addText('<span class="btn -link -fill"><i class="icon -material -gray">cancel</i><span>ปิดรับการจอง</span></span>');
		}


		$ret .= '<div class="-sg-view">'
			. '<div class="header"><h2>'.$activityInfo->productname.'</h2></div>'
			. '<p>เริ่มลงแปลง '.($activityInfo->startdate ? sg_date($activityInfo->startdate, 'ว ดด ปปปป') : '').' '
			. 'วันเก็บเกี่ยว '.($activityInfo->cropdate ? sg_date($activityInfo->cropdate, 'ว ดด ปปปป') : '').'<br />'
			. 'ปริมาณผลผลิต <b>'.$activityInfo->qty.'</b> '.$activityInfo->unit.'<br />'
			. 'ปริมาณคงเหลือ <b>'.$activityInfo->balance.'</b> '.$activityInfo->unit.'<br />'
			. '</p>'
			. ($activityInfo->detail ? '<p>'.nl2br($activityInfo->detail).'</p>' : '')
			. $photoAlbum->build(false)
			. '</div>';
		$ret .= '<div class="-sg-view">'
			. '<div class="ibuy-book-label">'
			. '<div class="-normal-price">ราคาขาย <span class="-money">'.number_format($activityInfo->saleprice,2).'</span> บาท</div>'
			. '<div class="-book-price">ราคาจอง <span class="-money">'.number_format($activityInfo->bookprice,2).'</span> บาท</div>'
			. '</div>'
			. $form->build()
			. '<nav class="nav -sg-text-center"><a class="sg-action btn -link -fill" href="'.url('ibuy/green/plant/'.$activityInfo->plantid).'" data-webview="'.htmlspecialchars($activityInfo->productname).'">รายละเอียด</a></nav>'
			. '</div>';
	} else {
			$ret .= '<div>'
			. ($isEdit ? nl2br($activityInfo->message) /*view::inlineedit(
				array('group'=>'service','fld'=>'rx','tr'=>$activityInfo->seq,'psnid'=>$activityInfo->psnid,'button'=>'yes','ret'=>'text','value'=>$activityInfo->rx),
				str_replace("\n",'<br />',$activityInfo->message),
				$isEdit
				,'textarea'
			)*/ : nl2br($activityInfo->message))
			.'</div>'._NL;
		$ret .= $photoAlbum->build(false);
	}

	$ret .= '</div><!-- detail -->'._NL;

	$ret .= '<div class="-activity-status">'
		. '<span class="-like-status'.($activityInfo->liketimes ? '' : ' -hidden').'"><span class="liketimes">'.number_format($activityInfo->liketimes).'</span> Likes</span> '
		. '<span class="-comment-status"><span class="commenttimes">'.number_format($activityInfo->commentCount).'</span> Comments</span> '
		. '<span class="-comment-status"><span class="commenttimes">'.number_format($activityInfo->shareCount).'</span> Shares</span>'
		. '</div><!-- -activity-status -->'._NL;




	// Action Button
	$cardUi = new Ui();
	$cardUi->addConfig('nav', '{class: "nav -card"}');
	if (i()->ok) {
		$cardUi->add('<a class="sg-action btn -link '.($activityInfo->liked ? '-active' : '-inactive').'" href="'.url('ibuy/my/info/msg.like/'.$activityInfo->msgid).'" data-rel="none" data-callback="ibuyMsgLikeDone" xdata-options=\'{callback:"ibuyMsgLikeDone"}\'><i class="icon -material">thumb_up</i><span>Like</span></a>');
		$cardUi->add('<a class="btn -link" href="#ibuy-activity-comment-form-'.$activityInfo->msgid.'" data-rel="box" data-width="640" data-width="80%" onClick=\'$("#ibuy-activity-comment-form-'.$activityInfo->msgid.'").find(".form-textarea").focus()\'><i class="icon -material">comment</i><span>Comment</span></a>');
		$cardUi->add('<a class="x-sg-action btn -link" x-href="'.url('underconstruction/1/ibuy/msg/share/'.$activityInfo->msgid).'" data-rel="box" data-width="640" data-width="80%"><i class="icon -material">share</i><span>Share</span></a>');
	}

	$dropUi = new Ui();

	if ($dropUi->count()) $cardUi->add(sg_dropbox($dropUi->build()));


	if ($cardUi->count()) $ret .= $cardUi->build();


	//if ($activityInfo->commentCount) {
		$ret .= R::Page('ibuy.green.activity.comment',NULL,$activityInfo->msgid);
	//}


	if (i()->ok) {
		$ret .= '<div id="ibuy-activity-comment-form-'.$activityInfo->msgid.'" class="-comment-form">'
			. '<form class="form sg-form -sg-flex" action="'.url('ibuy/my/info/activity.comment.save/'.$activityInfo->msgid).'" data-rel="notify" data-done="load->replace:#ibuy-activity-'.$activityInfo->msgid.' .ibuy-green-activity-comment:'.url('ibuy/green/activity/comment/'.$activityInfo->msgid).'">'
			. '<img class="poster-photo" src="'.model::user_photo(i()->username).'" width="32" height="32" alt="" style="border-radius: 50%;" />'
			. '<!-- <input type="text" class="form-text" placeholder="Write a comment..." > -->'
			. '<textarea class="form-textarea -fill" name="message" rows="1" placeholder="Write a comment..."></textarea>'
			. '</form>'
			. '</div>';
	}

	return $ret;
}
?>