<?php
/**
 * Widget   :: Sign Form Widget
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2022-07-20
 * Modified :: 2026-07-29
 * Version  :: 8
 *
 * @param Array $args
 *
 * @usage new SignForm([])
 */

class SignForm extends Widget {
	var $version = '1.00';
	var $action;
	var $username;
	var $password;
	var $time = 10080;
	var $done;
	var $showRegisterButton = true;
	var $showForgotButton = true;
	var $showGuide = true;
	var $registerRel = '#main';
	var $registerReturnUrl;
	var $registerText = 'Create new account';
	var $guideText = '<div class="-guideline"><h5>คำแนะนำในการเข้าสู่ระบบ</h5><ul><li>ป้อน <b>บัญชีผู้ใช้ (username) หรืออีเมล์ (email)</b> ที่ลงทะเบียนไว้กับเว็บไซท์</li><li>ป้อน <b>รหัสผ่าน (password)</b></li><li>คลิกที่ปุ่ม <strong>เข้าสู่ระบบ (Sign In)</strong></li><li>กรณีลืมรหัสผ่านคลิกที่ <b>ลืมรหัสผ่าน?</b></li><li>กรณีที่ยังไม่มีบัญชีผู้ใช้ กรุณา <strong>สมัครสมาชิก</strong> ก่อน</li></ul></div>';

	function __construct($args = []) {
		parent::__construct(
			array_replace_recursive(
				[
					'class' => '-normal',
					'formAction' => url(q()), // default current url
					'done' => 'reload',
					'username' => NULL,
					'password' => NULL,
					'time' => $this->time,
					'showForgotButton' => $this->showForgotButton,
					'showRegisterButton' => $this->showRegisterButton,
					'showGuide' => $this->showGuide,
					'registerText' => $this->registerText,
					'guideText' => $this->guideText,
					'preText' => NULL,
					'postText' => NULL,
				],
				$args
			)
		);
		unset($this->childContainer, $this->attributeText, $this->config, $this->childTagName);
		unset($this->header, $this->itemClass, $this->mainAxisAlignment, $this->crossAxisAlignment);
		unset($this->href, $this->dataUrl, $this->webview);
		unset($this->children, $this->child);
	}

	#[\Override]
	function build() {
		$id = uniqid();

		return new Widget([
			'tagName' => 'div',
			'class' => 'widget-signform '.($this->class ? ' '.$this->class : ''),
			'children' => [
				// $this->preText ? '<div class="-pre-text">'.$this->renderChildren([$this->preText]).'</div>' : NULL,
				'<header><h3><i class="icon -material">login</i><span> กรณีเป็นสมาชิกเว็บอยู่แล้ว</span></h3></header>',
				$this->preText ? new Widget(['children' => SG\getFirst($this->preText->children, [$this->preText])]) : NULL,
				new Form([
					'action' => $this->formAction,
					'id' => $this->id ? $this->id : 'signin-'.$id,
					'class' => 'sg-form -form',
					'checkValid' => true,
					'rel' => 'none',
					'done' => $this->done,
					'children' => [
						'username' => [
							'type' => 'text',
							'id' => 'username-'.$id,
							'label' => tr('Username').' '.tr('or').' '.tr('e-mail'),
							'class' => '-username -fill',
							'require' => true,
							'placeholder' => 'Username',
							'maxlength' => 50,
							'autocomplete' => 'off',
							'value' => $this->username,
							'container' => '{class: "-label-in"}',
						],
						'password' => [
							'type' => 'password',
							'id' => 'password-'.$id,
							'label' => tr('Password'),
							'class' => '-password -fill',
							'require' => true,
							'placeholder' => 'Password',
							'maxlength' => 20,
							'value' => $this->password,
							'posttext' => '<i class="icon -material -show-password" onClick=\'showPassword(this)\'>visibility_off</i>',
							'container' => ['class' => '-label-in -group'],
						],
						'cookielength' => in_array($this->time, [-1, 'forever']) ? [
							'type' => 'hidden',
							'value' => '-1',
						] : [
							'type' => 'select',
							'id' => 'time-'.$id,
							'class' => '-time -fill',
							'value' => $this->time,
							'options' => [
								'60' => '1 '.tr('Hour'),
								'1440' => '1 '.tr('Day'),
								'10080' => '1 '.tr('Week'),
								'43200' => '1 '.tr('Month'),
								'-1' => tr('Forever')
							],
						],
						'signin' => [
							'type' => 'button',
							'name' => 'signin',
							'class' => '-primary -fill',
							'value' => '<i class="icon -material">login</i><span>'.tr('Sign in').'</span>',
						],
						$this->postText ? $this->postText : NULL,
					], // children
				]), // Form
				'<div class="-more">',
				$this->showForgotButton ? new Button([
					'type' => 'link',
					'class' => '-fill',
					'href' => 'javascript:void(0)',
					'onClick' => 'window.location="'.Url::link('user/password').'";return false;',
					'icon' => new Icon('restore'),
					'text' => tr('Request new password').'?'
				 ]) : NULL,
				'</div>',
				new Column([
					'class' => '-not-member -sg-text-center',
					'children' => [
						'<h3>ยังไม่ได้เป็นสมาชิก</h3>',
						$this->showRegisterButton && user_access('register new member') ? new Button([
							'type' => 'link',
							'class' => 'sg-action -fill',
							'href' => Url::link('user/register'),
							'icon' => new Icon('person_add'),
							'text' => tr('Create new account'),
							'rel' => $this->registerRel,
						]) : NULL,
						// '<span class="ui-action">'
						// . (user_access('register new member') && $this->showRegisterButton ? '<a class="sg-action btn -fill" href="'.url('user/register', ['ret' => $this->registerReturnUrl, 'rel' => $this->registerRel]).'" data-rel="'.$this->regRel.'"><i class="icon -material">person_add</i><span>'.tr('Create new account').'</span></a> ' : '')
						// '<div style="height: 16px;"></div>',
						// '<a class="btn -link -fill" href="javascript:void(0)" onclick="window.location=\''.url('user/password').'\';return false;"><i class="icon -material -gray">restore</i><span>'.tr('Request new password').'?</span></a></span>',
					], // children
				]), // Column
				$this->showGuide ? new Widget(['children' => SG\getFirst($this->guideText->children, [$this->guideText])]) : NULL,
				$this->postText ? new Widget(['children' => SG\getFirst($this->postText->children, [$this->postText])]) : NULL,
			], // children
		]);
	}
}

/**
 * Widget   :: widget_signform
 * Call from html class "widget signform"
 * Author   :: Little Bear<softganz@gmail.com>
 *
 * Created  :: 2011-11-04
 * Modified :: 2026-07-29
 * Version  :: 2
 *
 * Get widget signform
 *
 * @param String $para
 * 	data-header=Header
 * 	data-limit=Limit (default all)
 * 	data-order=Order Field
 * 	data-sort=ASC|DESC
 * @return String
 */
function widget_signform() {
	$para = para(func_get_args(), 'class=signform');
	$para->nocontainer = 'yes';
	$ret = '';

	$ret .= '<a href="'.url('my').'">'
			. (i()->ok ? '<img class="profile-photo" src="'.BasicModel::user_photo(i()->username).'" width="32" height="32" />' : '<i class="icon -material">person</i>')
			. '<span>'.(i()->ok ? '<strong>'.i()->name.'</strong>' : tr('Member zone','มุมสมาชิก')).'</span>'
			. '</a>';

	if (i()->ok) {
		$ret .= '<ul class="menu -sub -member">'._NL;
		$ret .= '<li><a href="'.url('my').'">'.tr('Welcome').' '.i()->name.'</a></li>'._NL;
		$ret .= '<li><a href="'.url('my').'">'.tr('Edit My <strong>Profile</strong>').'</a></li>'._NL;
		if ($para->{'data-paper'}) {
			foreach (explode(',', $para->{'data-paper'}) as $fid) {
				list($fid, $text) = explode(':', $fid);
				$text = $text ? $text : tr('Create').' <strong>'.$fid.'</strong> '.tr('content');
				if (user_access('administer papers,create '.$fid.' paper'))
					$ret .= '<li><a href="'.url('paper/post/'.$fid).'">'.$text.'</a></li>'._NL;
			}
		}
		if (isset($para->link) && is_array($para->links) && $para->links)
			foreach ($para->links as $link)
				$ret .= '<li>'.$link.'</li>'._NL;

		if (isset($para->menu) && is_array($para->menu) && $para->menu) {
			foreach ($para->menu as $menu) {
				if (user_access($menu['access']))
					$ret .= '<li class="member-zone-link">'
							. '<a href="'.$menu['url'].'">'.$menu['text'].'</a>'
							. '</li>'._NL;
			}
		}

		if (module_install('blog'))
			$ret .= '<li><a href="'.url('dashboard').'">บล็อก (Blog) ของฉัน</a></li>'._NL;

		if (module_install('paper'))
			$ret .= '<li><a class="-new" href="'.url('paper/my').'">จัดการเอกสาร</a></li>'._NL;

		if (user_access('access administrator pages'))
			$ret .= '<li><a href="'.url('admin').'">Website <strong>Admin</strong>istrator</a></li>'._NL;

		$ret .= '<li><a href="'.url('signout').'">'.tr('Sign out').'</a></li>'._NL;
		$ret .= '</ul>'._NL;
	} else if ($para->form=='link') {
		$ret .= '<ul><li><a href="'.url('user').'">'.tr('Sign in').'</a></li><li><a href="'.url('user/register').'">'.tr('Create Account').'!</a></li></ul>';
	} else {
		//			$ret.='$_GET[action]='.$_GET['action'].' , $para->actiion='.$para->action.' , _URL='._URL.' , HTTP_REFERER='.$_SERVER['HTTP_REFERER'];

		$form = new Form([
			'action' => \SG\getFirst(url(q())),
			'id' => $para->id ? $para->id : 'signin-'.uniqid(),
			'class' => $para->class,
			// $form->config->action = SG\getFirst($_GET['action'],$para->action,url(q()),$_SERVER['HTTP_REFERER']);
			// $form->config->action = SG\getFirst($_GET['action'],$para->action,_URL,$_SERVER['HTTP_REFERER']);
			'children' => [
				'username' => [
					'name' => 'username',
					'type' => 'text',
					'id' => 'username-'.uniqid(),
					'label' => tr('Username').' '.tr('or').' '.tr('e-mail'),
					'class' => '-username -fill',
					'placeholder' => 'Username',
					'maxlength' => 50,
					'autocomplete' => 'off',
					'container' => '{class: "-label-in"}',
				],
				'password' => [
					'name' => 'password',
					'type' => 'password',
					'id' => 'password-'.uniqid(),
					'label' => tr('Password'),
					'class' => '-password -fill',
					'placeholder' => 'Password',
					'maxlength' => 20,
					'posttext' => '<i class="icon -material -show-password" onClick=\'showPassword(this)\'>visibility_off</i>',
					'container' => ['class' => '-label-in -group'],
				],
				'cookielength' => [
					'name' => 'cookielength',
					'type' => 'select',
					'id' => uniqid(),
					'class' => '-fill',
					'options' => array(
						'60' => '1 '.tr('Hour'),
						'1440' => '1 '.tr('Day'),
						'10080' => '1 '.tr('Week'),
						'43200' => '1 '.tr('Month'),
						'-1' => tr('Forever')
					),
					'value' => \SG\getFirst($para->cookielength, $_POST['cookielength'], 10080)
				],
				'signin' => [
					'type' => 'button',
					'name' => 'signin',
					'value' => '<i class="icon -material">login</i><span>'.tr('Sign in').'</span>',
				],
				user_access('register new member') ? '<a class="btn -link" href="'.url('user/register').'"><i class="icon -material">person_add</i><span>'.tr('Create new account').'</span></a>' : NULL,
				'<a class="btn -link" href="#" onclick="window.location=\''.url('user/password').'\';return false;"><i class="icon -material">restore</i><span>'.tr('Request new password').'?</span></a>',
			], // children
		]);

		$ret .= '<ul><li>'.$form->build().'</li></ul>'._NL;
	}
	return array($ret,$para);
}
?>