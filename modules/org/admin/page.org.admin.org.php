<?php
function org_admin_org($self,$orgid=NULL) {
	$self->theme->title='รายชื่อองค์กร';
	$orgid=SG\getFirst(post('id'),$orgid);
	$order=SG\getFirst(post('o'),'name');
	$sort=SG\getFirst(post('s'),'asc');
	$searchStr=post('qn');

	$isAdmin=user_access('administrator orgs');

	if ($orgid && is_numeric($orgid)) return R::Page('org.view',$self,$orgid);
	//else if (post('qn')) return R::Page('org.search',post('qn'));

	$tables = new Table();
	$tables->caption='รายชื่อองค์กร';
	$tables->addClass('org-list');

	if (post('name') && post('add')) {
		$data=new stdClass();
		$data->name=post('name');
		$newOrg=R::Model('org.create',$data);
	}

	$ret.='<form method="POST" action="'.url('org/admin/org').'">';
	//if ($isAdmin) $ret.='<button type="submit" value="รวมชื่อ">รวมชื่อ</button>';

	//$dbs=org_model::search_org(NULL,org_model::get_my_org());
	$dbs=R::Model('org.getall',$searchStr,'{order:"'.$order.'",sort:"'.$sort.'",debug:false}');
	$no=0;

	if ($isAdmin) $tables->thead[]='<input type="checkbox" />';
	$tables->thead['no']='';
	$tables->thead['org']='ชื่อองค์กร <a href="'.url('org/admin/org',array('qn'=>$searchStr,'o'=>'name','s'=>$order=='name'&&$sort=='desc'?'asc':'desc')).'"><i class="icon -sort"></i></a>';
	$tables->thead['type']='<span class="-nowrap">ประเภท <a href="'.url('org/admin/org',array('qn'=>$searchStr,'o'=>'type','s'=>$order=='type'&&$sort=='desc'?'asc':'desc')).'"><i class="icon -sort"></i></a></span>';
	$tables->thead['issue']='<span class="-nowrap">ประเด็นการทำงาน <a href="'.url('org/admin/org',array('qn'=>$searchStr,'o'=>'issue','s'=>$order=='issue'&&$sort=='desc'?'asc':'desc')).'"><i class="icon -sort"></i></a></span>';
	$tables->thead['amt -nowrap']='สมาชิก <a href="'.url('org/admin/org',array('qn'=>$searchStr,'o'=>'member','s'=>$order=='member'&&$sort=='desc'?'asc':'desc')).'"><i class="icon -sort"></i></a>';

	unset($row);
	if ($isAdmin) {
		$row[]='';
		$row[]='<td></td>';
		$row[]='<td colspan="3"><input type="text" class="form-text -fill -highlight" name="name" placeholder="ป้อนชื่อองค์กรใหม่ที่ต้องการเพิ่ม" /></td>';
		$row[]='<td class="col-center -nowrap"><button class="btn -primary" type="submit" name="add" value="เพิ่มองค์กร"><i class="icon -material">add</i> เพิ่มองค์กรใหม่</button></td>';
	}
	$tables->rows[]=$row;
	if ($newOrg) {
		$tables->rows[]=array('<td></td>','<td></td>','<a href="">'.$newOrg->name.'</a>','','','','config'=>array('class'=>'newitem'));
	}
	$current_group=' ';
	foreach ($dbs->items as $rs ) {
		if ( $order=='issue') {
			if ( $rs->org_issue != $current_group ) {
				$tables->rows[]='<tr><th colspan="5">'.(empty($rs->issue)?'ไม่ระบุประเด็น':$rs->issue).'</th></tr>';
			}
			$current_group=$rs->org_issue;
		} else if ($order=='type') {
			if ( $rs->typename != $current_group ) {
				$tables->rows[]='<tr><th colspan="5">'.(empty($rs->typename)?'ไม่ระบุประเภท':$rs->typename).'</th></tr>';
			}
			$current_group=$rs->typename;
		}

		unset($row);
		if ($isAdmin) $row[]='<input type="checkbox" name="id[]" value="'.$rs->org_id.'" />';
		$row[]=++$no;
		$row[]='<a href="'.url('org/admin/org/'.$rs->orgid).'">'.$rs->name.'</a>'.($rs->address || $rs->phone?('<br />'.($rs->address?'ที่อยู่ : '.$rs->address.' ':'').($rs->phone?'โทรศัพท์ : '.$rs->phone:'')):'');
		$row[]=$rs->type_name;
		$row[]=$rs->issue_name;
		$row[]=empty($rs->members)?'-':$rs->members;
		$tables->rows[]=$row;
	}
	$ret.=$tables->build();
	$ret.='</form>';
	//$ret.=print_o(post(),'post()');
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>