<?php
/**
 * My       :: Sidebar Menu
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 20xx-xx-xx
 * Modified :: 2026-11-15
 * Version  :: 3
 *
 * @uses new MySidebarWidget()
 */

declare(strict_types=1);

namespace My\Widgets;

class MySidebarWidget extends \SideBar {
	public ?string $class = '-my-sidebar';
	public object $right;

	function __construct(?string $active = null) {
		parent::__construct();

		$this->right = (object) [
			'admin' => is_admin()
		];

		$this->children = $this->menus();
		$this->gripHeader = new \Header(
			title: 'My Account',
			leading: new \Icon('account_circle')
		);
	}

	/**
	 * Widget menu
	 *
	 * @return array
	 */
	function menus(): array {
		return [
			new \Header(title: 'My Account', leading: new \Icon('manage_accounts')),
			new \Button(
				type: 'link',
				href: \Url::link('my'),
				icon: new \Icon('account_circle'),
				text: 'รายละเอียด'
			),
			new \Button(
				type: 'link',
				href: \Url::link('my/change/detail'),
				icon: new \Icon('person_edit'),
				text: '{tr:Change Your Profile Details}'
			),
			new \Button(
				type: 'link',
				href: \Url::link('my/change/password'),
				icon: new \Icon('enhanced_encryption'),
				text: '{tr:Change Password}'
			),
			new \Button(
				type: 'link',
				href: \Url::link('my/change/photo'),
				icon: new \Icon('add_a_photo'),
				text: '{tr:Change Photo}'
			),
			new \Button(
				type: 'link',
				class: '-change-color-scheme',
				icon: new \Icon(
					R('colorScheme') === 'dark' ? 'dark_mode' : 'light_mode',
					class: '-change-color-scheme'
				),
				text: '<span class="-change-color-scheme">'
					. (R('colorScheme') === 'dark' ? 'Dark mode' : 'Light mode')
					. '</span>'
			),
			new \Header(title: 'My Actions', leading: new \Icon('folder')),
			new \Button(
				type: 'link',
				href: \Url::link('my/doc'),
				icon: new \Icon('description'),
				text: 'My Documents'
			),
			new \Button(
				type: 'link',
				href: \Url::link('my/like'),
				icon: new \Icon('thumb_up_alt'),
				text: 'My Likes'
			),
			new \Button(
				type: 'link',
				href: \Url::link('my/bookmark'),
				icon: new \Icon('favorite'),
				text: 'My Bookmarks'
			),
			new \Button(
				type: 'link',
				href: \Url::link('my/view'),
				icon: new \Icon('preview'),
				text: 'My Views'
			),
			new \Button(
				type: 'link',
				href: \Url::link('my/photo'),
				icon: new \Icon('image'),
				text: 'My Photos'
			),
			$this->right->admin
				? new \Children(
					children: [
						new \Header(
							title: 'My Admin',
							leading: new \Icon('admin_panel_settings')
						),
						new \Button(
							type: 'link',
							href: \Url::link('admin'),
							icon: new \Icon('settings'),
							text: 'Website Administrator'
						),
						new \Button(
							type: 'link',
							href: \Url::link('admin/site'),
							icon: new \Icon('settings'),
							text: 'Site Building'
						),
						new \Button(
							type: 'link',
							href: \Url::link('admin/user/list'),
							icon: new \Icon('people'),
							text: 'User Management'
						),
						new \Button(
							type: 'link',
							href: \Url::link('stats'),
							icon: new \Icon('assessment'),
							text: 'Web Statistic'
						),
						new \Button(
							type: 'link',
							href: \Url::link('watchdog/analysis'),
							icon: new \Icon('assessment'),
							text: 'Log analysis'
						)
					]
				)
				: null,
			'<sep>',
			new \Button(
				type: 'link',
				href: \Url::link('signout'),
				icon: new \Icon('lock_open'),
				text: '{tr:Sign Out}'
			)
		];
	}
}
?>
