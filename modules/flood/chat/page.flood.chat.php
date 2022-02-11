<?php
/**
* Flood Chat
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;
function flood_chat($self) {
	$self->theme->title = 'แจ้งสถานการณ์';


	$ret .= R::Page('flood.chat.home',$self);
	return $ret;
}
?>