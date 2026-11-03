<?php
/**
 * Admin    :: Admin AppBar Widget
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2024-08-19
 * Modified :: 2026-11-03
 * Version  :: 5
 *
 * @param array $_args
 * @return AppBar
 *
 * @uses new AdminAppBarWidget($args)
 */

class AdminAppBarWidget extends AppBar {
	public ?string $search = null;

	function __construct(array $args = []) {
		$args = array_merge(
			[
				'title' => 'Web Site Administrator on ' . cfg('core.version'),
				'leading' => '<i class="icon -material">admin_panel_settings</i>',
				'trailing' => $this->searchUser(),
				'navigator' => $this->navigator(),
				'search' => \Request::all('q')
			],
			$args
		);
		parent::__construct($args);
	}

	private function searchUser() {
		if (!user_access('administer users')) {
			return null;
		}

		return '<form id="search" class="search-box" method="get" action="'
			. url('admin/user/list')
			. '" name="memberlist" role="search">'
			. '<input type="hidden" name="sid" id="sid" />'
			. '<input id="search-box" class="sg-autocomplete" type="text" name="q" size="20" value="'
			. $this->search
			. '" data-query="'
			. url('admin/get/username')
			. '" data-altfld="sid" data-callback="submit" placeholder="Username or Name or Email">'
			. '<button><i class="icon -material">search</i></button>'
			. '</form>';
	}

	private function navigator() {
		$activeUrl = q(1);

		return new Nav([
			'children' => array_merge(
				[
					is_admin()
						? '<a href="'
							. url('admin')
							. '"><i class="icon -material">home</i><span class="">Home</span></a>'
						: null,
					//'<a href="'.url('admin/task').'">'.tr('By task').'</a>',
					//'<a href="'.url('admin/module').'">'.tr('By modules').'</a>',
					is_admin()
						? '<a href="'
							. url('admin/content')
							. '"><i class="icon -material">ballot</i><span>{tr:Content}</span></a>'
						: null,
					is_admin()
						? '<a href="'
							. url('admin/site')
							. '"><i class="icon -material">build</i><span>Site</span></a>'
						: null,
					user_access('administer users')
						? '<a href="'
							. url('admin/user')
							. '"><i class="icon -material">people</i><span>Users</span></a>'
						: null,
					is_admin()
						? '<a href="'
							. url('admin/config')
							. '"><i class="icon -material">web</i><span>Config</span></a>'
						: null,
					is_admin()
						? '<a href="'
							. url('admin/log')
							. '"><i class="icon -material">check_box</i><span>Logs</span></a>'
						: null
				],
				$activeUrl === 'site'
					? [
						'<sep>',
						'<a href="'
						. url('admin/site/info')
						. '"><i class="icon -material">ballot</i><span>{tr:Site Information}</span></a>',
						'<a href="'
						. url('admin/site/theme')
						. '"><i class="icon -material">ballot</i><span>{tr:Theme}</span></a>'
					]
					: [],
				$activeUrl === 'user'
					? [
						'<sep>',
						'<a href="'
						. url('admin/user/list')
						. '" title="All User"><i class="icon -material">ballot</i><span>All User</span></a>',
						'<a href="'
						. url('admin/user/list', 's=enable')
						. '" title="Enabled User"><i class="icon -material">ballot</i><span>Enabled</span></a>',
						'<a href="'
						. url('admin/user/list', 's=disable')
						. '" title="Disabled User"><i class="icon -material">ballot</i><span>Disabled</span></a>',
						'<a href="'
						. url('admin/user/list', 's=block')
						. '" title="Blocked User"><i class="icon -material">ballot</i><span>Blocked</span></a>',
						'<a href="'
						. url('admin/user/list', 's=waiting')
						. '" title="Waiting User"><i class="icon -material">ballot</i><span>Waiting</span></a>',
						'<a href="'
						. url('admin/user/list', 'r=1')
						. '" title="User have roles"><i class="icon -material">ballot</i><span>Roles</span></a>'
					]
					: []
			)
		]);
	}
}
?>
