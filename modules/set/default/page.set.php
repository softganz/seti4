<?php
/**
* Set :: Home Page
* Created 2021-05-31
* Modify  2022-01-22
*
* @return Widget
*
* @usage set
*/

class Set extends Page {
	function build() {
		if (!user_access('administrator sets,access sets')) return message('error','access denied');

		$symbol = strtoupper(post('symbol'));

		return new Scaffold([
			'body' => new Widget([
				'children' => [
					new Container([
						'id' => 'set-search',
						'class' => 'set-search',
						'child' => '<form method="get" action="'.url('set/view').'" class="search-box" role="search"><input id="symbolSearch" class="form-text sg-autocomplete" data-query="set/api/symbol" data-minlengthsss="1" size="3" maxlength="200" accesskey="/" name="symbol" autocomplete="off" tabindex="" title="" type="text" placeholder="Search Symbol..." value="'.$symbol.'" data-select="value" data-callback="submit" /><button type="submit"><i class="icon -material">search</i></form>',
					]), // Container

					new Container([
						'id' => 'app-sidebar',
						'class' => 'app-sidebar',
						'children' => [
							'<h3 id="set-title" class="set-title"><a href="'.url('set').'" title="Click to refresh">@</a><a class="sg-action" href="'.url('set/portstatus').'" title="Set@home" data-rel="#app-output">SET</a><span></span></h3>',

							// Side Bar Menu
							new Container([
								'id' => 'app-sidebar-menu',
								'child' => new Ui([
									'children' => [
										'<a id="portstatus" class="sg-action" href="'.url('set/portstatus').'" data-rel="#app-output" data-done="load:#app-sidebar-content:'.url('set/port').'">Port Folio</a>',
										'<a class="sg-action" href="'.url('set/wishlist').'" data-rel="#app-output" data-done="load:#app-sidebar-content:'.url('set/wishlist').'">Wish List</a>',
										'<a class="sg-action" href="'.url('set/portstatus',array('show'=>'closed')).'" data-rel="#app-output">Close Symbol</a>',
										user_access('administrator sets') ? '<a class="sg-action" href="'.url('set/admin').'" data-rel="#app-output">Admin</a>' : NULL,
									], // children
								]), // Ui
							]), // Container

							// Side Bar Symbol List
							'<div id="app-sidebar-content" data-load="'.url('set/port').'"></div>',
						], // children
					]), // Container

					// Main Content
					new Container([
						'id' => 'app-output',
						'class' => 'app-output',
						'attribute' => ['data-load' => url($symbol ? 'set/view/'.$symbol : 'set/portstatus', ['d' => post('d')])],
						'children' => [
							'<div class="loader -rotate"></div>',
							'<span>Loading.....</span>',
						],
					]), // Container
					'<a id="set-refresh" class="button" href="javascript:;">Refresh OFF</a>',
					$this->_script(),
				], // children
			]), // Widget
		]);
	}

	function _script() {
		cfg('social.googleplus',false);
		cfg('social.facebook',false);

		head('jquery.jeditable.js','<script type="text/javascript" src="/js/jquery.jeditable.js"></script>');
		head('jquery.jeditable.datepicker.js','<script type="text/javascript" src="/js/jquery.jeditable.datepicker.js"></script>');
		head('jquery.jeditable.popup.js','<script type="text/javascript" src="/js/jquery.jeditable.popup.js"></script>');
		head('jquery.form.js','<script type="text/javascript" src="/js/jquery.form.js"></script>');
		head('jspi','<script type="text/javascript" src="https://www.google.com/jsapi"></script>');
		head('set.js','<script type="text/javascript" src="set/js.set.js"></script>');

	}
}
?>