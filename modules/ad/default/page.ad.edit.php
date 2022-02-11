<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function ad_edit($self, $adId, $action = NULL) {
	if (!user_access('administer ads')) return message('error','Access denied');

	$ad = ad_model::get_ad_by_id($adId);

	$ret = '';

	if ($action) {
		$editMethod = '__edit_'.$action;
		$result->edit = ad_model::$editMethod($ad,$para);
		$ret .= $result->edit->body;
		//$ret .= print_o($result, '$result');
		return $ret;
	}

	return $ret;
}
?>