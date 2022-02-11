<?php
/**
* Project owner
*
* @param Object $self
* @param Object $id
* @return String
*/
function project_admin_follow($self,$id=NULL) {
	$q=post('q');
	$id=SG\getFirst(post('id'),$id);
	$action=post('action');
	$order=SG\getFirst($para->order,post('o'),'date');
	$sort=SG\getFirst($para->sort,post('s'),1);
	$year=post('y');
	$itemPerPage=SG\getFirst(post('i'),100);
	$type=SG\getFirst(post('t'),'');
	$org=SG\getFirst(post('org'),'');

	$orders=array(
		'date'=>array('วันที่สร้าง','t.`created`'),
		'title'=>array('ชื่อโครงการ','CONVERT(`title` USING tis620)'),
		'prcode'=>array('รหัสโครงการ','p.`prid`'),
		'org'=>array('หน่วยงาน','CONVERT(`orgName` USING tis620)'),
	);
	$types=array(2=>'แผนงาน',3=>'ชุดโครงการ',1=>'โครงการ');

	R::View('project.toolbar',$self,'Project Administrator','admin');
	$self->theme->sidebar=R::View('project.admin.menu','follow');

	$navbar.='<div class="nav -page"><header class="header -hidden"><h3>Project Management</h3></header>'._NL;
	$navbar.='<form id="search-member" method="get" action="'.url('project/admin/follow').'"><ul>';
	$navbar.='<li class="ui-nav"><input type="hidden" name="id" id="id" />เงื่อนไข ';
	$years=mydb::select('SELECT DISTINCT `pryear` FROM %project% ORDER BY `pryear` ASC')->items;

	// Select project year
	$navbar.='<label></label><select class="form-select" name="y"><option value="">** ทุกปี **</option>';
	foreach ($years as $item) {
		$navbar.='<option value="'.$item->pryear.'" '.($item->pryear==$year?' selected="selected"':'').'>พ.ศ.'.($item->pryear+543).'</option>';
	}
	$navbar.='</select>';

	// Select project type
	$navbar.='<label></label><select class="form-select" name="t"><option value="">** ทุกประเภท **</option>';
	foreach ($types as $key=>$item) $navbar.='<option value="'.$key.'" '.($key==$type?' selected="selected"':'').'>'.$item.'</option>';
	$navbar.='</select>';

	// Select organization
	$navbar.='<label></label><select class="form-select" name="org"><option value="">** ทุกหน่วยงาน **</option>';
	$orgs=mydb::select('SELECT DISTINCT t.`orgid`,o.`name` FROM %topic% t LEFT JOIN %db_org% o USING(`orgid`) WHERE `type`="project" HAVING o.`name` IS NOT NULL ORDER BY CONVERT(o.`name` USING tis620) ASC')->items;
	array_unshift($orgs,(object)array('orgid'=>'out','name'=>'** หน่วยงานภายนอก **'));
	array_unshift($orgs,(object)array('orgid'=>'sub','name'=>'** หน่วยงานภายใต้สังกัด **'));
	array_unshift($orgs,(object)array('orgid'=>'in','name'=>'** หน่วยงานภายใน **'));

	foreach ($orgs as $key=>$item) {
		$navbar.='<option value="'.$item->orgid.'" '.($item->orgid==$org?' selected="selected"':'').'>'.$item->name.'</option>';
	}
	$navbar.='</select>';

	// Select project status
	$navbar.='<label></label><select class="form-select" name=""><option>** ทุกสถานะ **</option></select>';
	$navbar.='</li>';
	$navbar.='<li class="ui-nav"><span><input class="sg-autocomplete form-text" data-query="'.url('project/search').'" data-callback="submit" data-altfld="id" type="text" name="q" id="search-box" size="30" value="'.$q.'" placeholder="ค้นชื่อโครงการ"></span></li>';

	// Add new project button
	$navbar.='<li class="ui-nav -add"><a class="btn -floating -circle48 -fixed -at-bottom -at-right" href="'.url('project/admin/follow/create').'" title="สร้างโครงการใหม่"><i class="icon -addbig -white"></i></a></li>';
	$navbar.='</ul>';

	// Report options
	$navbar.='เรียงลำดับ <select class="form-select" name="o">';
	foreach ($orders as $key=>$item) $navbar.='<option value="'.$key.'" '.($key==$order?' selected="selected"':'').'>'.$item[0].'</option>';
	$navbar.='</select> ';
	$navbar.='<input type="radio" name="s" value="1"'.($sort==1?'checked="checked"':'').' /> น้อยไปมาก</option> <input type="radio" name="s" value="2"'.($sort!=1?'checked="checked"':'').' /> มากไปน้อย ';
	$navbar.='<label>จำนวน</label><select class="form-select" name="i"><option value="-1">** ทั้งหมด **</option>';
	for($i=100;$i<=1000;$i=$i+100) $navbar.='<option value="'.$i.'"'.($i==$itemPerPage?' selected="selected"':'').'>'.$i.' รายการ</option>';
	$navbar.='</select>';
	$navbar.=' <button class="btn -primary" type="submit"><span>แสดงรายชื่อ</span></button>';
	$navbar.='</form>'._NL;
	$navbar.='</div><!--navbar-->'._NL;

	$self->theme->navbar=$navbar;




	if ($id) {
		$ret.=__project_admin_project_info($id);
	} else {
		switch (post('r')) {
			case 'delete':
				$ret.=__project_admin_delete();
				break;

			default:
				if ($year) mydb::where('p.`pryear`=:year',':year',$year);
				if ($type) mydb::where('p.`prtype`+0=:type',':type',$type);
				if ($org) {
					if ($org=='in') {
						$orgList=mydb::select('SELECT `orgid` FROM %db_org% WHERE `parent`=1; -- {reset:false}')->lists->text ;
					} else if ($org=='sub') {
						$orgList=mydb::select('SELECT `orgid` FROM %db_org% WHERE `sector`=1 AND `parent`>1; -- {reset:false}')->lists->text ;
					} else if ($org=='out') {
						$orgList=mydb::select('SELECT `orgid` FROM %db_org% WHERE `sector`>1; -- {reset:false}')->lists->text ;
					} else {
						$orgList=$org;
					}
					if ($orgList) {
						mydb::where('t.`orgid` IN (:org)',':org','SET:'.$orgList);
					} else {
						mydb::where('FALSE');
					}
				}
				if ($u) mydb::where('u.`username`=:username',':username',$u);
				if ($q && $q!='all') mydb::where('(t.`title` LIKE :q)',':q','%'.$q.'%');
				if (post('r')) mydb::where('u.roles=:role',':role',post('r'));

				$page = post('page');
				if ($itemPerPage == -1) {
				} else {
					$firstRow = $page > 1 ? ($page-1)*$itemPerPage : 0;
					$limit = 'LIMIT '.$firstRow.' , '.$itemPerPage;
				}

				mydb::value('$ORDER$', $orders[$order][1].($sort==1?'ASC':'DESC'));
				mydb::value('$LIMIT$', $limit);

				$stmt = 'SELECT SQL_CALC_FOUND_ROWS
						t.`tpid`,t.`title`, t.`uid`, t.`created`, t.`orgid`, o.`name` orgName
						, u.`username`, u.`name` ownerName
						, p.`prid`, p.`prtype`,`project_status`,`project_status`+0 `project_statuscode`
						, pt.`title` `parentTitle`
					FROM %project% AS p
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %db_org% o USING(`orgid`)
						LEFT JOIN %topic% pt ON pt.`tpid`=p.`projectset`
						LEFT JOIN %users% u ON u.`uid`=t.`uid`
					%WHERE%
					ORDER BY
					  CONVERT(`parentTitle` USING tis620) ASC,
						$ORDER$
					$LIMIT$';

				$dbs= mydb::select($stmt,$where['value']);
				//$ret.=mydb()->_query;

				$totals = $dbs->_found_rows;

				$pagePara['q']=post('q');
				$pagePara['page']=$page;
				$pagePara['i']=$itemPerPage;
				$pagenv = new PageNavigator($itemPerPage,$page,$totals,q(),false,$pagePara);
				$no=$pagenv?$pagenv->FirstItem():0;

				$text[]='โครงการ';
				if ($q) $text[]='ที่มีคำว่า "'.$q.'"';
				$text[]='('.($totals?'จำนวน '.$totals.' รายการ' : 'ไม่มีรายการ').')';
				if ($text) $self->theme->title=implode(' ',$text);

				if ($dbs->_empty) {
					$ret.=message('error','ไม่มีรายชื่อโครงการตามเงื่อนไขที่ระบุ');
				} else {
					$ret .= '<div class="pagenv">'.$pagenv->show.'</div>'._NL;
				}

				$orders=array(
					'date'=>array('วันที่สร้าง','t.`created`'),
					'title'=>array('ชื่อโครงการ','CONVERT(`title` USING tis620)'),
					'prcode'=>array('รหัสโครงการ','p.`prid`'),
					'org'=>array('หน่วยงาน','CONVERT(`orgName` USING tis620)'),
				);

				$tables = new Table();
				$tables->addClass('project-list');
				$tables->caption='รายชื่อโครงการ';
				$tables->thead['id'.($order=='prcode'?' order':'')]='รหัสโครงการ';
				$tables->thead['type']='ประเภท';
				$tables->thead[]='';
				$tables->thead['name'.($order=='title'?' order':'')]='ชื่อโครงการ';
				$tables->thead['zone'.($order=='org'?' order':'')]='หน่วยงาน';
				$tables->thead['date'.($order=='date'?' order':'')]='วันที่สร้าง';
				$tables->thead['status']='สถานะโครงการ';

				foreach ($dbs->items as $rs) {
					$tables->rows[]=array(
						SG\getFirst($rs->prid,'???'),
						$rs->prtype,
						'<a class="sg-action" href="'.url('project/list',array('u'=>$rs->uid)).'" data-rel="box"><img class="profile left" src="'.model::user_photo($rs->username).'" width="24" height="24" /></a>',
						'<a href="'.url('paper/'.$rs->tpid).'" title="Project Information" target="_blank"><strong>'.SG\getFirst($rs->title,'???').'</strong></a><br />'
						.($rs->parentTitle?'ภายใต้ '.$rs->parentTitle:'แรกสุด')
							.' โดย '.$rs->ownerName,
						$rs->orgName,
						sg_date($rs->created,'d-m-ปปปป G:i'),
						($rs->status==_DRAFT AND $rs->project_status=='ระงับโครงการ') ? 'รอลบโครงการ':$rs->project_status,
						'config'=>array('class'=>'project-status-'.$rs->project_statuscode,'title'=>$rs->status)
					);
				}
				$ret .= $tables->build();
				if ($dbs->_num_rows) {
					$ret .= '<div class="pagenv">'.$pagenv->show.'</div>'._NL;
					$ret.='<p>รวมทั้งสิ้น <strong>'.$totals.'</strong> รายการ</p>';
				}
				break;
		}
	}
	//$ret.=print_o(post(),'post');
	//$ret.=print_o($dbs,'$dbs');
	$ret.='<style type="text/css"> .item th {white-space:nowrap;}</style>';
	return $ret;
}

function __project_admin_project_info($id) {
	$ret.='<h3>รายละเอียดโครงการ</h3>';
	return $ret;
}

function __project_admin_delete() {
	$self->theme->title='รายชื่อโครงการแจ้งลบ';
	$para=para($para,'order=t.tpid','sort=DESC','items=1000');

	mydb::where('t.`status` = :status',':status',_DRAFT);
	mydb::where('p.`project_status` = "ระงับโครงการ"');

	$stmt = 'SELECT DISTINCT
		t.`tpid`,t.`title`, o.`name` orgName
		, p.`project_status`
		, t.`uid`, u.`username`, u.`name` ownerName
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %db_org% o USING(`orgid`)
			LEFT JOIN %users% u ON t.`uid`=u.`uid`
		%WHERE%
		ORDER BY CONVERT(`title` USING tis620) ASC';

	$dbs = mydb::select($stmt,$where['value']);

	$tables = new Table();
	$tables->thead=array('no'=>'','','ชื่อโครงการ','amt calendarTotals'=>'กิจกรรม<br />(ทำแล้ว/ตามแผน)','date'=>'กิจกรรม<br />ล่าสุด','สถานะ','');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			++$no,
			'<a class="sg-action" href="'.url('project/list',array('u'=>$rs->uid)).'" data-rel="box"><img src="'.model::user_photo($rs->username).'" width="24" height="24" alt="'.htmlspecialchars($rs->ownerName).'" title="'.htmlspecialchars($rs->ownerName).'" /></a>',
			'<a href="'.url('paper/'.$rs->tpid).'" target="_blank">'.SG\getFirst($rs->title,'ไม่ระบุชื่อ').'</a>'
			. ($rs->orgName ? '<br />'.$rs->orgName : ''),
			($rs->ownerActivity ? $rs->ownerActivity:'-')
			. '/'
			. ($rs->calendarTotals ? $rs->calendarTotals : '-'),
			$rs->lastReport ? sg_date($rs->lastReport,'ว ดด ปปปป') : '-',
			'รอลบ',
			'<a class="sg-action" href="'.url('project/'.$rs->tpid.'/info.delete').'" data-rel="box" data-width="640"><i class="icon -material">delete</i></a>',
		);
	}
	$ret .= $tables->build();
	return $ret;
}
?>