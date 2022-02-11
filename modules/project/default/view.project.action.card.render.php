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
		$headerUi->add('<a class="sg-action btn -link" href="'.url('project/app/action/form/'.$projectId.'/'.$actionId).'" data-rel="box" data-width="full"><i class="icon -material">edit</i></a>');
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


	//$ret .= print_o($actionInfo,'$actionInfo');

	return $ret;
}
?>