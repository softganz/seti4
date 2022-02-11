<?php
/**
 * Rander note unit
 *
 * @param Record Set $commentInfo
 * @return String
 */

$debug = false;

function view_green_activity_comment_render($commentInfo, $options = '{}') {
	$defaults = '{debug:false, showEdit: true, page: "web"}';
	$options = sg_json_decode($options,$defaults);
	$debug = $options->debug;

	$isAdmin = is_admin('green');
	$isOwner = i()->uid == $commentInfo->uid;
	$isEdit = $options->showEdit && ($isAdmin || $isOwner);

	//$ret .= print_o($options,'$options');

	$cameraStr = $options->page == 'app' ? 'ถ่ายภาพ' : 'อัพโหลดภาพถ่าย';

	list($x, $doType) = explode('-', $commentInfo->tagname);


	$headerUi = new Ui();
	$dropUi = new Ui();
	if ($isEdit) {
		$dropUi->add('<a class="sg-action" href="'.url('green/my/info/activity.comment.delete/'.$commentInfo->msgid).'" title="ลบข้อความ" data-rel="none" data-title="ลบข้อความ" data-confirm="ลบข้อความนี้ทิ้ง กรุณายืนยัน?" data-done="remove:parent .ui-card>.ui-item"><i class="icon -delete"></i><span>ลบข้อความ</span></a>');
	}

	if ($dropUi->count()) $headerUi->add(sg_dropbox($dropUi->build()));

	$posterUrl = $options->page == 'app' ? '<a class="sg-action" href="'.url('green/u/'.$commentInfo->uid, array('ref' => 'app')).'" data-rel="box" data-webview="'.$commentInfo->posterName.'" data-width="480" data-height="80%">' : '<a class="sg-action" href="'.url('green/u/'.$commentInfo->uid).'" data-rel="box" data-done="moveto:0,0" data-width="480" data-height="80%">';


	// Create Photo Album
	$photoAlbum = new Ui(NULL, 'ui-album -justify-left');
	$photoAlbum->addId('green-activity-photo-'.$commentInfo->msgid);

	if ($commentInfo->photoList) {
		foreach (explode(',',$commentInfo->photoList) as $photoItem) {
			list($fid,$photofile)=explode('|', $photoItem);
			if (!$fid) continue;

			$photoInfo=model::get_photo_property($photofile);

			$Ui = new Ui('span');
			$Ui->addConfig('nav', '{class: "nav -icons -hover"}');
			if ($isEdit) {
				$Ui->add('<a class="sg-action -no-print" href="'.url('green/my/info/activity.photo.delete/'.$commentInfo->msgid,array('fid' => $fid)).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="notify" data-done="remove:parent li"><i class="icon -material">cancel</i></a>');
			}

			$photoAlbum->add(
				'<img class="photoitem -'.($photoInfo->_size->width > $photoInfo->_size->height ? 'wide' : 'tall').'" src="'.$photoInfo->_url.'" />'
				. $Ui->build(),
				array(
					'id' => 'green-activity-photo-'.$fid,
					'class' => 'sg-action -hover-parent',
					'href' => $photoInfo->_url,
					'data-rel' => 'img',
					'data-group' => 'imed-'.$commentInfo->seq,
					'onclick' => '',
				)
			);
		}
	}

	/*
	if ($isOwner) {
		$photoAlbum->add(
			'<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('green/my/info/activity.photo.upload/'.$commentInfo->msgid).'" data-rel="#green-activity-photo-'.$commentInfo->msgid.'" data-before="li">'
			. '<input type="hidden" name="tagname" value="activity" />'
			. '<span class="btn -link fileinput-button"><i class="icon -material">photo_camera</i>'
			. '<span class="-sg-is-desktop">'.$cameraStr.'</span>'
			. '<input type="file" name="photo[]" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span>'
			. '<input class="-hidden" type="submit" value="upload" />'
			. '</form>',
			array('class' => '-upload-btn')
		);
	}
	*/



	// Create Header
	$ret .= ($headerUi->count() ? '<nav class="nav -header -hover -sg-text-right">'.$headerUi->build().'</nav>'._NL : '');

	$ret .= '<div class="header">'
		. '<span class="profile">'
		. $posterUrl
		. '<img class="poster-photo" src="'.model::user_photo($commentInfo->username).'" width="24" height="24" alt="" />'
		. '</a> '
		. '</span><!-- profile -->'._NL
		//. ($headerUi->count() ? '<nav class="nav -header -sg-text-right">'.$headerUi->build().'</nav>'._NL : '')
		. '</div><!-- header -->'._NL;


	//$ret .= print_o($commentInfo,'$commentInfo').print_o($locInfo, '$locInfo');



	$ret .= '<div class="detail">'
		. '<span class="profile">'
		. $posterUrl
		. '<span class="poster-name">'.$commentInfo->posterName.'</span>'
		. '</a>'
		. '</span>'
		. nl2br($commentInfo->message);
	$ret .= $photoAlbum->show(false);
	$ret .= '</div><!-- detail -->'._NL;

	/*
		. '<span class="timestamp"> เมื่อ '
		. sg_date($commentInfo->created,'ว ดด ปป H:i'). ' น. '
		. '</span><!-- timestamp -->'._NL
	*/


	// Action Button
	/*
	$cardUi = new Ui();
	$cardUi->addConfig('nav', '{class: "nav -card"}');
	if (i()->ok) {
		$cardUi->add('<a class="sg-action btn -link" href="'.url('underconstruction/1/green/msg/like/'.$commentInfo->msgid).'" data-rel="box" data-width="640" data-width="80%"><i class="icon -material">thumb_up</i><span>Like</span></a>');
		$cardUi->add('<a class="sg-action btn -link" href="'.url('underconstruction/1/green/msg/comment/'.$commentInfo->msgid).'" data-rel="box" data-width="640" data-width="80%"><i class="icon -material">comment</i><span>Comment</span></a>');
		$cardUi->add('<a class="sg-action btn -link" href="'.url('underconstruction/1/green/msg/share/'.$commentInfo->msgid).'" data-rel="box" data-width="640" data-width="80%"><i class="icon -material">share</i><span>Share</span></a>');
	}

	$dropUi = new Ui();

	if ($dropUi->count()) $cardUi->add(sg_dropbox($dropUi->build()));


	if ($cardUi->count()) $ret .= $cardUi->build();
	*/


	/*
	if (i()->ok) {
		$ret .= '<div class="-comment-form">'
			. '<form class="form sg-form -sg-flex" action="'.url('green/my/info/activity.comment.save/'.$commentInfo->msgid).'" data-rel="notify">'
			. '<img class="poster-photo" src="'.model::user_photo(i()->username).'" width="32" height="32" alt="" style="border-radius: 50%;" />'
			. '<!-- <input type="text" class="form-text" placeholder="Write a comment..." > -->'
			. '<textarea class="form-textarea -fill" name="message" rows="1" placeholder="Write a comment..."></textarea>'
			. '</form>'
			. '</div>';
	}
	*/

	return $ret;
}
?>