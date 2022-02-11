<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_social_info($self, $orgId = NULL) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('imed.social.get', $orgId, '{}');
	$orgId = $orgInfo->orgid;

	if (!$orgId) return message('error','ไม่มีข้อมูลของกลุ่มที่ระบุ');

	//$ret .= '<h3>@Social Care Group</h3>';

	$ret .= R::View('imed.toolbox',$self,'iMed@Social', 'social');


	$isAdmin = $orgInfo->RIGHT & _IS_ADMIN;
	$isMember = $orgInfo->is->socialtype;
	//$ret .= R::Page('imed.social.group',$self);


	$ret .= '<div class="imed-sidebar"><h3>'.$orgInfo->name.'</h3>'.R::View('imed.menu.group',$orgInfo)->build().'</div>';

	$ret .= '<div id="imed-app" class="imed-app">'._NL;

	if ($isAdmin || $isMember) {
		$ret .= R::Page('imed.social.patient',NULL,$orgInfo);
		//$visitText .= R::Page('imed.rehab.visit', NULL, $orgInfo);
		//$ret .= $visitText ? $visitText : R::Page('imed.social.about', NULL, $orgInfo);
		//$ret .= '['.$visitText.']';
	} else {
		$ret .= R::Page('imed.social.about', NULL, $orgInfo);
	}

	//$ret .= print_o($orgInfo,'$orgInfo');

	$ret .= '</div><!-- imed-app -->';


	$ret .= '<style type="text/css">
	@media (min-width:40em) {
	.ui-card.-flex {display:flex; justify-content: space-between; flex-wrap: wrap;}
	.ui-card.-flex .ui-item {width: 45%;}
	}
	</style>';
	return $ret;
}
?>