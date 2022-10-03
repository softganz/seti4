<?php
/**
* View User Sign In Form
* Created 2019-05-06
* Modify  2019-09-05
*
* @param Object $options
* @return String
*/

$debug = false;

function view_signform($options = '{}') {
	$defaults = '{debug: false, id: "signin-'.uniqid().'", class: "signform", time: 10080, showTime: true, showInfo: true, showGuide: true, showRegist: true, rel: null, signret: null, done: "", regRel: "#main"}';
	$options = SG\json_decode($options, $defaults);
	$debug = $options->debug;

	$elementClass = 'member-zone'.($options->class ? ' '.$options->class : '');

	$ret = '';

	if (!mydb()->status) return message('SORY!!! ไม่สามารถให้บริการได้ในขณะนี้ กรุณาแวะมาใหม่อีกครั้ง');

	// If already signin, Show menu
	if (i()->ok) {
		$ret = '<ul '.($options->id?'id="'.$options->id.'"':'').' class="'.$elementClass.'">'._NL;
		$ret .= '<li class="member-zone-name first"><a href="'.url('profile/'.i()->uid).'">'.tr('Welcome').' '.i()->name.'</a></li>'._NL;
		$ret .= '<li class="member-zone-profile"><a href="'.url('profile/'.i()->uid).'">'.tr('Edit My <strong>Profile</strong>').'</a></li>'._NL;
		if ($options->paper) {
			foreach (explode(',',$options->paper) as $fid) {
				list($fid,$text) = explode(':',$fid);
				$text = $text ? $text : tr('Create').' <strong>'.$fid.'</strong> '.tr('content');
				if (user_access('administer papers,create '.$fid.' paper')) {
					$ret .= '<li class="member-zone-paper"><a href="'.url('paper/post/'.$fid).'">'.$text.'</a></li>'._NL;
				}
			}
		}
		if ($options->links && is_array($options->links))
			foreach ($options->links as $link) $ret .= '<li>'.$link.'</li>'._NL;
		if ($options->menu && is_array($options->menu)) {
			foreach ($options->menu as $menu) {
				if (user_access($menu['access'])) $ret .= '<li class="member-zone-link"><a href="'.$menu['url'].'">'.$menu['text'].'</a></li>'._NL;
			}
		}
		if (module_install('blog')) $ret .= '<li class="member-zone-blog"><a href="'.url('dashboard').'">บล็อก (Blog) ของฉัน</a></li>'._NL;
		if (module_install('paper')) $ret .= '<li class="member-zone-mydoc"><a href="'.url('paper/user/'.i()->uid).'">เอกสารของฉัน</a></li>'._NL;
		if (user_access('access administrator pages')) $ret .= '<li class="member-zone-admin"><a href="'.url('admin').'">Website <strong>Admin</strong>istrator</a></li>'._NL;
		$ret .= '<li class="member-zone-signout last"><a href="'.url('signout').'">'.tr('Sign out').'</a></li>'._NL;
		$ret .= '</ul>'._NL;
		return $ret;
	}

	// Show link
	if ($options->signform == 'link') {
		$ret.='<ul class="member-zone"><li><a href="'.url('my').'">'.tr('Sign in').'</a></li><li><a href="'.url('user/register').'">'.tr('Join with us').'!</a></li></ul>';
		return $ret;
	}

	$options->done .= ' | moveto:0,0';
	// Show signin form
	$form = new Form([
		'variable' => 'signin',
		'action' => SG\getFirst($options->action, url(q()), $_SERVER['HTTP_REFERER'], _URL),
		'id' => $options->id ? $options->id : 'signin',
		'class' => $options->class,
		'rel' => $options->rel ? $options->rel : NULL,
		'ret' => $options->ret ? url($options->signret) : NULL,
		'done' => $options->done ? $options->done : NULL,
		'children' => [
			'username' => [
				'name' => 'username',
				'type' => 'text',
				'id'  =>  'username-'.uniqid(),
				'label' => tr('Username').' '.tr('or').' '.tr('e-mail'),
				'class' => '-username -fill',
				'placeholder' => 'Username or E-mail',
				'maxlength' => 50,
				'autocomplete' => 'off',
				'value' => SG\getFirst($options->username,post('user_u')),
				'container' => '{class: "-label-in"}',
			],
			'password' => [
				'name' => 'password',
				'type' => 'password',
				'id'  =>  'password-'.uniqid(),
				'label' => tr('Password'),
				'class' => '-password -fill',
				'value' => SG\getFirst($options->password,post('user_p')),
				'placeholder' => 'Password',
				'maxlength' => cfg('member.password.maxlength'),
				'container' => '{class: "-label-in"}',
			],
			'cookielength' => [
				'name' => 'cookielength',
				'type' => $options->showTime ? 'select' : 'hidden',
				'class' => '-fill',
				'options' => [
					'60' => '1 '.tr('Hour'),
					'1440' => '1 '.tr('Day'),
					'10080' => '1 '.tr('Week'),
					'43200' => '1 '.tr('Month'),
					'-1' => tr('Forever'),
				],
				'value' => SG\getFirst($options->time,-1)
			],
			'submit' => [
				'type' => 'button',
				'class' => '-primary -fill',
				'value' => '<i class="icon -material">login</i><span>'.tr('Sign in').'</span>'
			],
			($googleId = cfg('signin')->google->id) ? new Column([
				'class' => '-sg-text-center',
				'children' => [
					'หรือ',
					'<script src="https://accounts.google.com/gsi/client" async defer></script>
					<div id="g_id_onload"
						data-client_id="'.$googleId.'"
						data-login_uri="'._DOMAIN.url(q(), ['signMethod' => 'google']).'"
						data-auto_prompt="false"
						data-ux_mode="redirect"
					></div>
					<div class="g_id_signin"
						data-type="standard"
						data-size="large"
						data-theme="filled_blue"
						data-text="sign_in_with"
						data-shape="circle"
						data-logo_alignment="left"
					></div>
					</div>',
					isset(R()->message->signInErrorInSignForm) ? new Container([
						'class' => '-error',
						'children' => [
							new Icon('error', ['class' => '-error']),
							R()->message->signInErrorInSignForm
						], // children
					]) : NULL, // Container
				], // children
			]) : NULL, // Column

			new Column([
				'class' => '-sg-text-center',
				'children' => [
					'<hr />',
					'ยังไม่ได้เป็นสมาชิก',
					'<span class="ui-action">'
					. (user_access('register new member') && $options->showRegist ? '<a class="sg-action btn" href="'.url('user/register', ['ret' => $options->signret, 'rel' => $options->regRel]).'" data-rel="'.$options->regRel.'"><i class="icon -material">person_add</i><span>'.tr('Create new account').'</span></a> ' : '')
					. '<div style="height: 16px;"></div>'
					. '<a class="btn -link" href="javascript:void(0)" onclick="window.location=\''.url('user/password').'\';return false;"><i class="icon -material -gray">restore</i><span>'.tr('Request new password').'?</span></a></span>'._NL,
				], // children
			]), // Column
		], // children
	]);

	$ret .= '<div id="login" class="login -normal -sg-clearfix">';

	$ret .= '<div class="-form"><h3>'.tr('I already have an account.').'</h3>';
	$ret .= $form->build();

	// $ret .= '<div class="-sg-text-center">หรือ<br />
	// 	<script src="https://accounts.google.com/gsi/client" async defer></script>
	// 	<div id="g_id_onload"
	// 		data-client_id="530187295990-lb8kuro5entopcdvrqa9g6mlcjrohmm3.apps.googleusercontent.com"
	// 		data-login_uri="'._DOMAIN.url('signin/google/complete').'"
	// 		data-auto_prompt="false">
	// 	</div>
	// 	<div class="g_id_signin"
	// 		data-type="standard"
	// 		data-size="large"
	// 		data-theme="outline"
	// 		data-text="sign_in_with"
	// 		data-shape="rectangular"
	// 		data-logo_alignment="left">
	// 	</div>
	// 	<style>.g_id_signin iframe {margin: 8px auto !Important;}</style>
	// </div>';

	if ($options->showGuide > 0) {
		$ret .= '<div class="-guideline"><h5>คำแนะนำในการเข้าสู่ระบบ</h5><ul><li>ป้อน <b>บัญชีผู้ใช้ (username) หรืออีเมล์ (email)</b> ที่ลงทะเบียนไว้กับเว็บไซท์</li><li>ป้อน <b>รหัสผ่าน (password)</b></li><li>คลิกที่ปุ่ม <strong>เข้าสู่ระบบ (Sign In)</strong></li><li>กรณีลืมรหัสผ่านคลิกที่ <b>ลืมรหัสผ่าน?</b></li><li>กรณีที่ยังไม่มีบัญชีผู้ใช้ กรุณา <strong>สมัครสมาชิก</strong> ก่อน</li></ul></div>';
	}

	$ret .= '</div>';

	// Show not member in -info
	// if ($options->showInfo > 0) {
	// 	$ret .= '<div class="-info"><h3>'.tr('I am a new customer!').'</h3>';
	// 	if (user_access('register new member')) {
	// 		$ret .= '<p>'.tr(cfg('web.msg.createnewusertext')).'</p>'
	// 			. '<a class="sg-action btn -secondary -fill" href="'.url('user/register', array('ret' => $options->signret)).'" data-rel="'.$options->regRel.'"><i class="icon -material -gray">person_add</i><span>'.tr('Create an Account').'</span></a>';
	// 	} else {
	// 		$ret .= '<p><b>เว็บไซท์ไม่ได้เปิดให้ผู้สนใจสมัครสมาชิกด้วยตนเอง</b><br />หากท่านต้องการสมัครเป็นสมาชิก กรุณาติดต่อผู้ดูแลระบบ.</p>';
	// 	}
	// 	$ret .= '</div>';
	// }

	$ret .= '</div><!-- login -->';

	return $ret;
}
?>