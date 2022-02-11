<?php
/**
* Module Method
* Created 2019-11-01
* Modify  2019-11-01
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function ibuy_my_cart($self) {
	$ret = '';
	$ret .= '<header class="header"><h3>รถเข็น</h3></header>';

	if (!i()->ok) {
		// Show Login Page
		R::View('toolbar',$self,'@Secure Log in','none');
		$ret = R::View('signform', '{time:-1, rel: "box", signret: "ibuy/green/my/shop"}');
		$ret .= '<style type="text/css">
		.toolbar.-main h2 {text-align: center;}
		.module-ibuy.-softganz-app .form-item.-edit-cookielength {display: none;}
		.login.-normal h3 {display: none;}
		</styel>';
		return $ret;
	}

	$ret .= R::Page('ibuy.cart', $self);
	return $ret;
}
?>