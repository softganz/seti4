<?php
/**
* My :: Side Menu
*
* @return Widget
*/

$debug = true;

function view_my_menu() {
	$isAdmin = user_access('access administrator pages');

	$sidebarUi = new Widget([
			'children' => [
				'<h3>My Account</h3>',
				new Ui([
					'type' => 'menu',
					'children' => [
						'<a href="'.url('my/change/detail').'"><i class="icon -material">person</i><span>{tr:Change Your Profile Details}</span></a>',
						'<a class="sg-action" href="'.url('my/change/password').'" data-rel="#main"><i class="icon -material">enhanced_encryption</i><span>{tr:Change Password}</span></a>',
						'<a href="'.url('my/change/photo').'"><i class="icon -material">add_a_photo</i><span>{tr:Change Photo}</span></a>',
					], // children
				]), // Ui
				'<h3>My Actions</h3>',
				new Ui([
					'type' => 'menu',
					'children' => [
						'<a href="'.url('my/doc').'"><i class="icon -material">description</i><span>My Documents</span></a>',
						'<a href="'.url('my/like').'"><i class="icon -material">thumb_up_alt</i><span>My Likes</span></a>',
						'<a href="'.url('my/bookmark').'"><i class="icon -material">favorite</i><span>My Bookmarks</span></a>',
						'<a href="'.url('my/view').'"><i class="icon -material">preview</i><span>My Views</span></a>',
						'<a href="'.url('my/photo').'"><i class="icon -material">image</i><span>My Photos</span></a>',
					], // children
				]), // Ui

				$isAdmin ? new Widget([
					'children' => [
						'<h3>My Admin</h3>',
						new Ui([
							'type' => 'menu',
							'children' => [
								'<a href="'.url('admin').'"><i class="icon -material">settings</i><span>Website Administrator</span></a>',
								'<a href="'.url('admin/site').'"><i class="icon -material">settings</i><span>Site Building</span></a>',
								'<a href="'.url('admin/user/list').'"><i class="icon -material">people</i><span>User Management</span></a>',
								'<a href="'.url('stats').'"><i class="icon -material">assessment</i><span>Web Statistic</span></a>',
								'<a href="'.url('watchdog/analysis').'"><i class="icon -material">assessment</i><span>Log analysis</span></a>',
							], // children
						]), // Ui
					], // children
				]) : NULL, // Widget

				new Ui([
					'type' => 'menu',
					'children' => [
						'<sep>',
						'<a href="'.url('signout').'"><i class="icon -material">lock_open</i><span>{tr:Sign Out}</span></a>',
					], // children
				]), // Ui
			], // children
	]);

	return $sidebarUi;
}
?>