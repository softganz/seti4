<?php
ini_set('display_errors',1);
ini_set('display_startup_errors',1);
$smf='/home/rap3g/domains/rap3g.com/public_html/forum/SSI.php';
require_once($smf);
function ibuy_admin_smf($self) {
	global $context;
	$ret.='<div class="login login--sgz"><h3>เข้าสู่ระบบสมาชิก</h3>';

	$ret.='</div>';

	$ret.='<div class="login login--smf"><h3>SMF User</h3>';

	if ($context['user']['is_guest'])
	{
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
	}
	else
	{
		//You can show other stuff here.  Like ssi_welcome().  That will show a welcome message like.
		//Hey, username, you have 552 messages, 0 are new.
		$_SESSION['logout_url']='/nshop/ibuy/admin/smf';
		$ret.='ยินดีต้อนรับ '.$context['user']['username'];
		ob_start();
		ssi_welcome();
		ssi_logout();
		$ret.=ob_get_contents();
		ob_end_clean();
		if ($context['user']['language']=='thai-utf8') $contaxt['user']['name']=sg_tis620_to_utf8($contaxt['user']['name']);
		$ret.='<p><a href="http://www.rap3g.com/forum/index.php?action=logout&sesc=2a40cf6c3cd61ffb35558d4245f4fa85">ออกจากระบบ</a></p>';
	}
	$ret.='</div>';
	$ret.='<br clear="all" />'.print_o($context['user']);
	$ret.='<style>
	.login {width:45%}
	.login--sgz {float:left;}
	.login--smf {float:right;}
	</style>';
	return $ret;
}
?>