<?php
/**
 * Admin    :: Admin AppBar Widget
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2024-08-19
 * Modified :: 2026-09-18
 * Version  :: 4
 *
 * @param Array $_args
 * @return AppBar
 *
 * @uses new AdminAppBarWidget($args)
 */

class AdminAppBarWidget extends AppBar {
	var $title;
	var $leading;
	var $trailing;
	var $navigator;
	var $search;

	function __construct($args = []) {
		$args = (object) array_merge(
			[
				'title' => 'Web Site Administrator on '.cfg('core.version'),
				'leading' => '<i class="icon -material">admin_panel_settings</i>',
				'trailing' => $this->searchUser(),
				'navigator' => $this->navigator(),
				'search' => $_GET['q'] ?? null,
			],
			(array) $args
		);
		parent::__construct($args);
	}

	private function searchUser() {
		if (!user_access('administer users')) return NULL;

		return '<form id="search" class="search-box" method="get" action="'.url('admin/user/list').'" name="memberlist" role="search">'
			. '<input type="hidden" name="sid" id="sid" />'
			. '<input id="search-box" class="sg-autocomplete" type="text" name="q" size="20" value="' . $this->search . '" data-query="'.url('admin/get/username').'" data-altfld="sid" data-callback="submit" placeholder="Username or Name or Email">'

			. '<button><i class="icon -material">search</i></button>'
			. '</form>';
	}

	private function navigator() {
		$activeUrl = q(1);

		return new Nav([
			'children' => array_merge(
				[
					is_admin() ? '<a href="'.url('admin').'"><i class="icon -material">home</i><span class="">Home</span></a>' : NULL,
					//'<a href="'.url('admin/task').'">'.tr('By task').'</a>',
					//'<a href="'.url('admin/module').'">'.tr('By modules').'</a>',
					is_admin() ? '<a href="'.url('admin/content').'"><i class="icon -material">ballot</i><span>{tr:Content}</span></a>' : NULL,
					is_admin() ? '<a href="'.url('admin/site').'"><i class="icon -material">build</i><span>Site</span></a>' : NULL,
					user_access('administer users') ? '<a href="'.url('admin/user').'"><i class="icon -material">people</i><span>Users</span></a>' : NULL,
					is_admin() ? '<a href="'.url('admin/config').'"><i class="icon -material">web</i><span>Config</span></a>' : NULL,
					is_admin() ? '<a href="'.url('admin/log').'"><i class="icon -material">check_box</i><span>Logs</span></a>' : NULL
				],
				$activeUrl === 'site' ? [
					'<sep>',
					'<a href="'.url('admin/site/info').'"><i class="icon -material">ballot</i><span>{tr:Site Information}</span></a>',
					'<a href="'.url('admin/site/theme').'"><i class="icon -material">ballot</i><span>{tr:Theme}</span></a>'
				] : [],
				$activeUrl === 'user' ? [
					'<sep>',
					'<a href="'.url('admin/user/list').'" title="All User"><i class="icon -material">ballot</i><span>All User</span></a>',
					'<a href="'.url('admin/user/list','s=enable').'" title="Enabled User"><i class="icon -material">ballot</i><span>Enabled</span></a>',
					'<a href="'.url('admin/user/list','s=disable').'" title="Disabled User"><i class="icon -material">ballot</i><span>Disabled</span></a>',
					'<a href="'.url('admin/user/list','s=block').'" title="Blocked User"><i class="icon -material">ballot</i><span>Blocked</span></a>',
					'<a href="'.url('admin/user/list','s=waiting').'" title="Waiting User"><i class="icon -material">ballot</i><span>Waiting</span></a>',
					'<a href="'.url('admin/user/list','r=1').'" title="User have roles"><i class="icon -material">ballot</i><span>Roles</span></a>'
				] : []
			)
		]);
	}
}
?>