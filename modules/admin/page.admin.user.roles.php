<?php
/**
* Admin :: User Rolws
* Created 2022-04-01
* Modify  2022-04-01
*
* @return Widget
*
* @usage admin/user/roles
*/

class AdminUserRoles extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'User Roles',
				'navigator' => 	R::View('admin.default.nav'),
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new ListTile([
						'title' => 'บทบาท',
						'subtitle' => '<div class="help">บทบาทเป็นการกำหนดสิทธิ์ของระบบรักษาความปลอดภัยและการจัดการข้อมูล. การกำหนดบทบาทของกลุ่มสมาชิกเพื่อให้มีสิทธิ์ในการจัดการกับข้อมูล เช่น anonymous, member , moderator, administrator และอื่น ๆ. คุณสามารถกำหนดบทบาทใหม่ได้.<br /><br />
							บทบาทที่กำหนดมาให้แล้วคือ
							<ul>
							<li>Anonymous user : (anonymous) สำหรับผู้เข้าดูเว็บทั่วไปที่ไม่ได้สมัครสมาชิก.</li>
							<li>Authenticated user: (member)สำหรับผู้ที่เป็นสมาชิกและได้ logged เข้าสู่ระบบแล้ว.</li>
							</ul>
							</div>',
					]), // ListTile

					new Form([
						'action' => url('admin/user/roles/create'),
						'class' => 'sg-form',
						'rel' => 'none',
						'done' => 'load',
						'children' => [
							new Table([
								'thead' => ['Role Name','Role Permission', 'op -center' => 'Operations'],
								'children' => array_map(
									function ($role, $value) {
										return [
											$role,
											$value,
											'<a class="btn -link" href="'.url('admin/user/roles/edit/'.$role).'" title="edit permissions"><i class="icon -material">edit</i><span class="-hidden">Edit Permissions</span></a>'
										];
									},
									array_keys((Array) cfg('roles')),
									(Array) cfg('roles')
								) +
								[
									'input' => [
										'<td colspan="2"><input class="form-text -fill" type="text" size="20" name="rolename" placeholder="Enter new role name" /></td>',
										'<button class="btn -primary" type="submit"><i class="icon -material">add</i><span>Add Role</span></button>'
									],
								],
							]), // Table
						], // children
					]), // Form

				], // children
			]), // Widget
		]);
	}
}
?>