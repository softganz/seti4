<?php
/**
* SignIn  :: Sign In Widget
* Created :: 2022-07-20
* Modify  :: 2023-04-11
* Version :: 2
*
* @param Array $args
* @return Widget
*
* @usage new SignInWidget([])
*/

class SignInWidget extends Widget {
	var $username;
	var $password;
	var $time = 10080;
	var $done;
	var $showRegisterButton = true;
	var $showForgotButton = true;

	function __construct($args = []) {
		parent::__construct($args);
	}

	function build() {
		return new Form([
				'action' => \SG\getFirst($this->returnUrl, url(q())),
				'id' => $this->id ? $this->id : 'signin-'.uniqid(),
				'class' => 'widget-signin signform'.($this->class ? ' '.$this->class : ''),
				'rel' => 'none',
				'done' => $this->done,
				'children' => [
					'username' => [
						'type' => 'text',
						'id' => 'username-'.uniqid(),
						'label' => tr('Username').' '.tr('or').' '.tr('e-mail'),
						'class' => '-username -fill',
						'placeholder' => 'Username',
						'maxlength' => 50,
						'autocomplete' => 'off',
						'value' => $this->username,
						'container' => '{class: "-label-in"}',
					],
					'password' => [
						'type' => 'password',
						'id' => 'password-'.uniqid(),
						'label' => tr('Password'),
						'class' => '-password -fill',
						'placeholder' => 'Password',
						'maxlength' => 20,
						'value' => $this->password,
						'container' => '{class: "-label-in"}',
					],
					'cookielength' => in_array($this->time, [-1, 'forever']) ? [
						'type' => 'hidden',
						'value' => '-1',
					] : [
						'type' => 'select',
						'id' => 'time-'.uniqid(),
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
					$this->showRegisterButton && user_access('register new member') ? '<a class="btn -link" href="'.url('user/register').'"><i class="icon -material">person_add</i><span>'.tr('Create new account').'</span></a>' : NULL,
					$this->showForgotButton ? '<a class="btn -link" href="#" onclick="window.location=\''.url('user/password').'\';return false;"><i class="icon -material">restore</i><span>'.tr('Request new password').'?</span></a>' : NULL,
				], // children
			]);
	}
}
?>