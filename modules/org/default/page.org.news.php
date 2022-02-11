<?php
/**
* Module Method
* Created 2019-08-01
* Modify  2019-08-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function org_news($self, $orgInfo) {
	$orgId = $orgInfo->orgid;

	$isAdmin = user_access('administrator orgs');
	$isEdit = $isAdmin || $orgInfo->is->membership;

	$ret = '';

	R::View('org.toolbar', $self, 'News', 'none', $orgInfo);


	if ($isEdit) {
		$ret.='<div class="btn-floating -right-bottom"><a class="?-sg-action btn -floating -circle48" href="'.url('paper/post/story',array('org' => $orgId)).'" data-rel="#main"><i class="icon -addbig -white"></i></a></div>';
	}

	$stmt = 'SELECT * FROM %topic%
		WHERE `type` = "story" AND `orgid` = :orgId
		ORDER BY `tpid` DESC';

	$dbs = mydb::select($stmt, ':orgId', $orgId);

	$ui = new Ui();
	foreach ($dbs->items as $rs) {
		$ui->add('<a href="'.url('paper/'.$rs->tpid).'">'.$rs->title.'</a>');
	}

	$ret .= $ui->build();

	//$ret .= print_o($dbs,'$dbs');

	//$ret .= print_o($orgInfo, '$orgInfo');
	return $ret;
}
?>