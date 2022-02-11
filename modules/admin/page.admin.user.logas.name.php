<?php
/**
* Admin :: Log As User
* Created 2018-09-27
* Modify  2021-10-17
*
* @param String $username
* @return Widget
*
* @usage admin/user/logas/{username}
*/

$debug = true;

class AdminUserLogasName extends Page {
	var $username;

	function __construct($username = NULL) {
		$this->username = SG\getFirst(post('logasusername'), $username);
	}

	function build() {
		if (!user_access('access administrator pages') || in_array($this->username, ['root','softganz'])) return message('error','access denied');

		if ($this->username) {
			$rs = R::Model('user.get', ['username' => $this->username]);

			if ($rs->_empty) return message('error','Username '.$this->username.' not found.');
			else if ($rs->uid <= 1) return message('error','Access denied:Cannot log as this username.');

			// Start login as username
			$remember_time = time()+60*60;
			$session_id = i()->session;

			setcookie(cfg('cookie.u'),$rs->username,$remember_time, cfg('cookie.path'),cfg('cookie.domain'));

			cache::clear('user:'.$session_id);

			// add session into cache
			$user = (Object) [
				'ok' => true,
				'uid' => intval($rs->uid),
				'username' => $rs->username,
				'name' => $rs->name,
				'roles' => $rs->roles,
				'session' => $session_id,
				'remember' => 60*60,
				'ip' => GetEnv('REMOTE_ADDR'),
			];
			cache::add('user:'.$session_id, $user, $remember_time, $this->username);

			$_SESSION['logas'] = i();
			$_SESSION['user'] = $user;

			// print_o($user,'$user', 1);
			// print_o($_SESSION,'$_SESSION',1);
			// print_o(i(),'$user',1);

			location();
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Login AS User',
			]),
			'body' => new Form([
				'action' => url(q()),
				'children' => [
					'logasusername' => [
						'type'=>'text',
						'name'=>'logasusername',
						'label'=>'LOG AS Username',
						'class'=>'-fill',
						'require'=>true,
						'placeholder'=>'username',
						'description'=>'Enter username that you want to log as.'
					],
					'submit' => [
						'type'=>'button',
						'value'=>'<i class="icon -material">vpn_key</i><span>LOG AS</span>',
						'container' => '{class: "-sg-text-right"}',
					],
				],
			]),
		]);
	}
}
?>