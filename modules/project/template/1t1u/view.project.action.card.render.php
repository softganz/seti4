<?php
/**
 * Project View :: Rander Activity Unit
 *
 * @param Record Set $actionInfo
 * @param Object $options
 * @return String
 */

$debug = false;

function view_project_action_card_render($actionInfo, $options = '{}') {
	$defaults = '{debug:false, showEdit: true, page: "web"}';
	$options = SG\json_decode($options,$defaults);
	$debug = $options->debug;

	$projectId = $actionInfo->tpid;
	$actionId = $actionInfo->actionId;

	$isAdmin = is_admin('project');
	$isOwner = i()->uid == $actionInfo->uid;
	$isEdit = $options->showEdit && ($isAdmin || $isOwner);
	$isSoftganz = i()->username == 'momo';

	//$ret .= print_o($options,'$options');

	$cameraStr = $options->page == 'app' ? 'ถ่ายภาพ' : 'อัพโหลดภาพถ่าย';

	list($x, $doType) = explode(',', $actionInfo->tagname);

	$headerUi = new Ui();
	$headerUi->addConfig('container', '{tag: "nav", class: "nav -header -sg-text-right"}');
	$dropUi = new Ui();
	if ($isEdit) {
		$headerUi->add('<a class="sg-action btn -link" href="'.url('project/app/action/form/'.$projectId.'/'.$actionId).'" data-rel="box" data-webview="แก้ไข" data-height="100%"><i class="icon -material">edit</i></a>');
		$dropUi->add('<a class="sg-action" href="'.url('project/'.$projectId.'/info/action.remove/'.$actionId).'" title="ลบรายการบันทึกนี้ทิ้ง" data-rel="notify" data-title="ลบรายการบันทึก" data-confirm="ลบรายการบันทึกนี้ทิ้ง กรุณายืนยัน?" data-done="remove:parent .ui-card>.ui-item"><i class="icon -delete"></i><span>ลบบันทึกกิจกรรม</span></a>');
	}

	if ($dropUi->count()) $headerUi->add(sg_dropbox($dropUi->build()));

	$posterUrl = url('project/app/activity/',array('u'=>$actionInfo->uid));
	$posterLink = '<a class="sg-action" href="'.$posterUrl.'" data-webview="'.$actionInfo->ownerName.'">';


	// Create Photo Album
	$photoAlbum = new Ui(NULL, 'ui-album -justify-left');
	$photoAlbum->addId('project-activity-photo-'.$actionId);
	if ($actionInfo->photos) {
		foreach (explode(',',$actionInfo->photos) as $photoItem) {
			list($fid,$photofile) = explode('|', $photoItem);
			if (!$fid || !is_numeric($fid)) continue;

			$photoInfo = model::get_photo_property($photofile);

			$Ui = new Ui('span');
			$Ui->addConfig('nav', '{class: "nav -icons -hover"}');
			if ($isEdit) {
				$Ui->add('<a class="sg-action -no-print" href="'.url('project/'.$projectId.'/info/photo.delete/'.$fid).'" title="ลบภาพนี้" data-confirm="ยืนยันว่าจะลบภาพนี้จริง?" data-rel="none" data-removeparent="li"><i class="icon -material">cancel</i></a>');
			}

			$photoAlbum->add(
				'<img class="photoitem -'.($photoInfo->_size->width > $photoInfo->_size->height ? 'wide' : 'tall').'" src="'.$photoInfo->_url.'" />'
				. $Ui->build(),
				array(
					'id' => 'project-activity-photo-'.$fid,
					'class' => 'sg-action -hover-parent',
					'href' => $photoInfo->_url,
					'data-rel' => 'img',
					'data-group' => 'project-'.$actionId,
					'onclick' => '',
				)
			);
		}
	}

	if ($isOwner) {
		$photoAlbum->add(
			'<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('project/'.$projectId.'/info/photo.upload/'.$actionId).'" data-rel="#project-activity-photo-'.$actionId.'" data-before="li">'
			. '<input type="hidden" name="tagname" value="action" />'
			. '<span class="btn -link fileinput-button"><i class="icon -material">add_a_photo</i>'
			. '<span class="-sg-is-desktop">'.$cameraStr.'</span>'
			. '<input type="file" name="photo[]" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span>'
			. '<input class="-hidden" type="submit" value="upload" />'
			. '</form>',
			array('class' => '-upload-btn')
		);
	}



	// Create Header
	$ret .= '<div class="sg-action header" href="'.$posterUrl.'" data-webview="'.htmlspecialchars($actionInfo->ownerName).'">'
		. '<span class="profile">'
		. $posterLink
		. '<img class="poster-photo" src="'.model::user_photo($actionInfo->username).'" width="32" height="32" alt="" />'
		. '<span class="poster-name">'.$actionInfo->ownerName.'</span>'
		. '</a>'
		. '<span class="timestamp">'
		. '<b>'.sg_date($actionInfo->actionDate, 'ว ดด ปปปป').'</b>'
		. ' บันทึก '.sg_date($actionInfo->created,'ว ดด ปปปป H:i'). ' น. '
		. ($isAdmin ? ' on '.$actionInfo->appagent : '')
		. '</span><!-- timestamp -->'._NL
		. '</span><!-- profile -->'._NL
		. ($headerUi->count() ? $headerUi->build()._NL : '')
		. '</div><!-- header -->'._NL;



	//$ret .= print_o($actionInfo,'$actionInfo').print_o($locInfo, '$locInfo');


	$ret .= '<div class="detail">'._NL;

	$ret .= '<div>'
		. '<h5>'.$actionInfo->title.' @'.$actionInfo->projectTitle.($actionInfo->parentTitle ? '/'.$actionInfo->parentTitle : '').'</h5>'
		. ($isEdit ? nl2br($actionInfo->actionReal) /*view::inlineedit(
				array('group'=>'service','fld'=>'rx','tr'=>$actionInfo->msgid,'psnid'=>$actionInfo->psnid,'button'=>'yes','ret'=>'text','value'=>$actionInfo->rx),
				str_replace("\n",'<br />',$actionInfo->message),
				$isEdit
				,'textarea'
			)*/ : nl2br($actionInfo->actionReal))
			.'</div>'._NL;
	$ret .= $photoAlbum->build(false);

	$ret .= '</div><!-- detail -->'._NL;

	/*
	$ret .= '<div class="-activity-status">'
		. '<span class="-like-status'.($actionInfo->liketimes ? '' : ' -hidden').'"><span class="liketimes">'.number_format($actionInfo->liketimes).'</span> Likes</span> '
		. '<span class="-comment-status"><span class="commenttimes">'.number_format($actionInfo->commentCount).'</span> Comments</span> '
		. '<span class="-comment-status"><span class="commenttimes">'.number_format($actionInfo->shareCount).'</span> Shares</span>'
		. '</div><!-- -activity-status -->'._NL;
	*/



	// Action Button
	$cardUi = new Ui();
	$cardUi->addConfig('nav', '{class: "nav -card"}');
	if ($isEdit) {
		if ($actionInfo->prtype == 'ชุดโครงการ') {
			$cardUi->add('<a class="sg-action btn -link" href="'.url('project/'.$actionInfo->projectId.'/info.expense/'.$actionInfo->actionId).'" data-rel="box" data-webview="บันทึกค่าใช้จ่ายกิจกรรม"><i class="icon -material">paid</i><span>ค่าใช้จ่าย</span></a>');
		}

		if ($actionInfo->prtype == 'โครงการ' || in_array($actionInfo->ownertype,array(_PROJECT_OWNERTYPE_GRADUATE, _PROJECT_OWNERTYPE_STUDENT, _PROJECT_OWNERTYPE_PEOPLE))) {
			$cardUi->add('<a class="sg-action btn -link" href="'.url('project/'.$projectId.'/info.train.form/'.$actionInfo->trainList,array('actionId' => $actionId)).'" data-rel="box" data-width="480" data-webview="อบรม"><i class="icon -material -'.($actionInfo->trainList ? 'sg-active' : 'sg-inactive').'">groups</i><span>อบรม</span></a>');
		}
		//$cardUi->add('<a class="btn -link" href="#green-activity-comment-form-'.$actionInfo->msgid.'" data-rel="box" data-width="640" data-width="80%" onClick=\'$("#green-activity-comment-form-'.$actionInfo->msgid.'").find(".form-textarea").focus()\'><i class="icon -material">comment</i><span>Comment</span></a>');
		//$cardUi->add('<a class="x-sg-action btn -link" x-href="'.url('underconstruction/1/green/msg/share/'.$actionInfo->msgid).'" data-rel="box" data-width="640" data-width="80%"><i class="icon -material">share</i><span>Share</span></a>');
	}

	$dropUi = new Ui();

	if ($dropUi->count()) $cardUi->add(sg_dropbox($dropUi->build()));


	if ($cardUi->count()) $ret .= $cardUi->build();


	//if ($actionInfo->commentCount) {
	//	$ret .= R::Page('green.activity.comment',NULL,$actionInfo->msgid);
	//}


	/*
	if (i()->ok) {
		$ret .= '<div id="green-activity-comment-form-'.$actionInfo->msgid.'" class="-comment-form">'
			. '<form class="form sg-form -sg-flex" action="'.url('green/my/info/activity.comment.save/'.$actionInfo->msgid).'" data-rel="notify" data-done="load->replace:#green-activity-'.$actionInfo->msgid.' .green-activity-comment:'.url('green/activity/comment/'.$actionInfo->msgid).'">'
			. '<img class="poster-photo" src="'.model::user_photo(i()->username).'" width="32" height="32" alt="" style="border-radius: 50%;" />'
			. '<!-- <input type="text" class="form-text" placeholder="Write a comment..." > -->'
			. '<textarea class="form-textarea -fill" name="message" rows="1" placeholder="Write a comment..."></textarea>'
			. '</form>'
			. '</div>';
	}
	*/

	//$ret .= print_o($actionInfo,'$actionInfo');

	return $ret;
}
?>