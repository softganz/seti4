<?php
/**
* Admin : Site Menu
* Created 2016-11-08
* Modify  2022-03-31
*
* @return Widget
*
* @usage new AdminMenuSiteWidget()
*/

class AdminMenuSiteWidget extends Widget {
	function build() {
		return new Column([
			'children' => [
				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Installation',
							'leading' => new Icon(''),
							'trailing' => new Button([
								'type' => 'normal',
								'href' => url('admin/install'),
								'text' => 'Installation',
							]),
							'subtitle' => 'Install new table with other table prefix.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Modules',
							'leading' => new Icon(''),
							'trailing' => new Button([
								'type' => 'normal',
								'href' => url('admin/site/module'),
								'text' => 'Modules',
							]),
							'subtitle' => 'Add / Remove / Configuration site modules.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Site information',
							'leading' => new Icon(''),
							'trailing' => new Button([
								'type' => 'normal',
								'href' => url('admin/site/info'),
								'text' => 'Site information',
							]),
							'subtitle' => 'Change basic site information, such as the site name, slogan, e-mail address, mission, front page and more.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Site initial command',
							'leading' => new Icon(''),
							'trailing' => new Button([
								'type' => 'normal',
								'href' => url('admin/site/init'),
								'text' => 'Site initial command',
							]),
							'subtitle' => 'Command execute before request process.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Site completed command',
							'leading' => new Icon(''),
							'trailing' => new Button([
								'type' => 'normal',
								'href' => url('admin/site/complete'),
								'text' => 'Site completed command',
							]),
							'subtitle' => 'Command execute before tag &lt;/body&gt; was display.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Site maintenance',
							'leading' => new Icon(''),
							'trailing' => new Button([
								'type' => 'normal',
								'href' => url('admin/site/maintenance'),
								'text' => 'Site maintenance',
							]),
							'subtitle' => 'Take the site offline for maintenance or bring it back online.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Site readonly',
							'leading' => new Icon(''),
							'trailing' => new Button([
								'type' => 'normal',
								'href' => url('admin/site/readonly'),
								'text' => 'Site readonly',
							]),
							'subtitle' => 'Take the site into readonly for maintenance.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Themes',
							'leading' => new Icon(''),
							'trailing' => new Button([
								'type' => 'normal',
								'href' => url('admin/site/theme'),
								'text' => 'Themes',
							]),
							'subtitle' => 'Change which theme your site uses or allows users to set.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'Upgrades',
							'leading' => new Icon(''),
							'trailing' => new Button([
								'type' => 'normal',
								'href' => url('admin/site/upgrade'),
								'text' => 'Upgrades',
							]),
							'subtitle' => 'Upgrade website database.',
						]),
					], // children
				]), // Card

				new Card([
					'children' => [
						new ListTile([
							'crossAxisAlignment' => 'start',
							'title' => 'URL aliases',
							'leading' => new Icon(''),
							'trailing' => new Button([
								'type' => 'normal',
								'href' => url('admin/site/path'),
								'text' => 'URL aliases',
							]),
							'subtitle' => 'Change your site\'s URL paths by aliasing them.',
						]),
					], // children
				]), // Card
			], // children
		]);
	}
}
?>