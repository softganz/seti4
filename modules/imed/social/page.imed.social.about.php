<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_social_about($self, $orgId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('imed.social.get', $orgId, '{}');
	$orgId = $orgInfo->orgid;

	if (!$orgId) return message('error','ไม่มีข้อมูลของกลุ่มที่ระบุ');

	$ret .= '<h3>@About Group</h3>';

	$ret .= '<section id="imed-social-about" class="">';

	$ret .= '<h3>'.$orgInfo->name.'</h3>';
	$ret .= '<p>ที่อยู่<br /><address>'.$orgInfo->info->address.'</address></p>';
	$ret .= '<p>โทรศัพท์ '.$orgInfo->info->phone.'</p>';
	$ret .= '<p>แฟกซ์ '.$orgInfo->info->fax.'</p>';

	//$ret .= print_o($orgInfo,'$orgInfo');

	$ret .= '</section>';

	return $ret;
}
?>