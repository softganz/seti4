<?php
/**
* Draw comment list
*
* @param Integer $tpid
* @param Object $para
* @param Boolean $archive
* @return String $ret
*/

$debug = true;

function view_paper_comment_render($commentInfo = NULL) {
	if ($commentInfo->photos) {
		foreach (explode(',',$commentInfo->photos) as $photo) {
			$photo=model::get_photo_property($photo);
			$photo->description='Photo : '.$commentInfo->photo.' , '.$photo->_size->width.'x'.$photo->_size->height.' pixel '.number_format($photo->_filesize).' bytes';

			$ret .= '<div class="photo photo-'.($photo->_size->width>$photo->_size->height?'wide':'tall').'">'._NL;
			if ($photo->_size->width>cfg('comment.photo.width')) $ret .= '<a class="sg-action" href="'.$photo->_src.'" data-rel="img" title="'.$photo->description.'">';
			$ret .= '<img class="photo photo-'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" '.($photo->_size->width<cfg('comment.photo.width')?'style="width:'.$photo->_size->width.'px;"':'').' title="'.$photo->description.'" alt="'.$photo->description.'" />';
			if ($photo->_size->width>cfg('comment.photo.width')) $ret .= '</a>';
			$ret .= '</div><!--photo-->'._NL;
		}
	}
	$ret .= '<div class="message-body '.sg_status_text($commentInfo->status).'">'._NL;
	$ret .= sg_text2html($commentInfo->comment)._NL;
	if ($commentInfo->docs) {
		$ret.='<h3>ไฟล์ประกอบ</h3><ul>';
		foreach (explode(',',$commentInfo->docs) as $doc) {
			$ret.='<li><a href="'.cfg('paper.upload.document.url').$doc.'">'.$doc.' - '.tr('Download').'</a></li>';
		}
		$ret.='</ul>';
	}
	$ret .= '</div><!--body-->'._NL;
	return $ret;
}
?>