<?php
/**
 * Admin    :: Admin Main Page
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2016-11-08
 * Modified :: 2026-11-03
 * Version  :: 10
 *
 * @uses admin
 */

use Softganz\DB;

class Admin extends Page {
	function build() {
		if (!DB::tableExists('%variable%')) {
			location('admin/install');
		}

		$menuList = [
			'content' => 'Content Management',
			'site' => 'Site Building',
			'user' => 'User Management',
			'config' => 'Site Configuration',
			'log' => 'Logs'
			// 'help' => ''
		];

		$dbInfo = DB::select([
			'SELECT VERSION() `version`, DATABASE() `db_name` LIMIT 1'
		]);

		return new Scaffold([
			'appBar' => new AdminAppBarWidget(),
			'body' => new Widget([
				'children' => [
					'<div class="help">Welcome to the administration section. Here you may control how your site functions.</div>',

					'<p>Core folder <b>'
					. cfg('core.version')
					. '@'
					. cfg('core.folder')
					. '</b></p>',
					'<p>nginx version <b>' . $_SERVER['SERVER_SOFTWARE'] . '</b></p>',
					'<p>PHP version <b>' . phpversion() . '</b></p>',
					'<p>MariaDB version <b>'
					. $dbInfo->valueOf('version')
					. '@'
					. $dbInfo->valueOf('db_name')
					. ';'
					. cfg('db.character_set_name')
					. '</b></p>',
					'<p><em>Today is <strong>'
					. date('Y-m-d H:i:s')
					. '</strong> and server timezone offset is <strong>'
					. cfg('server.timezone.offset')
					. ' hours</strong> so datetime to use by program is <strong>????-??-??</strong></em></p>',
					cfg('version.install') < cfg('core.version.install')
						? '<p>New version was release. Please <a href="'
							. url('admin/site/upgrade')
							. '">upgrade database table</a>.</p>'
						: '',

					new Container([
						'class' => 'admin-panel -home',
						'children' => (function ($menuList) {
							$childrens = [];
							foreach ($menuList as $menuKey => $menuItem) {
								$menuWidget = 'AdminMenu' . ucfirst($menuKey) . 'Widget';
								import('widget:admin.menu.' . $menuKey . '.php');
								$childrens[] = new Widget([
									'children' => [
										new Card([
											'children' => [
												new ListTile([
													'title' => $menuItem,
													'leading' => new Icon('stars'),
													'trailing' => new Button([
														'type' => 'normal',
														'href' => url('admin/' . $menuKey),
														'icon' => new Icon('settings')
													]) // Button
												]) // ListTile
											] // children
										]), // Card

										class_exists($menuWidget) ? new $menuWidget() : null
									] // children
								]);
							}
							return $childrens;
						})($menuList)
					]) // Container
				] // children
			]) // Widget
		]);
	}
}
?>
