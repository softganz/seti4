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

function green_app_article($self) {
	$isAddArticle = user_access('create story paper');

	$toolbar = new Toolbar($self, 'สาระเกษตรอินทรีย์');

	// Show main navigator
	$ui = new Ui(NULL, 'ui-nav');
	if ($isAddArticle) {
		$ui->add('<a class="sg-action -add" href="'.url('paper/post/story').'" data-webview="สาระเกษตรอินทรีย์"><i class="icon -material">add</i><span>เขียน</span></a>');
	}

	$toolbar->addNav('main', $ui);



	$ret = '';

	$tagId = 386;

	//$stmt = 'SELECT * FROM %tag_topic% tt LEFT JOIN %topic% t USING(`tpid`) WHERE `tid` = :tagid';

	//$stickyTopic = mydb::select($stmt, ':tagid', $tagId);

	$ret .= '<div class="widget content -news-greensmile" id="news-greensmile" data-tag="'.$tagId.'" data-limit="20" data-field="body,photo" show-photo="image" show-style="div" show-webview="true" show-readall="More:tags/'.$tagId.'"></div>';

	return $ret;
}
?>