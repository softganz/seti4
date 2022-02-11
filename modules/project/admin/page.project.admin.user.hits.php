<?php
/**
* Project Admin User Hits
*
* @param Object $self
* @param Int $uid
* @return String
*/
function project_admin_user_hits($self, $uid = NULL) {
	$self->theme->title = 'User Hits';
	$q = post('q');
	$uid = SG\getFirst(post('u'),$uid);
	$roles = SG\getFirst(post('r'));
	$action = post('action');
	$order = SG\getFirst($para->order,post('o'),'hits');
	$sort = SG\getFirst($para->sort,post('s'),'DESC');


	R::View('project.toolbar',$self, 'Project Administrator','admin');
	$self->theme->sidebar = R::View('project.admin.menu', 'member');

	$items = 100;
	$page = post('page');



	$ui = new Ui();

	$roleOptions[''] = 'All Roles';
	foreach (cfg('roles') as $key => $value)
		if (!in_array($key, array('anonymous','member'))) $roleOptions[$key] = $key;

	$form = new Form(NULL, url('project/admin/user/hits'), 'search-member', 'sg-form -inlineitem');
	$form->addConfig('method', 'GET');
	$form->addField('u', array('type' => 'hidden', 'id' => 'id'));
	$form->addField('r', array('type' => 'select', 'options' => $roleOptions, 'value' => post('r') ));
	$form->addField('q',
		[
			'type' => 'text',
			'class' => 'sg-autocomplete',
			'attr' => [
				'data-query' => url('admin/get/username',array('r'=>'id')),
				'data-callback' => 'submit',
				'data-altfld' => 'id',
			],
			'placeholder' => 'Username or Name or Email',
		]
	);
	$form->addField('search', array('type' => 'button', 'value' => '<i class="icon -search -white"></i><span class="-hidden">ค้นหาสมาชิก</span></button>'));

	$ui->add($form->build());
	$ui->add('<a class="btn -floating -circle48 -fixed -at-bottom -at-right" href="'.url('project/admin/createuser').'" title="สร้างสมาชิกใหม่"><i class="icon -addbig -white"></i></a>',array('container-class'=>'-add'));

	$navbar = '<nav class="nav -page"><header class="header -hidden"><h3>User Management</h3></header>'._NL;
	$navbar .= $ui->build();
	$navbar .= '</nav><!--navbar-->'._NL;
	$self->theme->navbar = $navbar;

	if ($uid) mydb::where('u.`uid` = :uid', ':uid', $uid);
	if ($q) mydb::where('(u.`username` LIKE :q OR u.`name` LIKE :q OR u.`email` LIKE :q)',':q','%'.$q.'%');
	if (post('r')) mydb::where('u.roles LIKE :role', ':role', '%'.post('r').'%');

	mydb::value('$firstrow',$page>1 ? ($page-1)*$items : 0);
	mydb::value('$items',$items);
	mydb::value('$order',$order);
	mydb::value('$sort',$sort);

	$stmt = 'SELECT SQL_CALC_FOUND_ROWS
		  u.*
		FROM %users% AS u
		%WHERE%
		ORDER BY $order $sort
		LIMIT $firstrow , $items';

	//$ret.=print_o(mydb()->value(),'mydb()');
	$dbs = mydb::select($stmt);
	//$ret .= mydb()->_query;
	//$ret.=print_o($dbs,'$dbs');

	$totals = $dbs->_found_rows;

	$pagePara['q']=post('q');
	$pagePara['page']=$page;
	$pagePara['o']=$order;
	$pagePara['s']=$sort;
	if ($roles) $pagePara['r']=$roles;
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

	$ret .= '<div class="pagenv">'.$pagenv->show.'</div>'._NL;



	$tables = new Table();
	$tables->caption = 'รายชื่อสมาชิก';
	$tables->thead = array('name'=>'ชื่อ', 'roles', 'hits -amt' => 'Hits', 'datein -date' => 'Date In', 'lastlogin -date' => 'Last Login');

	foreach ($dbs->items as $rs) {
		if ($rs->uid==1) continue;
		$tables->rows[] = [
			'<a class="sg-action" href="'.url('project/admin/user/'.$rs->uid).'" data-rel="box" title="User Information"><img class="profile-photo" src="'.model::user_photo($rs->username).'" width="32" height="32" /><strong>'.$rs->name.'</strong></a><br />'.$rs->username.'('.$rs->uid.') '.$rs->email,
			$rs->roles,
			number_format($rs->hits),
			sg_date($rs->datein,'d-m-Y'),
			$rs->login_time ? sg_date($rs->login_time,'d-m-Y') : '',
		];
	}
	$ret = $tables->build();
	if ($dbs->_num_rows) {
		$ret .= '<div class="pagenv">'.$pagenv->show.'</div>'._NL;
		$ret.='<p>รวมทั้งสิ้น <strong>'.$totals.'</strong> รายการ</p>';
	}

	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>