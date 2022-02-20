<?php
/**
* Project Admin User
*
* @param Object $self
* @param Int $uid
* @return String
*/
function project_admin_user($self, $uid = NULL) {
	$self->theme->title = 'User Management';
	$q = post('q');
	$uid = SG\getFirst(post('u'),$uid);
	$action = post('action');
	$order = SG\getFirst($para->order,post('o'),'CONVERT(u.`name` USING tis620)');
	$sort = SG\getFirst($para->sort,post('s'),'ASC');


	R::View('project.toolbar',$self,'Project Administrator','admin');
	$self->theme->sidebar = R::View('project.admin.menu','member');


	$navBar = '<nav class="nav -page"><header class="header -hidden"><h3>User Management</h3></header>'._NL;
	$navBar .= '<form id="search-member" class="search-box sg-form" method="get" action="'.url('project/admin/user').'" role="search">'
		.'<input type="hidden" name="u" id="id" />'
		.'<input class="sg-autocomplete form-text -fill" data-query="'.url('admin/get/username',array('r'=>'id')).'" data-callback="submit" data-altfld="id" type="text" name="q" id="search-box" size="30" value="'.$q.'" placeholder="Username or Name or Email" />'
		.'<button class="btn" type="submit"><i class="icon -search"></i><span class="-hidden">ค้นหาสมาชิก</span></button>'
		.'</form>';
	$navBar .= '</nav>'._NL;
	$self->theme->navbar = $navBar;


	if ($uid) {
		if (post('action') == 'edit') {
			return __project_admin_user_edit($uid);
		} else if (post('action') == 'addorg' && post('orgid')) {
			$stmt = 'INSERT INTO %org_officer%
					(`orgid`,`uid`,`membership`)
				VALUES
					(:orgid,:uid,:membership)
				ON DUPLICATE KEY UPDATE `orgid` = :orgid';
			mydb::query($stmt,':orgid',post('orgid'),':uid',$uid,':membership',post('membership'));
		} else if (post('action') == 'deleteorg' && post('orgid')) {
			mydb::query('DELETE FROM %org_officer% WHERE `orgid`= :orgid AND `uid` = :uid LIMIT 1',':orgid',post('orgid'),':uid',$uid);
			//$ret.=mydb()->_query;
		} else if (post('action') == 'changerole' && post('role')) {
			mydb::query('UPDATE %users% SET `roles` = :roles WHERE `uid` = :uid LIMIT 1',':uid',$uid,':roles',post('role'));
			//$ret.=mydb()->_query;
		} else if ($action == 'block') {
				$rs = R::Model('user.get',$uid);
				$status = $rs->status == 'block' ? 'enable' : 'block';
				// Delete cache when block or roles change
				mydb::query('UPDATE %users% SET `status` = :status WHERE `uid` = :uid LIMIT 1',':uid',$uid, ':status',$status);
				mydb::query('DELETE FROM %cache% WHERE `headers` = :username',':username',$rs->username);
				$ret .= $status == 'block' ? 'Blocked' : 'Active';
				return $ret;
		}
		$ret .= __project_admin_user_info($uid);
	} else {
		$items = 100;
		$page = post('page');

		if ($uid) mydb::where('u.`username` = :username',':username',$uid);
		if ($q) mydb::where('(u.`username` LIKE :q OR u.`name` LIKE :q OR u.`email` LIKE :q)',':q','%'.$q.'%');
		if (post('r')) mydb::where('u.roles = :role',':role',post('r'));

		mydb::value('$firstrow',$page>1 ? ($page-1)*$items : 0);
		mydb::value('$items',$items);
		mydb::value('$order',$order);
		mydb::value('$sort',$sort);

		$stmt = 'SELECT SQL_CALC_FOUND_ROWS
			  u.*
			, (SELECT GROUP_CONCAT(org.`name` SEPARATOR "<br />") FROM %org_officer% o LEFT JOIN %db_org% org USING(`orgid`) WHERE o.`uid` = u.`uid`) `orgName`
			, (SELECT COUNT(*) FROM %topic% t WHERE t.`type` = "project" AND t.`uid` = u.`uid`) `projects`
			, (SELECT COUNT(*) FROM %topic% d WHERE d.`type` = "project-develop" AND d.`uid` = u.`uid`) `develops`
			FROM %users% AS u
			%WHERE%
			ORDER BY $order $sort
			LIMIT $firstrow , $items';

		//$ret.=print_o(mydb()->value(),'mydb()');
		$dbs = mydb::select($stmt);
		//$ret.=mydb()->_query;
		//$ret.=print_o($dbs,'$dbs');

		$totals = $dbs->_found_rows;

		$pagePara['q']=post('q');
		$pagePara['page']=$page;
		$pagePara['o']=$order;
		$pagePara['s']=$sort;
		$pagenv = new PageNavigator($items,$page,$totals,q(),false,$pagePara);
		$no=$pagenv?$pagenv->FirstItem():0;

		//$ret.='First item='.$pagenv->FirstItem();
		//$sql_cmd .= '  LIMIT '.$pagenv->FirstItem().','.$items;

		//$ret.='Total = '.$totals;

		//$ret.=print_o($dbs,'$dbs');

		$text[]='สมาชิก';
		if ($q) $text[]='ที่มีคำว่า "'.$q.'"';
		$text[]='('.($totals?'จำนวน '.$totals.' รายการ' : 'ไม่มีรายการ').')';
		if ($text) $self->theme->title=implode(' ',$text);

		if ($para->order=="year") $dbs->_group='pryear';
		else if ($para->order=='projectset') $dbs->_group='projectset_name';
		//		if (i()->username=='softganz') $ret.=print_o($dbs,'$dbs');
		if ($dbs->_empty) {
			$ret .= message('error','ไม่มีรายชื่อสมาชิกตามเงื่อนไขที่ระบุ');
		} else {
			$ret .= '<div class="pagenv">'.$pagenv->show.'</div>'._NL;

			$tables = new Table();
			$tables->addClass('user-list');
			$tables->caption='รายชื่อสมาชิก';
			$tables->thead=array('name'=>'ชื่อ','amt'=>'โครงการ/พัฒนา','zone'=>'หน่วยงาน','กลุ่มสมาชิก','created -date'=>'วันที่สมัคร');

			foreach ($dbs->items as $rs) {
				if ($rs->uid==1) continue;

				$tables->rows[]=array(
					'<a class="sg-action" href="'.url('project/admin/user/'.$rs->uid).'" data-rel="box" data-width="640" title="User Information"><img class="profile" src="'.model::user_photo($rs->username).'" width="48" height="48" /><strong>'.$rs->name.'</strong></a><br />'.$rs->username.'('.$rs->uid.')<br />'.$rs->email,
					($rs->projects?$rs->projects:'-').'/'.($rs->develops?$rs->develops:'-'),
					$rs->orgName,
					$rs->roles,
					sg_date($rs->datein,'d-m-Y G:i'),
					'config'=>array('class'=>'user-'.$rs->status,'title'=>'User was '.$rs->status)
				);

				if ($rs->admin_remark) $tables->rows[]=array('','<td colspan="3"><p><font color="#f60">Admin remark : '.$rs->admin_remark.'</font></p></td>');
			}
			$ret .= $tables->build();
			if ($dbs->_num_rows) {
				$ret .= '<div class="pagenv">'.$pagenv->show.'</div>'._NL;
				$ret.='<p>รวมทั้งสิ้น <strong>'.$totals.'</strong> รายการ</p>';
			}
		}

		$ret .= R::View('button.floating',url('project/admin/createuser'));

	}

	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}

function __project_admin_user_info($uid) {
	$stmt='SELECT * FROM %users% WHERE `uid`=:uid LIMIT 1';
	$rs=mydb::select($stmt,':uid',$uid);
	$isAdmin=user_access('access administrator pages');

	$ui = new Ui();
	if ($isAdmin && $rs->uid > 1) {
		$ui->add('<a class="sg-action btn -link" href="'.url('admin/user/edit/'.$uid,array('action'=>'edit')).'" data-rel="box">Edit</a>');
		$ui->add('<a class=" btn -link" href="'.url('admin/user/logas/name/'.$rs->username).'">Log as</a>');
		$ui->add('<a class=" sg-action btn -link" href="'.url('project/admin/user/'.$uid,array('action'=>'block')).'" data-rel="this" title="Click to '.($rs->status=='block'?'Active':'Block').'">'.($rs->status=='block'?'Blocked':'Active').'</a>');
	}

	$ret .= '<header class="header -box"><nav class="nav -back"><a class="sg-action" href="javascript:void(0)" data-rel="back"><i class="icon -material">arrow_back</i></a></nav><h3>'.$rs->name.'</h3><nav class="nav">'.$ui->build().'</nav></header>';

	$ret .= '<img class="profile" src="'.model::user_photo($rs->username).'" width="180" height="180" style="position:absolute;right:20px;border-radius:180px;" />';

	$tables = new Table();

	$tables->rows[] = array(
		'Username',
		'<strong>'.$rs->username.'</strong>'.' ('.$rs->uid.')'
	);

	$tables->rows[]=array('Name',$rs->name);
	$tables->rows[]=array('E-Mail',$rs->email);
	$tables->rows[]=array('Phone',$rs->phone);
	$tables->rows[]=array('กลุ่มสมาชิก',$rs->roles);
	if ($rs->admin_remark) $tables->rows[]=array('หมายเหตุ',nl2br($rs->admin_remark));

	$ret .= $tables->build();



	$stmt='SELECT * FROM %org_officer% o LEFT JOIN %db_org% org USING(`orgid`) WHERE o.`uid`=:uid ORDER BY CONVERT(`name` USING tis620) ASC';
	$dbs=mydb::select($stmt,':uid',$uid);

	$tables = new Table();
	$tables->thead=array('สังกัดสำนัก/หน่วยงาน/องค์กร', 'type -center' => 'กลุ่มสมาชิก','icons -center'=>'');

	$tables->rows[]=array(
		'<input type="text" name="orgname" class="sg-autocomplete form-text -fill" data-query="'.url('org/api/org').'" data-altfld="orgid" size="40" placeholder="ป้อนชื่อหน่วยงาน" data-select="label" />',
		'<select name="membership" class="form-select -fill"><option value="ADMIN">Admin</option><option value="OFFICER">Officer</option><option value="TRAINER" selected="selected">Trainer</option><option value="MEMBER">Regular Member</option></select>',
		'<button class="btn"><i class="icon -add"></i><span>เพิ่มสังกัดหน่วยงาน</span></button>'
	);

	foreach ($dbs->items as $item) {
		$tables->rows[]=array(
			'<a class="sg-action" href="'.url('project/admin/org/'.$item->orgid).'" data-rel="box">'.$item->name.'</a>',
			$item->membership,
			'<a href="'.url('project/admin/user/'.$rs->uid,array('action'=>'deleteorg','orgid'=>$item->orgid)).'" class="sg-action" data-rel="this" data-removeparent="tr" data-confirm="ต้องการลบสังกัดหน่วยงานนี้ กรุณายืนยัน?"><i class="icon -cancel"></i></a>'
		);
	}
	$ret.='<form class="sg-form" action="'.url('project/admin/user').'" data-rel="box"><input type="hidden" name="action" value="addorg" /><input type="hidden" name="u" value="'.$uid.'" /><input type="hidden" name="orgid" id="orgid" value="" />';


	$ret .= $tables->build();

	$ret.='</form>';



	// รายชื่อโครงการในความรับผิดชอบ
	$stmt = 'SELECT DISTINCT p.`tpid`,t.`title`,p.`pryear`,p.`budget`,p.`project_status`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %topic_user% tu USING(`tpid`)
		WHERE t.`uid`=:uid OR tu.`uid`=:uid
		ORDER BY p.`pryear` DESC, p.`tpid` DESC';

	$dbs = mydb::select($stmt,':uid',$uid);

	if ($dbs->_num_rows) {
		$ret.='<h3>โครงการติดตาม</h3>';

		$tables = new Table();
		$tables->thead=array('no'=>'','ชื่อโครงการ','ปี','money'=>'งบประมาณ','สถานะโครงการ');
		foreach ($dbs->items as $item) {
			$tables->rows[]=array(++$no,'<a href="'.url('paper/'.$item->tpid).'">'.SG\getFirst($item->title,'ไม่ระบุ').'</a>',$item->pryear+543,number_format($item->budget,2),$item->project_status);
		}
		$ret .= $tables->build();
	}

	$stmt='SELECT * FROM %topic% WHERE `type`="project-develop" AND `uid`=:uid';
	$dbs=mydb::select($stmt,':uid',$uid);
	if ($dbs->_num_rows) {
		$tables = new Table();
		$ret.='<h3>พัฒนาโครงการ</h3>';
		$tables->thead=array('no'=>'','ชื่อโครงการ','ปี','สถานะโครงการ');
		foreach ($dbs->items as $item) {
			$tables->rows[]=array(++$no,'<a href="'.url('project/proposal/'.$item->tpid).'">'.SG\getFirst($item->title,'ไม่ระบุ').'</a>',sg_date($item->created,'ปปปป'),$item->status);
		}
		$ret .= $tables->build();
	}
	return $ret;
}

function __project_admin_user_edit($uid) {
	$rs = R::Model('user.get',$uid);

	if ($rs->_empty) return message('error','User <em>'.$para->info.'</em> not exists.');

	$profile=(object)post('profile',_TRIM);

	if ($profile->username) {
		if ($rs->username != $profile->username) {
			$isDupUsername=mydb::select('SELECT `username` FROM %users% WHERE `username`=:username LIMIT 1',':username',$profile->username)->username;
			if ($isDupUsername) $error[]='ชื่อสมาชิก (Username) <strong>'.$profile->username.'</strong> มีผู้อื่นใช้แล้ว กรุณาเลือกชื่อใหม่';
		}
		//if (empty($profile->email)) $error[]='กรุณาป้อนอีเมล์';
		if ($profile->email && !sg_is_email($profile->email)) $error[]='กรุณาป้อนอีเมล์ให้ถูกต้องตามรูปแบบ คือ yourname@domain.com';

		if ($profile->password) {
			if (strlen($profile->password)<6) $error[]='รหัสผ่านใหม่ต้องตัวอักษรอย่างน้อย 6 อักษร'; //-- password length
			if ($profile->password != $profile->repassword) $error[]='การป้อนรหัสผ่านใหม่ทั้งสองครั้งไม่ตรงกัน'; //-- password <> retype
		}

		if ($error) {
			$message=message('error',$error);
		} else {
			if ($profile->password) {
				$profile->password=sg_encrypt($profile->password,cfg('encrypt_key'));
				unset($profile->repassword);
			} else {
				unset($profile->password,$profile->repassword);
			}
			$profile->roles=implode(',',$profile->roles);
			$oldRoles=mydb::select('SELECT `roles` FROM %users% WHERE `uid`=:uid LIMIT 1',':uid',$rs->uid)->roles;

			// Delete cache when block or roles change
			if ($profile->status=='block' || $profile->roles!=$oldRoles) {
				mydb::query('DELETE FROM %cache% WHERE `headers`=:username',':username',$profile->username);
			}

			//unset($profile->username);

			$stmt = mydb::create_update_cmd('%users%',(array)$profile,' uid='.$rs->uid.' LIMIT 1');
			mydb::query($stmt);
			//$ret .= mydb()->_query;

			location('project/admin/user/'.$uid);
			return $ret;
		}
	} else {
		$profile=(object)$rs;
	}


	$ret .= '<header class="header -box"><nav class="nav -back -hidden"><a class="sg-action" href="javascript:void(0)" data-rel="back"><i class="icon -material">arrow_back</i></a></nav><h3>Account information</h3></header>';


	$form = new Form('profile', url(q()), 'edit-account', 'xsg-form');
	$form->config->attr=array('data-rel'=>'box',);

	$form->action=array('name'=>'action','type'=>'hidden','value'=>'edit');

	if ($message) {
		$form->message->type='textfield';
		$form->message->value=$message;
	}

	$form->username->type='text';
	$form->username->label='Username :';
	$form->username->maxlength=30;
	$form->username->size=20;
	$form->username->require=true;
	//$form->username->readonly=true;
	$form->username->value=htmlspecialchars($profile->username);
	$form->username->description='Your preferred username; punctuation is not allowed except for periods, hyphens, and underscores.';

	$form->email->type='text';
	$form->email->label='E-mail address :';
	$form->email->maxlength=50;
	$form->email->size=60;
	$form->email->require=true;
	$form->email->value=htmlspecialchars($profile->email);
	$form->email->description='A valid e-mail address. All e-mails from the system will be sent to this address. The e-mail address is not made public and will only be used if you wish to receive a new password or wish to receive certain news or notifications by e-mail.';

	$form->password->type='password';
	$form->password->label='Password :';
	$form->password->maxlength=20;
	$form->password->size=20;

	$form->repassword->type='password';
	$form->repassword->label='Confirm password :';
	$form->repassword->maxlength=20;
	$form->repassword->size=20;
	$form->repassword->description='To change the current user password, enter the new password in both fields.';

	$form->status->type='radio';
	$form->status->label='Status :';
	if ($profile->status=='disable') $form->status->options['disable']='Disabled';
	$form->status->options['block']='Blocked';
	$form->status->options['enable']='Active';
	$form->status->value=$profile->status;
	$form->status->description='To change the user status.';

	$roles=cfg('roles');
	unset($roles->member,$roles->anonymous);
	$form->roles->type='checkbox';
	$form->roles->label='Roles :';
	foreach (array_keys((array)$roles) as $role) {
		if ($role=='admin' && !(in_array('admin',i()->roles) || i()->uid==1)) continue;
		$form->roles->options[$role]=$role;
	}
	$form->roles->value=$profile->roles;
	$form->roles->multiple=true;
	$form->roles->description='The user receives the combined permissions of the <em>authenticated user</em> role, and all roles selected here. For <a href="'.url('admin/user/access/uid/'.$rs->uid).'">additional authenticated for this user only</a>.';

	$form->admin_remark->type='textarea';
	$form->admin_remark->label='Admin Remark :';
	$form->admin_remark->rows=2;
	$form->admin_remark->value=htmlspecialchars($profile->admin_remark);

	$form->button->type='submit';
	$form->button->items->save='Save';
	$form->button->items->text=' <a class="sg-action" href="'.url('project/admin/user/'.$uid).'" data-rel="box">Cancel</a> Or <a href="#" onclick="go(\'Are you sure to delete this user?. Please confirm?\',\''.url('admin/user/delete/member/'.$para->info.'/confirm/yes').'\');return false;">Remove this user</a>';

	$ret .= $form->build();

	return $ret;
}
?>