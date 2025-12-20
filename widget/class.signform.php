<?php
/**
* SignIn  :: Sign Form Widget
* Created :: 2022-07-20
* Modify  :: 2025-12-14
* Version :: 5
*
* @param Array $args
* @return Widget
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
					'guideText' => $this->guideText
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
				'<header><h3>กรณีเป็นสมาชิกเว็บอยู่แล้ว</h3></header>',
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
				$this->showGuide ? $this->guideText : NULL,
			], // children
		]);
	}
}
?>