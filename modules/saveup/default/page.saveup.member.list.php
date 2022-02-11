<?php
function saveup_member_list($self) {
	$page = post('page');
	$status = post('st');

	$itemPerPage = SG\getFirst(post('i'),100);

	R::View('saveup.toolbar',$self,'สมาชิก','member');

	if (post('mid')) location('saveup/member/view/'.post('mid'));

	$isEdit = user_access('administrator saveups,create saveup content');

	if ($isEdit) {
		$ret .= '<div class="btn-floating -right-bottom">'
			.'<a class="btn -floating -circle48" href="'.url('saveup/member/post/'.$rs->mid).'"><i class="icon -addbig -white"></i></a>'
			.'</div>';
	}


	mydb::where('m.`firstname` IS NOT NULL');
	if ($status == 'all') {
	} else if ($status) mydb::where('m.`status` = :status', ':status', $status);
	else mydb::where('m.`status` = "active"');

	$searchStr = trim(post('q'));
 	if ($searchStr) {
		list($firstname,$lastname) = sg::explode_name(' ',$searchStr);
		if ($firstname && $lastname) {
			mydb::where('m.`firstname` LIKE :firstname AND m.`lastname` LIKE :lastname', ':firstname', '%'.$firstname.'%', ':lastname', '%'.$lastname.'%');
		} else if ($firstname) {
			mydb::where('m.`firstname` LIKE :firstname OR m.`lastname` LIKE :firstname OR m.`nickname` LIKE :firstname OR m.mid LIKE :firstname', ':firstname','%'.$firstname.'%');
		}
	}

	mydb::value('$ORDER', 'm.`mid`');
	mydb::value('$SORT', 'ASC');

	if ($itemPerPage == -1) {
		mydb::value('$LIMIT$', '');
	} else {
		$firstRow = $page > 1 ? ($page-1)*$itemPerPage : 0;
		mydb::value('$LIMIT$', ($firstRow ? $firstRow.' , ' : '').$itemPerPage);
	}

	$stmt = 'SELECT SQL_CALC_FOUND_ROWS m.*
					FROM %saveup_member% m
					%WHERE%
					ORDER BY $ORDER $SORT
					LIMIT $LIMIT$';
	$dbs= mydb::select($stmt);
	//$ret .= mydb()->_query;

	$totalsRows = $dbs->_found_rows;

	$pagePara['q']=post('q');
	$pagePara['st']=$status;
	$pagePara['o']=$order;
	$pagePara['s']=$sort;
	$pagePara['i']=$itemPerPage;
	$pagePara['page']=$page;
	$pagenv = new PageNavigator($itemPerPage,$page,$totalsRows,q(),false,$pagePara);

	//$pagenv = new PageNavigator($items,$page,$total_items,q());


	$self->theme->title='กลุ่มออมทรัพย์ - รายชื่อสมาชิก';

	$no=$pagenv->FirstItem();

	if ($dbs->_empty) return $ret.message('notify','ไม่มีสมาชิกตามเงื่อนไขที่กำหนด');

	$ret .= $pagenv->show._NL;
	$tables = new Table();
	$tables->addClass('saveup-member-list');
	$tables->thead=array('id -date' => 'ID', 'approve -date' => 'สมาชิกเมื่อ', '', 'name' => 'ชื่อ - สกุล', 'address' => 'ที่อยู่', 'phone -hover-parent' => 'โทรศัพท์');
	foreach ($dbs->items as $rs) {
		$menu = '<nav class="nav iconset -hover">'
					. ($rs->facebook?'<a href="'.$rs->facebook.'" target="_blank" title="Facebook">FB</a>':'')
					. '<a href="'.url('saveup/member/view/'.$rs->mid).'" title="ดูรายละเอียด"><i class="icon -viewdoc"></i></a>'
					. '<a href="'.url('saveup/member/modify/'.$rs->mid).'" title="แก้ไขรายละเอียด"><i class="icon -edit"></i></a>'
					. '</nav>';

		unset($row);
		$row[] = $rs->mid;
		$row[] = $rs->date_approve?sg_date($rs->date_approve,'ว ดด ปป'):'<a href="'.url('saveup/member/modify/'.$rs->mid).'">Edit</a>';
		$row[] = '<a href="'.url('saveup/member/view/'.$rs->mid).'" title="ดูรายละเอียด"><img src="'.saveup_model::member_photo($rs->mid).'" class="profile-photo saveup-list-photo" width="46" height="46" /></a>';
		$row[] = $rs->prename.$rs->firstname.' '.$rs->lastname.($rs->nickname?' ('.$rs->nickname.')':'');
		$row[] = $rs->address.($rs->amphure?' อ.'.$rs->amphure:'').($rs->province?' จ.'.$rs->province:'');
		$row[] = $rs->phone
					. $menu;

		$row['config'] = array('class'=>'-'.$rs->status);
		$tables->rows[] = $row;
	}
	$ret .= $tables->build();
	$ret .= $pagenv->show._NL;

	$ret .= '<style type="text/css">
	.item tr.-inactive>td:first-child {border-left:2px #ccc solid;}
	.item tr.-inactive>td {color: #aaa;}
	.item tr.-active>td:first-child {border-left:2px green solid;}
	</style>';
	return $ret;
}
?>