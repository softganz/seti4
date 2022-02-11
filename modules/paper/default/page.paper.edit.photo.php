<?php
/**
* Paper Edit Photo
* Created 2019-06-01
* Modify  2019-06-01
*
* @param Object $self
* @param Object $topicInfo
* @return String
*/

$debug = true;

function paper_edit_photo($self, $topicInfo, $fileId = NULL) {
	if (!$topicInfo->tpid) return message('error', 'PARAMETER ERROR');

	$tpid = $topicInfo->tpid;

	if ($fileId) {
		return __paper_edit_photo_render($topicInfo, $topicInfo->photos[$fileId]);
	}

	$ret = '<header class="header -box">'._HEADER_BACK.'<h3>{tr:Photo management}</h3><nav class="nav"><a class="sg-action btn -primary" href="'.url('paper/'.$tpid.'/edit.photo.add').'" data-rel="box" data-width="640" data-height="640"><i class="icon -material">cloud_upload</i><span>{tr:UPLOAD NEW PHOTO}</span></a></nav></header>';


	// show reference file list
	$ret .= '<div class="photos"><p><strong>All photo relate to this topic</strong></p>';
	if ($topicInfo->photos) {
		krsort($topicInfo->photos);
		//$ret .= print_o($topicInfo->photos,'$photo');
		$ret .= '<div id="" class="ui-card album -sg-flex">';
		foreach ($topicInfo->photos as $key=>$photo) {
			$ret .= __paper_edit_photo_render($topicInfo, $photo);
		}

		$ret .= '</div><!-- photo-items -->';
	}
	$ret.='</div><!-- photos -->';

	$ret .= '<style type="text/css">
	.ui-card.album>.ui-item {width: 200px; height: 140px; overflow: hidden;}
	.photos .item>tbody>tr>td:first-child {white-space: nowrap}
	.photos .item>tbody>tr>td:nth-child(2) {width: 100%;}
	</style>';

	$ret .= '<script type="text/javascript">
	$("#edit-photo").change(function() {
		var f = $(this).val().replace(/.*[\/\\\\]/, "")
		$("#edit-photo-filename").text("เลือกไฟล์ : "+f)
	})
	</script>';

	//$ret .= print_o($topicInfo->photos, '$photos');
	return $ret;
}

function __paper_edit_photo_render($topicInfo, $photo) {
	$tpid = $topicInfo->tpid;
	$ret = '<li id="photo-id-'.$photo->fid.'" class="ui-item photo-items">';
	$ret .= '<a class="sg-action" href="'.url('paper/'.$tpid.'/photo/'.$photo->fid).'" data-rel="box" data-width="640" data-height="80%"><img src="'.$photo->_src.'" height="140" /></a>';


	$ret .= '</li><!-- photo-items -->';
	return $ret;
}
?>