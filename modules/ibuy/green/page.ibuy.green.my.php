<?php
/**
* My GoGreen
*
* @param Object $self
* @param Int $shopId
* @return String
*/

$debug = true;

function ibuy_green_my($self) {
	$host = preg_match('/^([a-z]+)/', _DOMAIN_SHORT, $out) ? $out[1] : _DOMAIN_SHORT;
	$isProduction = $host == 'communeinfo';

	if (!i()->ok) {
		// Show Login Page
		R::View('toolbar',$self,'@Secure Log in','none');
		$ret = R::View('signform', '{time:-1}');
		$ret .= '<style type="text/css">
		.toolbar.-main h2 {text-align: center;}
		.form.signform .form-item {margin-bottom: 16px; position: relative;}
		.form.signform label {position: absolute; left: 8px; color: #666; font-style: italic; font-size: 0.9em; font-weight: normal;}
		.form.signform .form-text, .form.signform .form-password {padding-top: 24px;}
		.module-ibuy.-softganz-app .form-item.-edit-cookielength {display: none;}
		.login.-normal h3 {display: none;}
		</styel>';
		return $ret;
	}

	R::View('toolbar',$self,'@'.i()->name,'none');

	$isAdmin = user_access('administer ibuys');
	$isOfficer = $isAdmin || user_access('access ibuys report');
	$isAccessCustomer = $isOfficer || user_access('access ibuys customer');

	// Show main navigator
	$ui = new Ui();
	$ui->add('<a href="'.url('ibuy/green/my').'"><i class="icon -home"></i><span>หน้าหลัก</span></a>');
	$ui->add('<a class="sg-action" href="'.url('ibuy/green/my/history').'" data-webview="ประวัติการซื้อสินค้า"><i class="icon -goods"></i><span>ประวัติ</span></a>');
	$ui->add('<a class="sg-action" href="'.url('ibuy/green/my/follow').'" data-webview="ติดตามกลุ่ม/ร้านค้า"><i class="icon -star"></i><span>ติดตาม</span></a>');
	$ret .= '<nav class="nav -page -app-icon">'.$ui->build().'</nav>'._NL;



	$ret .= '<div class="imed-patient-photo-wrapper" style="margin:0 0 16px; padding: 16px 0; position:relative; background-color: #fff">';
	$ret .= '<div id="imed-patient-photo" style="width: 196px; height: 196px; margin: 0px auto 32px; display: block; border-radius: 50%; overflow: hidden; border: 2px #eee solid;"><img src="'.model::user_photo(i()->username).'" width="100%" height="100%" /></a></div>';

	$ret .= '<form class="sg-form -upload" method="post" enctype="multipart/form-data" action="'.url('my/api/photo.change').'" data-rel="notify" data-done="load" style="position: absolute; top: 16px; right: calc(50% - 112px); padding: 0; margin: 0; background-color: transparent;"><span class="fileinput-button"><i class="icon -material">add_a_photo</i><input type="file" name="photo" class="inline-upload" accept="image/*;capture=camcorder" x-capture="capture" onchange=\'$(this).closest(form).submit(); return false;\' /></span></form>';

	$ret .= '</div>';

	if (i()->ok) {
		$ret .= '<header class="header"><h3>บริการของฉัน</h3></header>';

		$myServUi = new Ui(NULL, 'ui-menu -service -sg-flex -justify-left');

		//$myServUi->add('<a class="sg-action" href="'.url('ibuy/my/message').'" data-webview="ข้อความ"><i class="icon -material">email</i><span>ข้อความ</span></a>');

		if ($isAccessCustomer) {
			//$myServUi->add('<a class="sg-action" href="'.url('ibuy/customer').'" data-webview="บริการลูกค้า"><i class="icon -material">person_pin</i><span>บริการลูกค้า</span></a>');
		}

		if (i()->username == 'softganz') $myServUi->add('<a class="sg-action" data-rel="" data-webview="server" data-server="'.($isProduction ? 'DEV' : 'PRODUCTION').'"><i class="icon -material">android</i><span>'.strtoupper($host).'</span></a>');

		$ret .= $myServUi->build();
	}

	return $ret;
}
?>