<?php
/**
* Admin   :: Set User Access For Some User Only
* Created :: 2016-11-08
* Modify  :: 2024-08-19
* Version :: 3
*
* @param Int $userId
* @return Widget
*
* @usage admin/user/access/uid/{userId}
*/

class AdminUserAccessUid extends Page {
	var $userId;
	var $userInfo;

	function __construct($userId = NULL) {
		parent::__construct([
			'userId' => $userId,
			'userInfo' => UserModel::get($userId)
		]);
		// debugMsg($this, '$this');
	}

	function build() {
		if (!user_access('administer access control')) return message('error','access denied');

		$ret .= '<h3>Access control for '.$this->userInfo->name.' ( '.$this->userInfo->username.' ) only.</h3>
		<div class="help">สิทธิการใช้งานพิเศษสำหรับสมาชิก '.$this->userInfo->name.' เป็นการกำหนดสิทธิพิเศษให้แก่เฉพาะสมาชิกในการเข้าถึงเว็บไซท์ ซึ่งสิทธิที่ได้รับเพิ่มเติมนี้จะนำไปรวมกับสิทธิของบทบาทเดิมที่มีอยู่แล้ว</div>';

		if ($this->userInfo->_empty) return $ret.message('error','User <em>'.$this->userId.'</em> not exists.');

		$roles = cfg('roles');
		$db_roles_user = cfg('roles_user');
		// debugMsg(post(),'post()');
		// debugMsg($db_roles_user, '$db_roles_user');
		$current_roles_user = explode(',', $db_roles_user->{$this->userInfo->username});

		if (post('save')) {
			$newAccess = post('access');
			if ($newAccess) {
				$db_roles_user->{$this->userInfo->username} = implode(',',$newAccess);
			} else {
				unset($db_roles_user->{$this->userInfo->username});
			}
			cfg_db('roles_user', json_encode($db_roles_user));
			return success('บันทึกการเปลี่ยนแปลงเรียบร้อย.');
		}

		unset($roles->admin);
		$user_roles = array_merge(array('anonymous'), $this->userInfo->roles);
		$perms=(array)cfg('perm');
		ksort($perms);
		$perms=(object)$perms;
		$roles_count=count((array)$roles);

		$ret .= '<form class="sg-form" method="post" action="'.url(q()).'" data-rel="notify"><input type="hidden" name="save" value="save" />';
		$ret .= '<p class="-sg-text-right"><button class="btn -primary" type="submit" name="save"><i class="icon -material">done_all</i><span>Save permissions</span></button></p>';
		$ret .= '<table class="item" width="100%" cellspacing="1" cellpadding="3" border="0">';
		$ret .= '<thead><tr><th>Permission</th>';
		foreach ($roles as $role_name=>$role_perm) {
			if (!in_array($role_name,$user_roles)) continue;
			$ret .= '<th>'.$role_name.'</th>';
			$roles->$role_name=explode(',',$role_perm);
			array_walk($roles->$role_name, function(&$elem) {$elem = trim($elem);});
		}
		$ret .= '<th>Current user permission</th>';
		$ret .= '</tr></thead>';
		$ret .= '<tbody>';
		foreach ($perms as $perm => $perm_value) {
		// while (list($perm,$perm_value) = each((Array) $perms)) {
			$ret .= '<tr><th colspan="'.($roles_count+1).'">'.$perm.' module</th></tr>'._NL._NL;
			$no=0;
			$perm_lists=explode(',',$perm_value);
			array_walk($perm_lists, function(&$elem) {$elem = trim($elem);});
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
		$ret .= '<p class="-sg-text-right"><button class="btn -primary" type="submit" name="save"><i class="icon -material">done_all</i><span>Save permissions</span></button></p>';
		$ret.='</form>';

		return new Scaffold([
			'appBar' => new AdminAppBarWidget([
				'title' => 'User Access of '.$this->userInfo->name.' ( '.$this->userInfo->username.' ) only'
			]), // AdminAppBarWidget
			'body' => new Widget([
				'children' => [$ret], // children
			]), // Widget
		]);
	}
}
?>