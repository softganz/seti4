<?php
/**
* Module :: Description
* Created 2022-07-20
* Modify 	2022-07-20
*
* @param Array $args
* @return Widget
*
* @usage new SignInWidget([])
*/

$debug = true;

class SignInWidget extends Widget {

	function __construct($args = []) {
		parent::__construct($args);
	}

	function build() {
		$para = para(func_get_args(), 'class=signform');
		$para->nocontainer = 'yes';

		return new Form([
				'action' => SG\getFirst(url(q())),
				'id' => $para->id ? $para->id : 'signin-'.uniqid(),
				'class' => $para->class,
				// $form->config->action=SG\getFirst($_GET['action'],$para->action,url(q()),$_SERVER['HTTP_REFERER']);
				// $form->config->action=SG\getFirst($_GET['action'],$para->action,_URL,$_SERVER['HTTP_REFERER']);
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
						'container' => '{class: "-label-in"}',
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
						'value' => SG\getFirst($para->cookielength, $_POST['cookielength'], 10080)
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
	}
}
?>