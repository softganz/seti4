<?php
/**
* Admin   :: User Access Setting
* Created :: 2016-11-08
* Modify  :: 2023-01-28
* Version :: 2
*
* @return Widget
*
* @usage admin/user/access
*/

class AdminUserAccess extends Page {
	function build() {
		if (!user_access('administer access control')) return message('error','access denied');

		$roles = (Array) cfg('roles');
		unset($roles['admin']);

		$perms = (Array) cfg('perm');
		ksort($perms);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'User Management &gt; Access control',
				'trailing' => '<form id="search" class="search-box" method="get" action="'.url('admin/user/list').'" name="memberlist" role="search">'
					. '<input type="hidden" name="sid" id="sid" /><input id="search-box" class="sg-autocomplete" type="text" name="q" size="20" value="'.$_GET['q'].'" data-query="'.url('admin/get/username').'" data-altfld="sid" data-callback="submit" placeholder="Username or Name or Email"><button><i class="icon -material">search</i></button>'
					. '</form>',
				'navigator' => 	R::View('admin.default.nav'),
			]), // AppBar

			'body' => new Widget([
				'children' => [
					'สิทธิ์การใช้งานเป็นการกำหนดเพื่อสิทธิ์ให้แก่สมาชิกของแต่ละบทบาทในการเข้าถึงเว็บไซท์ สามารถกำหนดสิทธิ์ให้แต่ละบทบาทสามารถทำอะไรได้บ้าง ถ้าสมาชิกมีหลายบทบาท สิทธิ์ที่ได้รับก็จะเป็นการรวมสิทธิ์ของทุกบทบาทที่เป็นอยู่',
					new Form([
						'action' => url('api/admin/save.useraccess'),
						'class' => 'sg-form',
						'rel' => 'notify',
						'children' => array_map(
							// Each module
							function($perm, $perm_value) use($roles) {
								return new Card([
									'children' => [
										new ListTile([
											'title' => $perm.' module',
											'leading' => new Icon('stars'),
										]),

										// Each permission of module
										new ScrollView([
											'child' => new Table([
												'class' => 'admin-user-access -center -nowrap',
												'thead' => ['permission -left' => 'Permission'] + array_keys($roles),
												'children' => array_map(
													function($permName) use($roles) {
														$permName = trim($permName);
														return ['perm -sg-text-left' => $permName] +
															array_map(
																function($role_name, $role_perm) use($permName) {
																	return '<input type="checkbox" name=access['.$role_name.'][] value="'.$permName.'" '.(in_array($permName, explode(',', $role_perm)) ? 'checked="checked"' : '').' />';
																},
																array_keys($roles), $roles
															);
													},
													explode(',', $perm_value)
												), // children
											]), // Table
										]), // ScrollView

										// Save button
										new Container([
											'class' => '-sg-text-right -sg-paddingnorm',
											'child' => '<button class="btn -primary" type="submit"><i class="icon -material">done_all</i><span>Save permissions</span></button>',
										]), // Container
										// new DebugMsg($perm_value),
									],
								]);
							},
							array_keys($perms), $perms
						), // children
					]), // Form

					'<style type="text/css">
					.admin-user-access .perm {text-align: left !Important; font-weight: bold;}
					</style>',
				], // children
			]), // Widget
		]);
	}
}
?>