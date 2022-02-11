<?php
function flood_admin_member($self,$uid=NULL) {
	$self->theme->title='สมาชิก';
	$self->theme->sidebar=R::Page('flood.admin.menu','member');
	$self->theme->title='User Management';
	$q=post('q');
	$uid=SG\getFirst(post('id'),$uid);
	$status=post('st');
	$action=post('action');

	$order=SG\getFirst($para->order,post('o'),'uid');
	$sort=SG\getFirst($para->sort,post('s'),2);
	$itemPerPage=SG\getFirst(post('i'),100);

	$orders = [
		'uid'=>array('รหัสสมาชิก','u.`uid`'),
		'date'=>array('วันที่เริ่มเป็นสมาชิก','u.`datein`'),
		'name'=>array('ชื่อสมาชิก','CONVERT(u.`name` USING tis620)'),
	];

	$navbar.='<nav class="nav -page"><header class="header -hidden"><h3>Member Management</h3></header>'._NL;
	$navbar.='<form id="search-member" method="get" action="'.url('flood/admin/member').'">'._NL;
	$navbar.='<input type="hidden" name="id" id="id" />'._NL;
	$navbar.='<ul class="ui-nav">'._NL;
	$navbar.='<li class="ui-item">เงื่อนไข ';
	$navbar.='</li>'._NL;
	$navbar.='<li class="ui-item">ค้นหาสมาชิก <input id="search-box" class="sg-autocomplete form-text" data-query="'.url('admin/get/username',array('r'=>'id')).'" data-callback="submit" data-altfld="id" type="text" name="q" size="30" value="'.$q.'" placeholder="Username or Name or Email"></li>'._NL;
	$navbar.=' <button type="submit" class="btn"><i class="icon -search"></i></button>'._NL;
	//$navbar.='<li class="navbar--add"><a href="'.url('paper/post/ibuy').'" class="floating circle32" title="เพิ่มสินค้าใหม่">+</a></li>'._NL;
	$navbar.='</ul>'._NL;
	$navbar.='<br />เรียงลำดับ <select class="form-select" name="o">';
	foreach ($orders as $key=>$item) $navbar.='<option value="'.$key.'" '.($key==$order?' selected="selected"':'').'>'.$item[0].'</option>';
	$navbar.='</select> '._NL;
	$navbar.='<input type="radio" name="s" value="1"'.($sort==1?' checked="checked"':'').' /> น้อยไปมาก <input type="radio" name="s" value="2"'.($sort!=1?' checked="checked"':'').' /> มากไปน้อย '._NL;
	$navbar.='<label>จำนวน</label><select class="form-select" name="i"><option value="-1">** ทั้งหมด **</option>';
	for($i=100;$i<=1000;$i=$i+100) $navbar.='<option value="'.$i.'"'.($i==$itemPerPage?' selected="selected"':'').'>'.$i.' รายการ</option>';
	$navbar.='</select>'._NL;
	$navbar.='</form>'._NL;
	$navbar.='</nav><!--nav-->'._NL;

	$self->theme->navbar=$navbar;

	if ($u) mydb::where('u.`username`=:username',':username',$_REQUEST['u']);
	if ($q) mydb::where('(u.`username` LIKE :q OR u.`name` LIKE :q OR u.`email` LIKE :q)',':q','%'.$q.'%');
	if ($_REQUEST['r']) mydb::where('u.roles=:role',':role',$_REQUEST['r']);

	$page=post('page');
	if ($itemPerPage==-1) {
	} else {
		$firstRow=$page>1 ? ($page-1)*$itemPerPage : 0;
		$limit='LIMIT '.$firstRow.' , '.$itemPerPage;
	}

	$stmt = 'SELECT SQL_CALC_FOUND_ROWS
			u.`uid`, u.`username`, u.`name`, u.`roles`
			, GROUP_CONCAT(s.`title`) stationList
		FROM %users% AS u
			LEFT JOIN %flood_user% fu USING(`uid`)
			LEFT JOIN %flood_station% s USING(`station`)
		%WHERE%
		GROUP BY u.`uid`
		ORDER BY '.$orders[$order][1].' '.($sort==1?'ASC':'DESC').'
		'.$limit;

	$dbs= mydb::select($stmt);
	//$ret.=$dbs->_query;

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
	$tables->addClass('user-list');
	$tables->caption='รายชื่อสมาชิก';
	$tables->thead=array('name'=>'ชื่อ','กลุ่มสมาชิก','สถานี','');

	foreach ($dbs->items as $rs) {
		if ($rs->uid==1) continue;
		$tables->rows[] = [
			'<!-- <a class="sg-action" href="'.url('flood/admin/member',array('id'=>$rs->uid)).'" data-rel="box" title="User Information"> -->'
			.'<img class="profile" src="'.model::user_photo($rs->username).'" width="48" height="48" />'
			.'<strong>'.$rs->name.'</strong>'
			.'<!-- </a> --><br />'
			.$rs->username.'('.$rs->uid.')<br />'.$rs->email,
			$rs->roles,
			str_replace(',', '<br />', $rs->stationList),
			'<a class="sg-action" href="'.url('flood/admin/member/station/'.$rs->uid).'" data-rel="box">สถานี</a>',
			'config'=>array('class'=>'user-'.$rs->status,'title'=>'User was '.$rs->status)
		];
		if ($rs->admin_remark) $tables->rows[]=array('','<td colspan="3"><p><font color="#f60">Admin remark : '.$rs->admin_remark.'</font></p></td>');
	}

	$ret .= $tables->build();

	if ($dbs->_num_rows) {
		$ret .= '<div class="pagenv">'.$pagenv->show.'</div>'._NL;
		$ret.='<p>รวมทั้งสิ้น <strong>'.$totals.'</strong> รายการ</p>';
	}

	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>