<?php
/**
 * Visit patient home
 *
 * @return String
 */
function imed_at_visit($self) {
	$ret .= print_o(post(),'post()');
	$ret .= '<textarea id="myVisitBox" class="writeBox" data-service="Home Visit" placeholder="@31/12/2560 ข้อความบันทึกการไปเยี่ยมบ้าน"></textarea>';
	$ret .= '<div class="toolbar">';
	$ret .= R::View('imed.patientmenu');
	$ret .= '<ul class="post">';
	$ret .= '<li><button class="btn -primary"><i class="icon -save -white"></i><br /><span>โพสท์</span></button></li>';
	$ret .= '</ul>';
	$ret .= '</div>';
	return $ret;
}
?>