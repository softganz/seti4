<?php
/**
* SignIn  :: Sign In
* Created :: 2019-09-05
* Modify  :: 2022-10-02
* Version :: 2
*
* @return Widget
*
* @usage signin
*/

class Signin extends Page {
	var $username;
	var $password;
	var $time;
	var $ret;
	var $rel;
	var $signRet;
	var $showTime;
	var $showGuide;
	var $showInfo;
	var $showRegist;

	function __construct() {
		parent::__construct([
			'username' => post('u'),
			'password' => post('pw'),
			'time' => post('time'),
			'ret' => post('ret'),
			'rel' => post('rel'),
			'signRet' => post('signret'),
			'showTime' => post('showTime'),
			'showHeader' => post('showHeader') === '0' ? false : true,
			'showFooter' => post('showFooter') === '0' ? false : true,
			'showNav' => post('showNav') === '0' ? false : true,
			'showGuide' => post('showGuide') === '0' ? false : true,
			'showInfo' => post('showInfo') === '0' ? false : true,
			'showRegist' => post('showRegist') === '0' ? false : true,
			'done' => post('done'),
		]);
	}

	function build() {
		if (i()->ok) {
			location('my');
		} else {
			$options = (Object) [
				'username' => $this->username,
				'password' => $this->password,
				'time' => $this->time,
				'ret' => $this->ret,
				'rel' => $this->rel,
				'signret' => $this->signRet,
				'showTime' => $this->showTime,
				'showGuide' => $this->showGuide,
				'showInfo' => $this->showInfo,
				'showRegist' => $this->showRegist,
				'done' => $this->done,
			];
		}

		if (!$this->showHeader) cfg('web.header', false);
		if (!$this->showNav) cfg('web.navigator', false);
		if (!$this->showFooter) cfg('web.footer', false);

		return new Scaffold([
			'body' => new Widget([
				'children' => [
					'<header class="header -box -hidden"><h3>@Secure Sign In</h3></header>',
					R::View('signform', $options),
					// print_o($this, '$this'),
					'<style>
					.package-footer {display: none !Important}'
					. ($this->showHeader ? '' : '.page.-header {display: none !Important;}')
					. ($this->showFooter ? '' : '.page.-footer {display: none !Important;}')
					. '</style>',
				], // children
			]), // Widget
		]);
	}
}
?>