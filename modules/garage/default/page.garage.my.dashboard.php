<?php
/**
* Garage My Dashboard
* Created 2019-11-24
* Modify  2019-11-24
*
* @param Object $self
* @return String
*/

$debug = true;

function garage_my_dashboard($self) {
	$host = preg_match('/^([a-z]+)/', _DOMAIN_SHORT, $out) ? $out[1] : _DOMAIN_SHORT;
	$isProduction = $host == 'abcg';

	$ret = '';
	$ret .= '<header class="header"><h3>แผงควบคุม</h3></header>';

	if (!i()->ok) {
		// Show Login Page
		R::View('toolbar',$self,'@Secure Log in','none');
		$ret = R::View('signform', '{time:-1, rel: "box"}');
		$ret .= '<style type="text/css">
		.toolbar.-main h2 {text-align: center;}
		.module-garage.-softganz-app .form-item.-edit-cookielength {display: none;}
		.login.-normal h3 {display: none;}
		</styel>';
		return $ret;
	}

	$isAdmin = user_access('administrator garages');
	$isOfficer = $isAdmin || user_access('access garages report');

	$ui = new Ui(NULL, 'ui-menu');
	$ui->add('<a class="sg-action" href="'.url('garage/my/profile').'" data-webview="My Account"><i class="icon -material">account_circle</i><span>My Account</span></a>');

	$ret .= $ui->build();

	if (i()->ok) {
		$ret .= '<header class="header"><h3>บริการของฉัน</h3></header>';

		$myServUi = new Ui(NULL, 'ui-menu -service -sg-flex -justify-left');

		$myServUi->add('<a class="sg-action" href="'.url('garage/my/message').'" data-webview="ข้อความ"><i class="icon -material">email</i><span>ข้อความ</span></a>');

		if ($isOfficer) {
			$myServUi->add('<a class="sg-action" href="'.url('garage/app').'" data-webview="บริการลูกค้า"><i class="icon -material">person_pin</i><span>บริการลูกค้า</span></a>');
		}

		if (i()->username == 'softganz') $myServUi->add('<a class="sg-action" data-rel="" data-webview="server" data-server="'.($isProduction ? 'DEV' : 'PRODUCTION').'"><i class="icon -material">android</i><span>'.strtoupper($host).'</span></a>');

		$ret .= $myServUi->build();
	}

	$ret .= '<style type="text/css">
	.ui-menu.-service a {text-align: center;}
	.ui-menu.-service>.ui-item>a>.icon {display: block; margin: 0 auto;}
	</style>';

	return $ret;
}
?>