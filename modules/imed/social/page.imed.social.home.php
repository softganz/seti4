<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function imed_social_home($self, $orgId = NULL) {
	$ret .= R::View('imed.toolbox',$self,'iMed@Social', 'social');

	$ret .= '<div id="imed-app" class="imed-app -fill">'._NL;

	$ret .= R::Page('imed.social.group',$self);

	$ret .= '</div><!-- imed-app -->';
	return $ret;
}
?>