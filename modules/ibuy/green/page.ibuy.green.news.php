<?php
/**
* Module Method
* Created 2019-11-01
* Modify  2019-11-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function ibuy_green_news($self) {
	$ret = '';

	$tagId = 369;

	$stmt = 'SELECT * FROM %tag_topic% tt LEFT JOIN %topic% t USING(`tpid`) WHERE `tid` = :tagid AND `sticky` = 255 LIMIT 1';

	$stickyTopic = mydb::select($stmt, ':tagid', $tagId);

	if ($stickyTopic->_num_rows) {
		$ret .= '<div class="widget content -news-greensmile" id="news-greensmile" data-sticky="255" data-limit="2" data-field="body,photo" show-photo="image" show-style="div" show-webview="true" show-readall="More:tags/'.$tagId.'"></div>';

		//$ret .= print_o($stickyTopic, '$stickyTopic');
	} else {
		$ret .= '<div class="sg-slider banner-top"><a href="{url:tags/'.$tagId.'}" style="margin: 0; display: block;"><img src="https://communeinfo.com/upload/pics/green-banner-1.jpg" width="100%" /></a></div>';
		//$ret .= '<div class="sg-slider banner-top" style="height:300px;"><ul><li><a href="{url:tags/'.$tagId.'}"><img src="https://communeinfo.com/upload/pics/green-banner-1.jpg" width="100%" height="100%" /></a></li><li><a href="{url:tags/'.$tagId.'}"><img src="https://communeinfo.com/upload/pics/green-banner-2.jpg" width="100%" height="100%" /></a></li></ul></div>';
	}

	return $ret;
}
?>