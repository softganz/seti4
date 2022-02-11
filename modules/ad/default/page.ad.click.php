<?php
/**
* Module Method
*
* @param Object $self
* @param Int $adId
* @return String
*/

$debug = true;

function ad_click($self, $adId = NULL) {
	$adInfo = ad_model::get_ad_by_id($adId);
	if ($adInfo->_empty) return message('error','Data not found');

	$stmt = 'UPDATE %ad% SET clicks=clicks+1 WHERE aid = :aid LIMIT 1';
	mydb::query($stmt, ':aid', $adInfo->aid);

	R::Model('watchdog.log','ad','click',$adInfo->title,i()->uid,$adInfo->aid);

	if ($adInfo->url) {
		location($adInfo->url);
		die;
	}

	$self->theme->title = $adInfo->title;

	if ($adInfo->file)
		$ret .= '<div class="photo">'.ad_model::__show_img_str($adInfo).'</div>';

	$ret .= '<div class="body">'.sg_text2html($adInfo->body).'</div>';
	return $ret;
}
?>