<?php
/**
 * Widget widget_signform
 *
 * @package core
 * @version 0.01
 * @copyright Copyright (c) 2000-present , The SoftGanz Group By Panumas Nontapan
 * @author Panumas Nontapan <webmaster@softganz.com> , http://www.softganz.com
 * @created 2011-11-04
 * @modify 2012-10-16
 * ============================================
 * This program is free software. You can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License.
 * ============================================
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
			. (i()->ok ? '<img class="profile-photo" src="'.model::user_photo(i()->username).'" width="32" height="32" />' : '<i class="icon -person"></i>')
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

		$ret .= '<ul><li>'.$form->build().'</li></ul>'._NL;
	}
	return array($ret,$para);
}
?>