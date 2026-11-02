<?php
/**
 * Module Method
 *
 * @param Object $self
 * @param Int $var
 * @return String
 */

$debug = true;

function user_signform($self) {
	$self->theme->title = 'Sign in';
	$self->theme->header->description = R::view('user.menu');
	$ret .= R::view('signform', '{action:"user"}');
	return $ret;
}
?>
