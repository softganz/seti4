<?php
/**
 * Home page
 *
 * @return String
 */
function flood_app($self) {
	$ret .= R::View('flood.app.head');
	$ret .= '<div id="flood-event">'._NL;
	$ret .= R::Page('flood.app.basin',$self)._NL;
	$ret .= '</div><!-- flood-event -->'._NL;
	$ret .= R::View('flood.app.foot');
	echo $ret;
	die;
	return $ret;
}
?>