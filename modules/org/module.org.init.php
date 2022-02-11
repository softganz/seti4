<?php
function module_org_init($self) {
	//$self->theme->title=_ORG_TITLE;
	R::View('org.toolbar',$self,_ORG_TITLE);
}
?>