<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
$smf='/home/rap3g/domains/rap3g.com/public_html/forum/SSI.php';
if (file_exists($smf)) require_once($smf);

function ibuy_member_login($self) {
	global $context;
	$ret.='<div class="login login--sgz"><h3>เข้าสู่ระบบสมาชิก</h3>';
	if (i()->ok) {
		$ret.='<p>ยินดีต้อนรับ <strong>'.i()->name.'</strong></p>';
		$ui=new ui();
		$ui->add('<a href="'.url('ibuy/cart').'">สินค้าในตะกร้า</a>');
		$ui->add('<a href="'.url('ibuy/status').'">สถานะการสั่งสินค้า</a>');
		$ui->add('<a href="'.url('ibuy/franchise/'.i()->username).'">ข้อมูลสำหรับจัดส่งสินค้า</a>');
		$ui->add('<a href="'.url('profile/password').'">เปลี่ยนรหัสผ่าน</a>');
		$ui->add('<a href="'.url('profile/photo').'">เปลี่ยนภาพถ่าย</a>');
		$ret.=$ui->build('ul');

		$ret.='<p>หากคุณไม่ใช่ <strong>'.i()->name.'</strong> กรุณา <a href="'.url('signout').'">ออกจากระบบ</a> และ เข้าสู่ระบบสมาชิกในชื่อของคุณอีกครั้ง</p>';
	} else {
		$ret.='<div class="widget signform" data-option-replace="no" data-paper="ibuy:เพิ่มสินค้าใหม่,story:ส่งข่าว-บทความ"></div>';
		$ret.='<p>หากคุณยังไม่ได้สมัครสมาชิก การลงทะเบียนใหม่จะเป็นการสร้างบัญชีแล้วจะทำให้ซื้อของได้สะดวก ง่าย รวดเร็ว ในการจัดเก็บ ปรับปรุงรายการสินค้าที่ได้ทำไว้</p>';
		$ret.='<p><a class="button" href="'.url('user/register').'">ลงทะเบียนใหม่</a></p>';
	}
	$ret.='</div>';

	$ret.='<div class="login login--smf"><h3>สำหรับสมาชิกเว็บบอร์ด</h3>';

	//$context['user']['is_guest']=false;
	if ($context['user']) {
		if ($context['user']['is_guest']) {
			$_SESSION['login_url']='http://www.rap3g.com/nshop/ibuy/admin/smf';
			/*
			ob_start();
			ssi_login();
			$ret.=ob_get_contents();
			ob_end_clean();
			*/
			$ret.='<form action="/forum/index.php?action=login2" method="post">
				<table cellspacing="1" cellpadding="0" border="0" class="ssi_table">
					<tbody><tr>
						<td align="right"><label for="user">ชื่อผู้ใช้งาน:</label>&nbsp;</td>
						<td><input type="text" value="" size="8" name="user" id="user"></td>
					</tr><tr>
						<td align="right"><label for="passwrd">รหัสผ่าน:</label>&nbsp;</td>
						<td><input type="password" size="8" id="passwrd" name="passwrd"></td>
					</tr><tr>
						<td><input type="hidden" value="-1" name="cookielength"></td>
						<td><input class="button" type="submit" value="เข้าสู่ระบบ"></td>
					</tr>
				</tbody></table>
	</form>';
		} else {
			//You can show other stuff here.  Like ssi_welcome().  That will show a welcome message like.
			//Hey, username, you have 552 messages, 0 are new.
			$_SESSION['logout_url']='/nshop/ibuy/admin/smf';
			$ret.='<p>ยินดีต้อนรับ <strong>'.$context['user']['username'].'</strong></p>';
			if ($context['user']['avatar']['href']) $ret.='<p><img src="'.$context['user']['avatar']['href'].'" /></p>';

			// หากยังไม่ได้สมัครเป็นสมาชิกเว็บ
			$smfUserName=$context['user']['username'];
			//$smfUserName='pick';
			$sgzUserInfo=mydb::select('SELECT `uid`,`username`,`password`,`name` FROM %users% WHERE `username`=:username LIMIT 1',':username',$smfUserName);
			// สมาชิกเก่าที่ยังไม่ได้กำหนดรหัสผ่าน
			if ($sgzUserInfo->uid && $_POST['profile']) {
				$ret.=__ibuy_smf_new_password($sgzUserInfo->uid);
			} else if ($sgzUserInfo->username && !$sgzUserInfo->password) {
				$ret.='<p>ท่านเป็นสมาชิกของเว็บแล้ว แต่หลังจากการปรับปรุงระบบซื้อขายใหม่แล้ว รหัสผ่านเดิมจะไม่สามารถใช้งานกับระบบซื้อขายใหม่ได้ ท่านสามารถกำหนดรหัสผ่านอีกครั้งเพื่อเข้าถึงข้อมูลเดิมที่ท่านเคยลงทะเบียนไว้แล้ว</p>';
				$ret.='<p>กรุณากำหนดรหัสผ่านใหม่</p>';
				$ret.=__ibuy_smf_new_password($sgzUserInfo->uid);
			} else if ($sgzUserInfo->username && $sgzUserInfo->password) {
				$ret.='<p>ท่านเป็นสมาชิกของเว็บบอร์ดและได้บันทึกข้อมูลสมาชิกในระบบซื้อขายเรียบร้อยแล้ว</p>';
			}
			//$ret.=print_o($sgzUserInfo,'$sgzUserInfo');
			/*
			ob_start();
			ssi_welcome();
			ssi_logout();
			$ret.=ob_get_contents();
			ob_end_clean();
			*/
			//$ret.='<p><a href="http://www.rap3g.com/forum/index.php?action=logout">ออกจากระบบ</a></p>';
		}
	}
	$ret.='</div>';
	//$ret.='<br clear="all" />'.print_o($context['user']);
	return $ret;
}

	function __ibuy_smf_new_password($uid) {
		$ret='<h3>กำหนดรหัสผ่านใหม่</h3>';

		$password=(object)post('profile',_TRIM);

		if ($_POST['save']) {
			if (strlen($password->password)<6) $error[]='รหัสผ่านใหม่ต้องมีตัวอักษรอย่างน้อย 6 อักษร'; //-- password length
			if (strlen($password->password)!=strlen($password->repassword) || $password->password!=$password->repassword) $error[]='การป้อนรหัสผ่านใหม่ทั้งสองครั้งไม่ตรงกัน'; //-- password <> retype
			if (!$error) {
				$newpassword=sg_encrypt($password->password,cfg('encrypt_key'));
				mydb::query('UPDATE %users% SET password=:password WHERE `uid`=:uid LIMIT 1',':uid',$uid,':password',$newpassword);
				$ret .= message('status','New password was change : บันทึกรหัสผ่านใหม่เรียบร้อย กรุณาเข้าสู่ระบบสมาชิกด้วย username และ password ใหม่');
				return $ret;
			}
		}

		$form = new Form([
			'variable' => 'profile',
			'action' => url(q()),
			'id' => 'edit-password',
			'children' => [
				'password' => [
					'type' => 'password',
					'label' => 'รหัสผ่านใหม่',
					'maxlength' => 20,
					'require' => true,
					'value' => $password->password,
					'description' => 'ป้อนรหัสผ่านใหม่ที่ต้องการเปลี่ยน',
				],
				'repassword' => [
					'type' => 'password',
					'label' => 'รหัสผ่านใหม่ (ยืนยัน)',
					'maxlength' => 20,
					'require' => true,
					'value' => $password->repassword,
					'description' => 'ป้อนรหัสผ่านใหม่อีกครั้งเพื่อยืนยันความถูกต้อง',
				],
				'save' => [
					'type' => 'submit',
					'value' => tr('SAVE'),
				],
			],
		]);

		$ret .= $form->build();

		if ($error) $ret.=message('error',$error);

		return $ret;
	}
?>