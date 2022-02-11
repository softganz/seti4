<?php
// user roles management
function admin_user_roles($self) {
	$ret .= sg_client_convert('<h3>บทบาท</h3>
<div class="help">บทบาทเป็นการกำหนดสิทธิ์ของระบบรักษาความปลอดภัยและการจัดการข้อมูล. การกำหนดบทบาทของกลุ่มสมาชิกเพื่อให้มีสิทธิ์ในการจัดการกับข้อมูล เช่น anonymous, member , moderator, administrator และอื่น ๆ. คุณสามารถกำหนดบทบาทใหม่ได้.<br /><br />
บทบาทที่กำหนดมาให้แล้วคือ
<ul>
<li>Anonymous user : (anonymous) สำหรับผู้เข้าดูเว็บทั่วไปที่ไม่ได้สมัครสมาชิก.</li>
<li>Authenticated user: (member)สำหรับผู้ที่เป็นสมาชิกและได้ logged เข้าสู่ระบบแล้ว.</li>
</ul>
</div>');

	$roles=cfg('roles');
	$tables = new Table();
	$tables->thead=array('Role Name','Role Permission','Operations');
	while (list($role,$value)=each($roles)) {
		if ($role=='admin') continue;
		$tables->rows[]=array(
											$role,
											$value,
											'<a href="'.url('admin/user/roles/edit/'.$role).'" title="edit permissions"><i class="icon -edit"></i><span class="-hidden">Edit Permissions</span></a>'
											);
	}
	$tables->rows[]=array(
										'<input class="form-text -fill" type="text" size="20" name="rolename" placeholder="Enter new role name" />',
										'<button class="btn -primary" type="submit"><i class="icon -addbig -white"></i><span>Add Role</span></button>'
										);
	$ret.='<form method="post" action="'.url('admin/user/roles/create').'">';
	$ret.=$tables->build();
	$ret.='</form>';
	return $ret;
}
?>