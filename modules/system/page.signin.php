<?php
/**
* SignIn  :: Sign In
* Created :: 2019-09-05
* Modify  :: 2025-12-10
* Version :: 3
*
* @return Widget
*
* @usage signin
*/

class Signin extends Page {
	var $username;
	var $password;
	var $time = 10080;
	var $ret;
	var $rel;
	var $signRet;
	var $showTime = true;
	var $showGuide;
	var $showInfo;
	var $showRegist;
	var $complete;

	function __construct() {
		parent::__construct([
			'username' => Request::all('u'),
			'password' => Request::all('pw'),
			'time' => SG\getFirst(Request::all('time'), $this->time),
			'ret' => Request::all('ret'),
			'rel' => Request::all('rel'),
			'signRet' => Request::all('signret'),
			'showTime' => SG\getFirst(Request::all('showTime'), $this->showTime),
			'showHeader' => Request::all('showHeader') === '0' ? false : true,
			'showFooter' => Request::all('showFooter') === '0' ? false : true,
			'showNav' => Request::all('showNav') === '0' ? false : true,
			'showGuide' => Request::all('showGuide') === '0' ? false : true,
			'showInfo' => Request::all('showInfo') === '0' ? false : true,
			'showRegist' => Request::all('showRegist') === '0' ? false : true,
			'done' => Request::all('done'),
			'complete' => Request::all('complete')
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
				'complete' => $this->complete,
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