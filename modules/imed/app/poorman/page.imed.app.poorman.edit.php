<?php
function imed_app_poorman_edit($self,$qtref=NULL,$action=NULL) {
	//R::View('imed.toolbar',$self,'@'.i()->name);

	$ret.=R::Page('imed.poorman.edit',NULL,$qtref,$action);
	return $ret;
}
?>