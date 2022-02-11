<?php
/**
* Module :: Description
* Created 2021-08-01
* Modify  2021-08-01
*
* @param String $arg1
* @return Widget
*
* @usage module/{id}/method
*/

$debug = true;

class OrgList extends Page {
	var $arg1;

	function __construct($orgId = NULL) {
	}

	function build() {
		$orgId=SG\getFirst(post('id'),$orgId);
		$order=SG\getFirst(post('o'),'name');
		$sort=SG\getFirst(post('s'),'asc');
		$searchStr=post('qn');

		$isAdmin=user_access('administer orgdbs');

		// if ($orgId && is_numeric($orgId)) return R::Page('org.view',$self,$orgId);
		//else if (post('qn')) return R::Page('org.search',post('qn'));


		// $ret.='<form method="POST" action="'.url('org/list').'">';
		// if ($isAdmin) $ret.='<button type="submit" value="รวมชื่อ">รวมชื่อ</button>';

		//$dbs=org_model::search_org(NULL,org_model::get_my_org());
		$dbs = R::Model(
			'org.search',
			$searchStr,
			$isAdmin ? NULL : org_model::get_my_org(),
			'{order:"'.$order.'",sort:"'.$sort.'"}'
		);
	
		$no=0;

		$tables = new Table();
		$tables->caption='รายชื่อองค์กร';
		$tables->addClass('org-list');
		if ($isAdmin) $tables->thead[]='<input type="checkbox" />';
		$tables->thead['no']='';
		$tables->thead['org']='ชื่อองค์กร <a href="'.url('org/list',array('qn'=>$searchStr,'o'=>'name','s'=>$order=='name'&&$sort=='desc'?'asc':'desc')).'"><i class="icon -sort"></i></a>';
		$tables->thead['type']='<span class="-nowrap">ประเภท <a href="'.url('org/list',array('qn'=>$searchStr,'o'=>'type','s'=>$order=='type'&&$sort=='desc'?'asc':'desc')).'"><i class="icon -sort"></i></a></span>';
		$tables->thead['issue']='<span class="-nowrap">ประเด็นการทำงาน <a href="'.url('org/list',array('qn'=>$searchStr,'o'=>'issue','s'=>$order=='issue'&&$sort=='desc'?'asc':'desc')).'"><i class="icon -sort"></i></a></span>';
		$tables->thead['amt -nowrap']='สมาชิก <a href="'.url('org/list',array('qn'=>$searchStr,'o'=>'member','s'=>$order=='member'&&$sort=='desc'?'asc':'desc')).'"><i class="icon -sort"></i></a>';

		unset($row);
		if ($isAdmin) $row[]='';
		$row[]='<td></td>';
		$row[]='<td colspan="3"><input type="text" class="form-text -fill" name="orgname" placeholder="ค้นหาชื่อองค์กร" /></td>';
		$row[]='<td class="col-center"><button class="btn" type="submit" value="Search"><i class="icon -search"></i></button></td>';
		$tables->rows[]=$row;
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
			$row[]='<a href="'.url('org/'.$rs->orgid).'">'.$rs->name.'</a>'.($rs->address || $rs->phone?('<br />'.($rs->address?'ที่อยู่ : '.$rs->address.' ':'').($rs->phone?'โทรศัพท์ : '.$rs->phone:'')):'');
			$row[]=$rs->type_name;
			$row[]=$rs->issue_name;
			$row[]=empty($rs->members)?'-':$rs->members;
			$tables->rows[]=$row;
		}
		// $ret.=$tables->build();
		// $ret.='</form>';

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'องค์กร',
			]),
			'body' => new Widget([
				'children' => [
					new Form([
						'action' => url('org/list'),
						'children' => [
							$isAdmin ? '<button class="btn -primary" type="submit" value="รวมชื่อ">รวมชื่อ</button>' : NULL,
							$tables->build(),
						],
					]),
					$ret,
				],
			]),
		]);
	}
}
?>