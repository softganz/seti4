<?php
/**
 * Green View :: Rander Activity Unit
 *
 * @param Record Set $activityInfo
 * @param Object $options
 * @return String
 */

$debug = false;

function view_green_activity_render($activityInfo, $options = '{}') {
	$defaults = '{debug:false, showEdit: true, page: "web"}';
	$options = sg_json_decode($options,$defaults);
	$debug = $options->debug;

	$isAdmin = is_admin('green');
	$isOwner = i()->uid == $activityInfo->uid;
	$isEdit = $options->showEdit && ($isAdmin || $isOwner);
	$isSoftganz = i()->username == 'momo';

	//$ret .= print_o($options,'$options');

	$cameraStr = $options->page == 'app' ? 'ถ่ายภาพ' : 'อัพโหลดภาพถ่าย';

	list($x, $doType) = explode(',', $activityInfo->tagname);

	$isPlant = $activityInfo->tagname == 'GREEN,PLANT' && $activityInfo->plantid;

	$headerUi = new Ui();
	$dropUi = new Ui();
	if ($isEdit) {
		$dropUi->add('<a class="sg-action" href="'.url('green/my/info/activity.delete/'.$activityInfo->msgid).'" title="ลบรายการบันทึกนี้ทิ้ง" data-rel="notify" data-title="ลบรายการบันทึก" data-confirm="ลบรายการบันทึกนี้ทิ้ง กรุณายืนยัน?" data-done="remove:parent .ui-card>.ui-item"><i class="icon -delete"></i><span>ลบบันทึกกิจกรรม</span></a>');
	}

	if ($dropUi->count()) $headerUi->add(sg_dropbox($dropUi->build()));

	if ($activityInfo->orgid) {
		$posterUrl = url('green/shop/'.$activityInfo->orgid, array('ref' => $options->page == 'app' ? 'app' : NULL));
	} else {
		$posterUrl = url('green/', array('u' => $activityInfo->uid, 'ref' => $options->page == 'app' ? 'app' : NULL));
	}

	$posterUrl = url('green/', array('u' => $activityInfo->uid, 'ref' => $options->page == 'app' ? 'app' : NULL));
	$posterLink = '<a class="sg-action" href="'.url('green/u/'.$activityInfo->uid).'" data-webview="'.$activityInfo->posterName.'">';

	if ($activityInfo->locJson) $locInfo = json_decode($activityInfo->locJson);
	$locationName = $activityInfo->locname
		. ($locInfo->locname ? ($activityInfo->locname ? '('.$locInfo->locname.')' : $locInfo->locname) : '');



	// Create Photo Album
	$photoAlbum = new Ui(NULL, 'ui-album -justify-left');
	$photoAlbum->addId('green-activity-photo-'.$activityInfo->msgid);
	if ($activityInfo->photoList) {
		foreach (explode(',',$activityInfo->photoList) as $photoItem) {
			list($fid,$photofile)=explode('|', $photoItem);
			if (!$fid) continue;

			$photoInfo=model::get_photo_property($photofile);

			$Ui = new Ui('span');
			$Ui->addConfig('nav', '{class: "nav -icons -hover"}');
			if ($isEdit) {
				$Ui->add('<a class="sg-action -no-print" href="'.url('green/my/info/activity.photo.delete/'.$activityInfo->msgid,array('fid' => $fid)).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="none" data-removeparent="li"><i class="icon -material">cancel</i></a>');
			}

			$photoAlbum->add(
				'<img class="photoitem -'.($photoInfo->_size->width > $photoInfo->_size->height ? 'wide' : 'tall').'" src="'.$photoInfo->_url.'" />'
				. $Ui->build(),
				array(
					'id' => 'green-activity-photo-'.$fid,
					'class' => 'sg-action -hover-parent',
					'href' => $photoInfo->_url,
					'data-rel' => 'img',
					'data-group' => 'imed-'.$activityInfo->msgid,
					'onclick' => '',
				)
			);
		}
	}

	list($treeModule, $treeTagName) = explode(',', $activityInfo->tagname);

	if ($isOwner) {
		$photoAlbum->add(
			'<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('green/my/info/photo.upload/'.$activityInfo->msgid).'" data-rel="#green-activity-photo-'.$activityInfo->msgid.'" data-before="li">'
			. '<input type="hidden" name="module" value="'.$treeModule.'" />'
			. '<input type="hidden" name="tagname" value="'.$treeTagName.'" />'
			. '<input type="hidden" name="orgid" value="'.$activityInfo->orgid.'" />'
			. '<span class="btn -link fileinput-button"><i class="icon -material">add_a_photo</i>'
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
		. ($activityInfo->landname ? '<a class="sg-action" href="'.url('green/land/'.$activityInfo->landid).'" data-webview="'.$activityInfo->landname.'"> @'.$activityInfo->landname.'</a>' : '')
		. ($activityInfo->locname ? ' @'.$activityInfo->locname : '')
		. ($locInfo->location ? '<i class="icon -material">place</i>' : '')
		. ($activityInfo->staytime ? ' '.$activityInfo->staytime.' นาที' : '')
		//. ($isSoftganz ? '@'.$activityInfo->msgid : '')
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
		$form = new Form(NULL, url('green/my/info/book.save/'.$activityInfo->plantid), NULL, 'sg-form -book-form');
		$form->addData('checkValid', true);
		$form->addData('rel', 'notify');
		$form->addData('done', 'load->replace:#green-activity-'.$activityInfo->msgid.':'.url('green/activity/render/'.$activityInfo->msgid));

		if ($activityInfo->balance > 0 && $activityInfo->bookprice > 0) {
			$form->addField(
				'qty',
				array(
					'type' => 'text',
					'label' => 'จำนวนจอง',
					'id' => NULL,
					'value' => 1,
					'attr' => array('data-balance' => $activityInfo->balance),
					'pretext' => '<div class="input-prepend -nowrap"><span><a id="green-plant-down" class="btn -link -green-plant-down" href="javascript:void(0)"><i class="icon -material">remove</i></a></span></div>',
					'posttext' => '<div class="input-append -nowrap"></span><span>กก.</span><span><a id="green-plant-up" class="btn -link -green-plant-up" href="javascript:void(0)"><i class="icon -material">add</i></a></div>',
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
			. 'ปริมาณผลผลิต <b>'.number_format($activityInfo->qty,2).'</b> '.$activityInfo->unit.'<br />'
			. 'ปริมาณคงเหลือ <b>'.number_format($activityInfo->balance,2).'</b> '.$activityInfo->unit.'<br />'
			. '</p>'
			. ($activityInfo->detail ? '<p>'.nl2br($activityInfo->detail).'</p>' : '')
			. $photoAlbum->show(false)
			. '</div>';
		$ret .= '<div class="-sg-view">'
			. '<div class="green-book-label">'
			. '<div class="-normal-price">ราคาขาย <span class="-money">'.number_format($activityInfo->saleprice,2).'</span> บาท</div>'
			. '<div class="-book-price">ราคาจอง <span class="-money">'.number_format($activityInfo->bookprice,2).'</span> บาท</div>'
			. '</div>'
			. $form->build()
			. '<nav class="nav -sg-text-center"><a class="sg-action btn -link -fill" href="'.url('green/plant/'.$activityInfo->plantid).'" data-webview="'.htmlspecialchars($activityInfo->productname).'">รายละเอียด</a></nav>'
			. '</div>';
	} else {
			$ret .= '<div>'
			. ($isEdit ? nl2br($activityInfo->message) /*view::inlineedit(
				array('group'=>'service','fld'=>'rx','tr'=>$activityInfo->msgid,'psnid'=>$activityInfo->psnid,'button'=>'yes','ret'=>'text','value'=>$activityInfo->rx),
				str_replace("\n",'<br />',$activityInfo->message),
				$isEdit
				,'textarea'
			)*/ : nl2br($activityInfo->message))
			.'</div>'._NL;
		$ret .= $photoAlbum->show(false);
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
		$cardUi->add('<a class="sg-action btn -link '.($activityInfo->liked ? '-active' : '-inactive').'" href="'.url('green/my/info/msg.like/'.$activityInfo->msgid).'" data-rel="none" data-callback="greenMsgLikeDone" xdata-options=\'{callback:"greenMsgLikeDone"}\'><i class="icon -material">thumb_up</i><span>Like</span></a>');
		$cardUi->add('<a class="btn -link" href="#green-activity-comment-form-'.$activityInfo->msgid.'" data-rel="box" data-width="640" data-width="80%" onClick=\'$("#green-activity-comment-form-'.$activityInfo->msgid.'").find(".form-textarea").focus()\'><i class="icon -material">comment</i><span>Comment</span></a>');
		$cardUi->add('<a class="x-sg-action btn -link" x-href="'.url('underconstruction/1/green/msg/share/'.$activityInfo->msgid).'" data-rel="box" data-width="640" data-width="80%"><i class="icon -material">share</i><span>Share</span></a>');
	}

	$dropUi = new Ui();

	if ($dropUi->count()) $cardUi->add(sg_dropbox($dropUi->build()));


	if ($cardUi->count()) $ret .= $cardUi->build();


	//if ($activityInfo->commentCount) {
		$ret .= R::Page('green.activity.comment',NULL,$activityInfo->msgid);
	//}


	if (i()->ok) {
		$ret .= '<div id="green-activity-comment-form-'.$activityInfo->msgid.'" class="-comment-form">'
			. '<form class="form sg-form -sg-flex" action="'.url('green/my/info/activity.comment.save/'.$activityInfo->msgid).'" data-rel="notify" data-done="load->replace:#green-activity-'.$activityInfo->msgid.' .green-activity-comment:'.url('green/activity/comment/'.$activityInfo->msgid).'">'
			. '<img class="poster-photo" src="'.model::user_photo(i()->username).'" width="32" height="32" alt="" style="border-radius: 50%;" />'
			. '<!-- <input type="text" class="form-text" placeholder="Write a comment..." > -->'
			. '<textarea class="form-textarea -fill" name="message" rows="1" placeholder="Write a comment..."></textarea>'
			. '</form>'
			. '</div>';
	}

	return $ret;
}
?>