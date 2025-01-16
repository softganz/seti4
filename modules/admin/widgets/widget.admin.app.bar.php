<?php
/**
* Admin   :: Admin AppBar Widget
* Created :: 2024-08-19
* Modify  :: 2025-01-16
* Version :: 3
*
* @param Array $_args
* @return AppBar
*
* @usage new AdminAppBarWidget($shopInfo, $options)
*/

class AdminAppBarWidget extends AppBar {
	var $title;
	var $leading;
	var $trailing;
	var $navigator;

	function __construct($_args = []) {
		parent::__construct($_args);

		$this->shopInfo = $_args['info'];
		$this->shopId = $this->shopInfo->id;
		$this->title = \SG\getFirst($_args['title'], 'Web Site Administrator on '.cfg('core.version'));
		$this->leading = SG\getFirst($_args['leading'], '<i class="icon -material">admin_panel_settings</i>');

		$this->trailing = SG\getFirst($_args['trailing'], $this->searchUser());
		$this->navigator = SG\getFirst($_args['navigator'], $this->navigator());
	}

	private function searchUser() {
		if (!user_access('administer users')) return NULL;

		return '<form id="search" class="search-box" method="get" action="'.url('admin/user/list').'" name="memberlist" role="search">'
			. '<input type="hidden" name="sid" id="sid" />'
			. '<input id="search-box" class="sg-autocomplete" type="text" name="q" size="20" value="'.$_GET['q'].'" data-query="'.url('admin/get/username').'" data-altfld="sid" data-callback="submit" placeholder="Username or Name or Email">'

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