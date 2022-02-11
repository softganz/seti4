<?php
function ibuy_admin_member($self,$uid = NULL) {
	$self->theme->title='สมาชิก';
	$self->theme->sidebar=R::Page('ibuy.admin.menu','member');
	$self->theme->title='User Management';
	$q=post('q');
	$uid=SG\getFirst(post('id'),$uid);
	$status=post('st');
	$action=post('action');

	$order=SG\getFirst($para->order,post('o'),'uid');
	$sort=SG\getFirst($para->sort,post('s'),2);
	$itemPerPage=SG\getFirst(post('i'),100);

	$isEdit=user_access('administer ibuys');
	$isAdmin=i()->admin;

	$orders = array(
						'uid'=>array('รหัสสมาชิก','u.`uid`'),
						'date'=>array('วันที่เริ่มเป็นสมาชิก','u.`datein`'),
						'name'=>array('ชื่อสมาชิก','CONVERT(u.`name` USING tis620)'),
						'amt'=>array('สั่งสินค้า','`orderTotal`'),
						'score'=>array('คะแนน','f.`score`'),
						);

	$navbar.='<nav class="nav -page"><header class="header -hidden"><h3>Member Management</h3></header>'._NL;
	$navbar.='<form id="search-member" method="get" action="'.url('ibuy/admin/member').'">'._NL;
	$navbar.='<input type="hidden" name="id" id="id" />'._NL;
	$navbar.='<ul class="ui-nav">'._NL;
	$navbar.='<li class="ui-item">เงื่อนไข ';
	$navbar.='<label></label><select class="form-select" name="st"><option value="">** ทุกระดับราคา **</option>';
	foreach (cfg('ibuy.price.use') as $key => $value) {
		if ($key == 'cost') continue;
		$navbar .= '<option value="'.$key.'"'.($key==$status?' selected="selected"':'').'>'.$value->label.'</option>';
	}
	$navbar.='</select>';
	$navbar.='</li>'._NL;
	$navbar.='<li class="ui-item"><input class="sg-autocomplete" data-query="'.url('admin/get/username',array('r'=>'id')).'" data-callback="submit" data-altfld="id" type="text" name="q" id="search-box" size="30" value="'.$q.'" placeholder="Username or Name or Email"></li>'._NL;
	$navbar.='<li class="ui-item"><button type="submit" class="btn -primary"><i class="icon -material">search</i><span>แสดง</span></button></li>'._NL;
	//$navbar.='<li class="navbar--add"><a href="'.url('paper/post/ibuy').'" class="floating circle32" title="เพิ่มสินค้าใหม่">+</a></li>'._NL;
	$navbar.='</ul><br />'._NL;
	$navbar.='เรียงลำดับ <select class="form-select" name="o">';
	foreach ($orders as $key=>$item) $navbar.='<option value="'.$key.'" '.($key==$order?' selected="selected"':'').'>'.$item[0].'</option>';
	$navbar.='</select> '._NL;
	$navbar.='<input type="radio" name="s" value="1"'.($sort==1?' checked="checked"':'').' /> น้อยไปมาก <input type="radio" name="s" value="2"'.($sort!=1?' checked="checked"':'').' /> มากไปน้อย '._NL;
	$navbar.='<label>จำนวน</label><select class="form-select" name="i"><option value="-1">** ทั้งหมด **</option>';
	for($i=100;$i<=1000;$i=$i+100) $navbar.='<option value="'.$i.'"'.($i==$itemPerPage?' selected="selected"':'').'>'.$i.' รายการ</option>';
	$navbar.='</select>'._NL;
	$navbar.='</form>'._NL;
	$navbar.='</nav><!--navbar-->'._NL;

	$self->theme->navbar = $navbar;



	if ($uid) {
		if (post('action')=='edit') {
			return __ibuy_admin_user_edit($uid);
		} else if (post('action')=='addorg' && post('orgid')) {
			$stmt='INSERT INTO %org_officer%
									(`orgid`,`uid`,`membership`)
								VALUES
									(:orgid,:uid,"officer")
								ON DUPLICATE KEY UPDATE `orgid`=:orgid';
			mydb::query($stmt,':orgid',post('orgid'),':uid',$uid);
		} else if ($action=='block') {
				$rs = R::Model('user.get',$uid);
				$status=$rs->status=='block'?'enable':'block';
				// Delete cache when block or roles change
				mydb::query('UPDATE %users% SET `status`=:status WHERE `uid`=:uid LIMIT 1',':uid',$uid, ':status',$status);
				mydb::query('DELETE FROM %cache% WHERE `headers`=:username',':username',$rs->username);
				$ret.=$status=='block'?'Blocked':'Active';
				return $ret;
		}
		$ret.=__ibuy_admin_user_info($uid);
	} else {
		mydb::where('u.`uid` > 1');
		if ($u) mydb::where('u.`username` = :username',':username',$_REQUEST['u']);
		if ($status) mydb::where('f.`custtype` = :status', ':status',$status);
		if ($q) mydb::where('(u.`username` LIKE :q OR u.`name` LIKE :q OR u.`email` LIKE :q OR f.`custname` LIKE :q)',':q','%'.$q.'%');
		if (post('r')) mydb::where('u.`roles` = :role',':role',post('r'));

		$page = post('page');

		if ($itemPerPage == -1) {
			mydb::value('$LIMIT$', '');
		} else {
			$firstRow = $page > 1 ? ($page-1)*$itemPerPage : 0;
			mydb::value('$LIMIT$', 'LIMIT '.$firstRow.' , '.$itemPerPage);
		}

		mydb::value('$ORDER$', 'ORDER BY '.$orders[$order][1].' '.($sort==1?'ASC':'DESC'));
		$stmt = 'SELECT SQL_CALC_FOUND_ROWS
								f.*
								, u.`uid`, u.`username`, u.`name`, u.`datein`
								, COUNT(`oid`) orderTotal
							FROM %users% AS u
								LEFT JOIN %ibuy_customer% f USING(`uid`)
								LEFT JOIN %ibuy_order% o USING(`uid`)
							%WHERE%
							GROUP BY u.`uid`
							$ORDER$
							$LIMIT$';

		$dbs = mydb::select($stmt,$where['value']);
		//$ret .= $dbs->_query;

		$totals = $dbs->_found_rows;

		$pagePara['q']=post('q');
		$pagePara['st']=$status;
		$pagePara['o']=$order;
		$pagePara['s']=$sort;
		$pagePara['i']=$itemPerPage;
		$pagePara['page']=$page;
		$pagenv = new PageNavigator($itemPerPage,$page,$totals,q(),false,$pagePara);
		$no=$pagenv?$pagenv->FirstItem():0;

		$text[]='สมาชิก';
		if ($q) $text[]='ที่มีคำว่า "'.$q.'"';
		$text[]='('.($totals?'จำนวน '.$totals.' รายการ' : 'ไม่มีรายการ').')';
		if ($text) $self->theme->title=implode(' ',$text);

		if ($dbs->_empty) {
			$ret.=message('error','ไม่มีข้อมูลตามเงื่อนไขที่ระบุ');
		} else {
			$ret .= '<div class="pagenv">'.$pagenv->show.'</div>'._NL;
		}

		$tables = new Table();
		$tables->addClass('ibuy-member-list sg-inline-edit');
		$tables->caption='รายชื่อสมาชิก';
		$tables->thead=array('name'=>'ชื่อ','amt'=>'ระดับราคา','shop'=>'ร้าน / ที่อยู่','โทรศัพท์','amt order'=>'สั่งสินค้า', 'amt score'=>'คะแนน'	, 'date'=>'วันที่สมัคร');
		$inlinePara['data-update-url']=url('ibuy/admin/update');
		if (debug('inline')) $inlinePara['data-debug'] = 'yes';
		$tables->attr=$inlinePara;


		$memberLevelList = array(''=>'ไม่กำหนด');
		foreach (cfg('ibuy.price.use') as $key => $value) {
			if ($key == 'cost') continue;
			$memberLevelList[$key] = $value->label;
		}

		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(
											'<a class="sg-action" href="'.url('ibuy/admin/member',array('id'=>$rs->uid)).'" data-rel="box" title="User Information"><img class="profile" src="'.model::user_photo($rs->username).'" width="48" height="48" /><br /><strong>'.$rs->name.'</strong></a><br />'.$rs->username.'('.$rs->uid.')<br />'.$rs->email,
											view::inlineedit(
												array(
												'group' => 'franchise',
												'fld' => 'custtype',
												'tr' => $rs->uid
												),
												$memberLevelList[$rs->custtype],
												$isEdit,
												'select',
												$memberLevelList
											),
											'<a href="'.url('ibuy/franchise/'.$rs->username).'"><strong>'.$rs->custname.'</strong></a><br />'.$rs->custaddress,
											$rs->custphone,
											$rs->orderTotal,
											number_format($rs->score),
											$rs->datein?sg_date($rs->datein,sg_date($rs->datein,'Y-m-d')==date('Y-m-d')?'G:i':'d-m-Y G:i'):'',
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

	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}

function __ibuy_admin_user_info($uid) {
	$stmt='SELECT * FROM %users% WHERE `uid`=:uid LIMIT 1';
	$rs=mydb::select($stmt,':uid',$uid);
	$self->theme->title=$rs->name;

	$ret .= '<header class="header -box"><nav class="nav -back"><a class="sg-action" data-rel="back" href="javascript:void(0)"><i class="icon -material">arrow_back</i></a></nav><h3>'.$rs->name.'</h3></header>';

	$ret.='<img class="profile ibuy__admin__member--photo" src="'.model::user_photo($rs->username).'" width="180" height="180" />';

	$tables = new Table();
	$tables->rows[]=array('ID',$rs->uid);

	$tables->rows[]=array('Username','<strong>'.$rs->username.'</strong>'.(i()->admin?' | <a class="sg-action" href="'.url('ibuy/admin/member/'.$uid,array('action'=>'edit')).'" data-rel="box">Edit</a> | <a href="'.url('admin/user/logas/name/'.$rs->username).'">Log as</a> | <a href="'.url('ibuy/admin/member/'.$uid,array('action'=>'block')).'" class="sg-action" data-rel="this" title="Click to '.($rs->status=='block'?'Active':'Block').'">'.($rs->status=='block'?'Blocked':'Active').'</a>':''));

	$tables->rows[]=array('Name',$rs->name);
	$tables->rows[]=array('E-Mail',$rs->email);
	$tables->rows[]=array('Phone',$rs->phone);
	$tables->rows[]=array('กลุ่มสมาชิก',$rs->roles);
	if ($rs->admin_remark) $tables->rows[]=array('หมายเหตุ',nl2br($rs->admin_remark));

	$shop=mydb::select('SELECT f.* , u.username , u.datein , p.name province FROM %ibuy_customer% f LEFT JOIN %users% u ON u.uid=f.uid LEFT JOIN %province% p ON p.pid=f.pid WHERE f.`uid`=:uid LIMIT 1',':uid',$uid);
	$self->theme->title=$shop->custname.' - '.($shop->custtype=='franchise'?'เฟรนไชส์':'ตัวแทนจำหน่าย');

	//$ret.=$this->__menu('detail',$shop->uid,$shop->username);

	$tables->caption='รายละเอียดร้าน '.$shop->custname;
	$tables->rows[]=array('ชื่อร้าน',$shop->custname);
	$tables->rows[]=array('ระดับราคา',$shop->custtype);
	$tables->rows[]=array('ที่อยู่',$shop->custaddress.' '.$shop->custzip);
	$tables->rows[]=array('โทรศัพท์',user_access('access user profiles') ? $shop->custphone : '**');
	$tables->rows[]=array('เมื่อวันที่',$shop->datein);
	// private information
	if (user_access('administer ibuys','access ibuys',$shop->uid)) {
		$tables->rows[]='<tr><th colspan="2">ข้อมูลส่วนบุคคล</th></tr>';
		$tables->rows[]=array('ชื่อผู้ติดต่อ',$shop->custattn);
		$tables->rows[]=array('ส่วนลดที่ใช้งานได้',number_format($shop->discount,2).' บาท');
		$tables->rows[]=array('ส่วนลดที่ยังไม่สามารถใช้งานได้',number_format($shop->discount_hold<0?0:$shop->discount_hold,2).' บาท ( สถานะ : '.($shop->discount_hold>=0?'ระงับ':'ใช้ได้').' )');
		$tables->rows[]=array('ขนส่งสินค้าโดย',$shop->shippingby);
	}

	$ret .= $tables->build();

	//$ret.='<div class="widget " widget-request="ibuy/franchise/'.$rs->username.'" data-option-replace="yes"></div>';

	//$ret.=R::Page('ibuy.admin.report.order');

	//$ret.='<div class="widget " widget-request="ibuy/admin/report/order?uid='.$uid.'" data-option-replace="yes"></div>';

	$stmt = 'SELECT
						o.* , u.`username`, u.`name` , f.`custname`,f.`custtype`
						FROM %ibuy_order% o
							LEFT JOIN %ibuy_customer% f ON f.`uid`=o.`uid`
							LEFT JOIN %users% u ON u.`uid`=f.`uid`
						WHERE o.`uid`=:uid
						GROUP BY `oid`
						ORDER BY `oid` DESC';
	$dbs= mydb::select($stmt,':uid',$rs->uid);

	if ($dbs->_num_rows) {
		$tables = new Table();
		$tables->header=array('Order no','date'=>'Date','Franchise shop','T','money total'=>'Total','money balance'=>'ค้างชำระ','Action','status'=>'Status','');
		foreach ($dbs->items as $rs) {
			$status=ibuy_define::status_text($rs->status);
			$tables->rows[]=array($rs->orderno,
												date('d-m-Y H:i',$rs->orderdate),
												SG\getFirst($rs->custname,'ไม่ระบุ'),
												$rs->custtype,
												number_format($rs->total,2),
												$rs->balance?number_format($rs->balance,2):'-',
												$rs->emscode.($rs->emsdate?'<br />('.sg_date($rs->emsdate,'ว ดด ปป').')':''),
												$status,
												'<a href="'.url('ibuy/report/order/'.$rs->oid).'" title="ดูรายละเอียดใบสั่งสินค้าหมายเลข '.$rs->oid.'">รายละเอียด</a>',
												'config'=>array('class'=>'status-'.$rs->status)
											);
			if ($rs->remark) $tables->rows[]='<tr><td colspan="2"></td><td colspan="7">หมายเหตุ : '.$rs->remark.'</td></tr>';
		}

		$ret .= $tables->build();
	}
	return $ret;
}

function __ibuy_admin_user_edit($uid) {
	$rs = R::Model('user.get',$uid);

	if ($rs->_empty) return message('error','User <em>'.$para->info.'</em> not exists.');

	$self->theme->title=$rs->name.' : Edit User Information';

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

			db_querytable(db_create_update_cmd('%users%',(array)$profile,' uid='.$rs->uid.' LIMIT 1'));

			location('ibuy/admin/member/'.$uid);
			return $ret;
		}
	} else {
		$profile=(object)$rs;
	}

	$ret .='<h2>Account information</h2>'._NL;
	$form = new Form('profile', url(q()), 'edit-account', 'sg-form');
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
	$form->button->items->text=' <a class="sg-action" href="'.url('ibuy/admin/member/'.$uid).'" data-rel="box">Cancel</a> Or <a href="#" onclick="go(\'Are you sure to delete this user?. Please confirm?\',\''.url('admin/user/delete/member/'.$para->info.'/confirm/yes').'\');return false;">Remove this user</a>';

	$ret .= $form->build();

	return $ret;
}
?>