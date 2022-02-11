<?php
function saveup_bank_searchmember($self) {
	R::View('saveup.toolbar',$self,'ธนาคารขยะ','bank');
	$ret.=R::Page('saveup.bank.member');
	return $ret;
}
?>