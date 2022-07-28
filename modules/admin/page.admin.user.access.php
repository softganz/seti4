<?php
// change user access control
function admin_user_access($self) {
	if (!user_access('administer access control')) return message('error','access denied');

	$roles=cfg('roles');
	if (post('access')) {
		$access = post('access');
		foreach ($roles as $role_name=>$role_perm) {
			if (isset($access[$role_name])) {
				asort($access[$role_name]);
				$roles->$role_name=implode(',',$access[$role_name]);
			} else {
				$roles->$role_name='';
			}
		}
		cfg_db('roles',$roles);
		$ret .= 'บันทึกการเปลี่ยนแปลงเรียบร้อย.';
		return $ret;
	}

	$ret .= sg_client_convert('<h3>Access control</h3>
	<div class="help">สิทธิ์การใช้งานเป็นการกำหนดเพื่อสิทธิ์ให้แก่สมาชิกของแต่ละบทบาทในการเข้าถึงเว็บไซท์ สามารถกำหนดสิทธิ์ให้แต่ละบทบาทสามารถทำอะไรได้บ้าง ถ้าสมาชิกมีหลายบทบาท สิทธิ์ที่ได้รับก็จะเป็นการรวมสิทธิ์ของทุกบทบาทที่เป็นอยู่</div>');



	unset($roles->admin);
	$perms=(array)cfg('perm');
	ksort($perms);
	$perms=(object)$perms;
	$roles_count=count((array)$roles);
	$ret .= '<form class="sg-form" method="post" action="'.url(q()).'" data-rel="notify" >';


	$tables = new Table([
		'showHeader' => false,
		'thead' => ['permission -nowrap' => 'Permission'],
	]);

	foreach ($roles as $role_name=>$role_perm) {
		$tables->thead['center -'.$role_name]=$role_name;
		$roles->$role_name=explode(',',$role_perm);
		array_walk($roles->$role_name, function($elem) {return trim($elem);});
	}

	foreach($perms as $perm => $perm_value) {
		$tables->rows[] = ['<th colspan="'.($roles_count+1).'">'.$perm.' module</th>'];
		$tables->rows[]='<header>';
		$no=0;
		$perm_lists=explode(',',$perm_value);
		array_walk($perm_lists, function($elem) {return trim($elem);});
		asort($perm_lists);
		foreach ($perm_lists as $perm_item) {
			unset($row);
			$perm_item=trim($perm_item);
			$row[]=$perm_item;
			foreach ($roles as $role_name=>$role_perm) {
				$row[]='<input type="checkbox" name=access['.$role_name.'][] value="'.$perm_item.'" '.(in_array($perm_item,$roles->$role_name)?'checked="checked"':'').' />';
			}
			$tables->rows[]=$row;
		}
		$tables->rows[]=array('<td colspan="'.($roles_count+1).'" align="right"><div class="form-item"><button class="btn -primary" type="submit"><i class="icon -material">done_all</i><span>Save permissions</span></button></td>');
	}
	$ret.=$tables->build();
	$ret.='</form>';
	return $ret;
}
?>