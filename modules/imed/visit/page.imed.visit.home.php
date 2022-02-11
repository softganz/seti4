<?php
/**
* iMed :: Patient Visit Home Page
* Created 2019-03-05
* Modify  2021-05-28
*
* @param Integer $psnId
* @return String
*
* @usage imed/visit
*/

$debug = true;

class ImedVisitHome {
	function build() {
		$ret .= R::View('imed.toolbox',$self,'iMed@Visit', 'visit');

		$ret .= '<div id="imed-app" class="imed-app">'._NL;

		$ret .= '</div><!-- imed-app -->';
		return $ret;
	}
}
?>