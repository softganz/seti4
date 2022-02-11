<?php
/**
* Flood Application Notify
*
* @param Object $self
* @return String
*/

$debug = true;

function flood_app_notify($self) {
	$self->theme->title = 'Notification<br />@'.date('Y-m-d H:i:s');
	$ret = '';

	$ret .= '<img class="" src="//hatyaicityclimate.org/upload/hatyai-flood-flag-green.jpg" style="width: 180px; display: block; margin: 0 auto 32px;" />';
	$ret .= '<div class="flood-notify -green">สถานการณ์น้ำในพื้นที่เทศบาลนครหาดใหญ่อยู่ในสภาวะปกติ</div>';

	$ret .= '<img src="//hatyaicityclimate.org/upload/hatyai-flood-flag.jpg" style="width: 100%; margin: 32px 8px;" />';

	$ret .= '<style type="text/css">
	.flood-notify.-green {margin: 32px 8px; padding: 32px; background-color: green; color: #fff; font-size: 2em; text-align: center; line-height: 2em;}
	</style>';
	return $ret;
}
?>