<?php
/**
* Admin :: Main Page
* Created 2016-11-08
* Modify  2022-03-31
*
* @return Widget
*
* @usage admin
*/

class Admin extends Page {
	function build() {
		if (!mydb::table_exists('%variable%')) {
			location('admin/install');
		}

		$menuList = [
			'content' => 'Content Management',
			'site' => 'Site Building',
			'user' => 'User Management',
			'config' => 'Site Configuration',
			'log' => 'Logs',
			// 'help' => ''
		];

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Web Site Administrator on '.cfg('core.version'),
				'trailing' => '<form id="search" class="search-box" method="get" action="'.url('admin/user/list').'" name="memberlist" role="search">'
					. '<input type="hidden" name="sid" id="sid" /><input id="search-box" class="sg-autocomplete" type="text" name="q" size="20" value="'.$_GET['q'].'" data-query="'.url('admin/get/username').'" data-altfld="sid" data-callback="submit" placeholder="Username or Name or Email"><button><i class="icon -material">search</i></button>'
					. '</form>',
				'navigator' => 	R::View('admin.default.nav'),
			]), // AppBar
			'body' => new Widget([
				'children' => [
					'<div class="help">Welcome to the administration section. Here you may control how your site functions.</div>',

					'<p>Core folder <b>'.cfg('core.version').'@'.cfg('core.folder').'</b></p>',
					'<p><em>Today is <strong>'.date('Y-m-d H:i:s').'</strong> and server timezone offset is <strong>'.cfg('server.timezone.offset').' hours</strong> so datetime to use by program is <strong>????-??-??</strong></em></p>',
					(cfg('version.install') < cfg('core.version.install')?'<p>New version was release. Please <a href="'.url('admin/site/upgrade').'">upgrade database table</a>.</p>':''),

					new Container([
						'class' => 'admin-panel -home',
						'children' => (function($menuList) {
							$childrens = [];
							foreach ($menuList as $menuKey => $menuItem) {
								$menuWidget = 'AdminMenu'.ucfirst($menuKey).'Widget';
								import('widget:admin.menu.'.$menuKey.'.php');
								$childrens[] = new Widget([
									'children' => [
										new Card([
											'children' => [
												new ListTile([
													'title' => $menuItem,
													'leading' => new Icon('stars'),
													'trailing' => new Button([
														'type' => 'normal',
														'href' => url('admin/'.$menuKey),
														'icon' => new Icon('settings'),
													]), // Button
												]), // ListTile
											], // children
										]), // Card

										class_exists($menuWidget) ? new $menuWidget() : NULL,
										// R::View('admin.menu.'.$menuKey),
									], // children
								]);
							}
							return $childrens;
						})($menuList),
					]), // Container

				], // children
			]), // Widget
		]);
	}
}
?>