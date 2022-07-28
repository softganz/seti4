<?php
// change user access control for single user
function admin_user_access_uid($self,$uid) {
	if (!user_access('administer access control')) return message('error','access denied');
	$user = R::Model('user.get',$uid);
	$ret .= '<h3>Access control for '.$user->name.' ( '.$user->username.' ) only.</h3>
	<div class="help">สิทธิการใช้งานพิเศษสำหรับสมาชิก '.$user->name.' เป็นการกำหนดสิทธิพิเศษให้แก่เฉพาะสมาชิกในการเข้าถึงเว็บไซท์ ซึ่งสิทธิที่ได้รับเพิ่มเติมนี้จะนำไปรวมกับสิทธิของบทบาทเดิมที่มีอยู่แล้ว</div>';

	if ($user->_empty) return $ret.message('error','User <em>'.$uid.'</em> not exists.');

	$roles=cfg('roles');
	$db_roles_user=cfg('roles_user');
	$current_roles_user=explode(',',$db_roles_user[$user->uid]);

	if ($_POST) {
		$current_roles_user=$_POST['access'];
		$db_roles_user[$user->uid]=implode(',',$current_roles_user);
		cfg_db('roles_user',$db_roles_user);
		$ret .= message('status','บันทึกการเปลี่ยนแปลงเรียบร้อย.');
	}
	unset($roles->admin);
	$user_roles=array_merge(array('anonymous'),$user->roles);
	$perms=(array)cfg('perm');
	ksort($perms);
	$perms=(object)$perms;
	$roles_count=count((array)$roles);

	$ret .= '<form method="post" action="'.url(q()).'" >';
	$ret .= '<p><button class="btn -primary" type="submit"><i class="icon -material">done_all</i><span>Save permissions</span></button></p>';
	$ret .= '<table class="item" width="100%" cellspacing="1" cellpadding="3" border="0">';
	$ret .= '<thead><tr><th>Permission</th>';
	foreach ($roles as $role_name=>$role_perm) {
		if (!in_array($role_name,$user_roles)) continue;
		$ret .= '<th>'.$role_name.'</th>';
		$roles->$role_name=explode(',',$role_perm);
		array_walk($roles->$role_name,create_function('&$elem','$elem=trim($elem);'));
	}
	$ret .= '<th>Current user permission</th>';
	$ret .= '</tr></thead>';
	$ret .= '<tbody>';
	while (list($perm,$perm_value)=each($perms)) {
		$ret .= '<tr><th colspan="'.($roles_count+1).'">'.$perm.' module</th></tr>'._NL._NL;
		$no=0;
		$perm_lists=explode(',',$perm_value);
		array_walk($perm_lists,create_function('&$elem','$elem=trim($elem);'));
		asort($perm_lists);
		foreach ($perm_lists as $perm_item) {
			$perm_item=trim($perm_item);
			$ret .= '<tr class="'.(++$no%2?'odd':'even').'">'._NL;
			$ret .= '<td>'.$perm_item.'</td>'._NL;
			foreach ($roles as $role_name=>$role_perm) {
				if (!in_array($role_name,$user_roles)) continue;
				$ret .= '<td align="center"><input type="checkbox" value="'.$perm_item.'" '.(in_array($perm_item,$roles->$role_name)?'checked="checked"':'').' onclick="return false;" /></td>'._NL;
			}
			$ret .= '<td align="center"><input type="checkbox" name=access[] value="'.$perm_item.'" '.(in_array($perm_item,$current_roles_user)?'checked="checked"':'').' /></td>'._NL;
			$ret .= '</tr>'._NL._NL;
		}
		$ret .= '<tr height="15"><td colspan="'.($roles_count+1).'"></td></tr>'._NL;
	}
	$ret .= '</tbody>';
	$ret .= '</table>';
	$ret .= '<p><button class="btn -primary" type="submit"><i class="icon -material">done_all</i><span>Save permissions</span></button></p>';
	$ret.='</form>';
	return $ret;
}
?>