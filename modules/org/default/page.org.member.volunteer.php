<?php
function org_member_volunteer($self,$action,$psnid) {
	if ($action=='add') {
		bigdata::addField('volunteer',1,'int','org',$psnid);
	} elseif ($action=='remove') {
		bigdata::removeField('volunteer','org',$psnid);
	}
	location('org/member/info/'.$psnid);
}
?>