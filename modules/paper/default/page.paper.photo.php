<?php
/**
* Module Method
* Created 2019-01-01
* Modify  2019-01-01
*
* @param Object $self
* @param Object $topicInfo
* @return String
*/

$debug = true;

function paper_photo($self, $topicInfo, $photoId = NULL) {
	if (!$topicInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $topicInfo->tpid;

	$isEdit = $topicInfo->RIGHT & _IS_EDITABLE;

	$photo = $topicInfo->photos[$photoId];

	$ui = new Ui();

	if ($isEdit) {
		if (user_access('upload photo')) {
			$ui->add('<form class="sg-upload" method="post" enctype="multipart/form-data" action="'.url('paper/info/api/'.$tpid.'/photo.change/'.$photo->fid).'" data-rel="refresh" data-done="close"><span class="btn -link fileinput-button"><i class="icon -material">photo_camera</i><span>{tr:Change photo}</span><input type="file" name="photo" multiple="true" class="inline-upload" accept="image/*;capture=camcorder" /></span><input class="-hidden" type="submit" value="upload" /></form>');
		}
		$ui->add('<a class="sg-action" href="'.url('paper/'.$tpid.'/edit.photo.info/'.$photo->fid).'" data-rel="box" data-width="640" data-height="80%"><i class="icon -material">edit</i><span class="-hidden">{tr:Edit detail}</span></a>');

		$ui->add('<a class="sg-action" href="'.url('paper/info/api/'.$tpid.'/photo.delete/'.$photo->fid).'" data-title="Delete photo!!!!!" data-confirm="Delete photo -> '.htmlspecialchars($photo->file).' <-- !!! Are you sure?" data-rel="notify" data-done="close" data-removeparent="#photo-id-'.$photo->fid.'"><i class="icon -material">delete</i><span class="-hidden">'.tr('Remove').'</span></a>');
	}

	$ret = '<header class="header -box">'._HEADER_BACK.'<h3>PHOTO</h3><nav class="nav">'.$ui->build().'</nav></header>';

	$ret .= '<div id="photo-id-'.$photo->fid.'" class="photo-items">';
	if ($photo->title) $ret .= '<h3>'.$photo->title.'</h3>';
	$ret .= '<a href="'.$photo->_src.'" target=_blank><img class="photo" src="'.$photo->_src.'" width="100%" /></a>';

	if ($isEdit) {
		$ret .= '<h4>'.$photo->file.' <small>size '.$photo->_size->width.'x'.$photo->_size->height.' pixel in '.number_format($photo->_filesize).' bytes</small></h4>';

		$tables = new Table();
		$tables->rows[] = array(
				'url short address :',
				'<input class="form-text -fill" type="text" value="'.$photo->_src.'" onfocus="if (typeof(document.layers)==\'undefined\') this.select()" />'
			);
		$tables->rows[] = array(
				'url full address :',
				'<input class="form-text -fill" type="text" value="'.cfg('domain').$photo->_src.'" onfocus="if (typeof(document.layers)==\'undefined\') this.select()" />'
			);
		$tables->rows[] = array(
				'HTML tag :',
				'<input class="form-text -fill" type="text" value="'.htmlspecialchars('<img src="'.$photo->_src.'" alt="'.$photo->title.'" />').'" onfocus="if (typeof(document.layers)==\'undefined\') this.select()" />'
			);
		$tables->rows[] = array(
				'bb code :',
				'<input class="form-text -fill" type="text" value="'.htmlspecialchars('[img]"'.$photo->_src.'"[/img]').'" onfocus="if (typeof(document.layers)==\'undefined\') this.select()" />'
			);

		$ret .= $tables->build();
	}
	$ret .= '</div><!-- photo-items -->';

	$ret .= '<style type="text/css">
	.photo-items .photo {display: block; margin: 0 auto 16px;}
	.photo-items .item>tbody>tr>td:first-child {white-space: nowrap; text-align: right;}
	.photo-items .item>tbody>tr>td:nth-child(2) {width: 100%;}
	</style>';
	return $ret;
}
?>