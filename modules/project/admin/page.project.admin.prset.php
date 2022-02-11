<?php
/**
* Project owner
*
* @param Object $self
* @param Object $id
* @return String
*/
function project_admin_prset($self, $id = NULL, $action = NULL) {
	$q=post('q');
	$id=SG\getFirst(post('id'),$id);
	$order=SG\getFirst($para->order,post('o'),'date');
	$sort=SG\getFirst($para->sort,post('s'),1);
	$year=SG\getFirst(post('y'),date('m')>9?date('Y')+1:date('Y'));
	$itemPerPage=SG\getFirst(post('i'),100);
	$type='2,3';
	$org=SG\getFirst(post('org'),'');
	$orders=array(
						'date'=>array('วันที่สร้าง','t.`created`'),
						'title'=>array('ชื่อโครงการ','CONVERT(`title` USING tis620)'),
						'prcode'=>array('รหัสโครงการ','p.`prid`'),
						'org'=>array('หน่วยงาน','CONVERT(`orgName` USING tis620)'),
						);
	$types=array(1=>'โครงการ',2=>'แผนงาน',3=>'ชุดโครงการ');

	R::View('project.toolbar',$self,'Project Management','admin');
	$self->theme->sidebar=R::View('project.admin.menu','follow');

	$navbar.='<div class="nav -page"><header class="header -hidden"><h3>Project Management</h3></header>'._NL;
	$navbar.='<form id="search-member" method="get" action="'.url('project/admin/prset').'"><ul>';
	$navbar.='<li class="ui-nav"><input type="hidden" name="id" id="id" />เงื่อนไข ';
	$years=mydb::select('SELECT DISTINCT `pryear` FROM %project% ORDER BY `pryear` ASC')->items;
	$navbar.='<label></label><select class="form-select" name="y"><option value="">** ทุกปี **</option>';
	foreach ($years as $item) $navbar.='<option value="'.$item->pryear.'" '.($item->pryear==$year?' selected="selected"':'').'>พ.ศ.'.($item->pryear+543).'</option>';
	$navbar.='</select>';
	$navbar.='<label></label><select class="form-select" name="t"><option value="">** ทุกประเภท **</option>';
	foreach ($types as $key=>$item) $navbar.='<option value="'.$key.'" '.($key==$type?' selected="selected"':'').'>'.$item.'</option>';
	$navbar.='</select>';
	$navbar.='<label></label><select class="form-select" name="org"><option value="">** ทุกหน่วยงาน **</option>';
	$orgs=mydb::select('SELECT DISTINCT t.`orgid`,o.`name` FROM %topic% t LEFT JOIN %db_org% o USING(`orgid`) WHERE `type`="project" AND o.`parent` IS NULL HAVING o.`name` IS NOT NULL ORDER BY CONVERT(o.`name` USING tis620) ASC')->items;

	foreach ($orgs as $key=>$item) $navbar.='<option value="'.$item->orgid.'" '.($item->orgid==$org?' selected="selected"':'').'>'.$item->name.'</option>';
	$navbar.='</select>';
	$navbar.='<label></label><select class="form-select" name=""><option value="">** ทุกสถานะ **</option></select>';
	$navbar.='</li>';
	//$navbar.='<li><span class="search-box"><input class="sg-autocomplete" data-query="'.url('project/search').'" data-callback="submit" data-altfld="id" type="text" name="q" id="search-box" size="30" value="'.$q.'" placeholder="ค้นชื่อโครงการ"></span></li>';
	$navbar.='<li class="ui-nav -add"><a class="sg-action btn -floating -circle48 -fixed -at-bottom -at-right" href="'.url('project/admin/prset/0/create').'" data-rel="box" title="สร้างโครงการหลักระดับบนสุด"><i class="icon -addbig -white"></i></a></li>';
	$navbar.='</ul>';
	$navbar.='เรียงลำดับ <select class="form-select" name="o">';
	foreach ($orders as $key=>$item) $navbar.='<option value="'.$key.'" '.($key==$order?' selected="selected"':'').'>'.$item[0].'</option>';
	$navbar.='</select> ';
	$navbar.='<input type="radio" name="s" value="1"'.($sort==1?'checked="checked"':'').' /> น้อยไปมาก</option> <input type="radio" name="s" value="2"'.($sort!=1?'checked="checked"':'').' /> มากไปน้อย ';
	$navbar.='<label>จำนวน</label><select class="form-select" name="i"><option value="-1">** ทั้งหมด **</option>';
	for($i=100;$i<=1000;$i=$i+100) $navbar.='<option value="'.$i.'"'.($i==$itemPerPage?' selected="selected"':'').'>'.$i.' รายการ</option>';
	$navbar.='</select>';
	$navbar.=' <button type="submit" class="btn"><span>แสดงโครงการ</span></button></form>'._NL;
	$navbar.='</div><!--navbar-->'._NL;

	$self->theme->navbar=$navbar;

	switch ($action) {
		case 'info' :
			$ret.=__project_admin_project_info($id);
			return $ret;
			break;

		case 'create' :
			$ret.=__project_admin_prset_create($id);
			return $ret;
			break;

		case 'delete':
			$ret.=__project_admin_delete();
			break;
	}

	$where = array();
	if ($year) $where=sg::add_condition($where,'p.`pryear`=:year','year',$year);
	if ($type) $where=sg::add_condition($where,'p.`prtype`+0 IN (:type)','type','SET:'.$type);
	if ($u) $where=sg::add_condition($where,'u.`username`=:username','username',$_REQUEST['u']);
	if ($q && $q!='all') $where=sg::add_condition($where,'(t.`title` LIKE :q)','q','%'.$q.'%');
	if ($_REQUEST['r']) $where=sg::add_condition($where,'u.roles=:role','role',$_REQUEST['r']);

	$page=post('page');
	if ($itemPerPage==-1) {
	} else {
		$firstRow=$page>1 ? ($page-1)*$itemPerPage : 0;
		$limit='LIMIT '.$firstRow.' , '.$itemPerPage;
	}

	$stmt = 'SELECT SQL_CALC_FOUND_ROWS
							t.`tpid`,t.`title`, t.`uid`, t.`created`, t.`orgid`, o.`name` orgName
							, pa.`parent`
							, pa.`bdgroup`, tg.`name` prbgtype
							, u.`username`, u.`name` ownerName
							, p.`pryear`, p.`prid`, p.`prtype`, p.`budget`
							, `project_status`,`project_status`+0 `project_statuscode`
						FROM %project% AS p
							LEFT JOIN %topic% t USING(`tpid`)
							LEFT JOIN %topic_parent% pa USING(`tpid`)
							LEFT JOIN %db_org% o USING(`orgid`)
							LEFT JOIN %users% u ON u.`uid`=t.`uid`
							LEFT JOIN %tag% tg ON tg.`taggroup`="project:bdgroup" AND tg.`tid`=pa.`bdgroup`
						'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
						ORDER BY '.$orders[$order][1].($sort==1?'ASC':'DESC').'
						'.$limit;

	$dbs= mydb::select($stmt,$where['value']);
	//$ret.=mydb()->_query;

	foreach ($dbs->items as $rs) {
		$tree[$rs->tpid]=$rs->parent;
		$items[$rs->tpid]=$rs;
	}
	$lists=sg_parseTree($items,$tree);
	if ($org) {
		foreach ($lists as $key => $item) {
			if ($item['rs']->orgid!=$org) unset($lists[$key]);
		}
	}
	$projectSetTree=sg_printTreeTable($items,$lists,$rows);
	$maxLevel=0;
	foreach ($projectSetTree as $rs) $maxLevel=$rs->treeLevel>$maxLevel?$rs->treeLevel:$maxLevel;

	//$ret.='Max Level='.$maxLevel;

	//$ret.=print_o($tree,'$tree');
	//$ret.=print_o($lists,'$lists');
	//$ret.=print_o($projectSetTree,'$projectSetTree');

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
	$tables->caption='โครงการหลัก';
	$tables->thead['title']='ชื่อโครงการ';
	$tables->thead['year']='ปีงบประมาณ';
	$tables->thead['id']='กลุ่มภารกิจ';
	$tables->thead['budgetcode']='รหัสงบประมาณ';
	$tables->thead['money from']='แหล่งงบประมาณ';
	$tables->thead['code action']='รหัสกิจกรรม';
	for ($i=0;$i<=$maxLevel;$i++) $tables->thead['money budget'.$i]='งบประมาณ';
	$tables->thead['money get']='ได้รับการจัดสรร';
	$tables->thead[]='';

	foreach ($projectSetTree as $rs) {
		$ui=new ui();
		$ui->add('<a class="sg-action" href="'.url('project/admin/prset/'.$rs->tpid.'/info').'" data-rel="box">รายละเอียดโครงการหลัก</a>');
		$ui->add('<a class="sg-action" href="'.url('project/admin/prset/'.$rs->tpid.'/add').'" data-rel="box">เพิ่มโครงการหลักภายใต้โครงการหลักนี้</a>');
		$ui->add('<a class="sg-action" href="'.url('project/admin/prset/'.$rs->tpid.'/remove').'" data-confire="ต้องลบโครงการหลักนี้ กรุณายืนยัน?">ลบโครงการหลัก</a>');

		$config=array('class'=>'project-status-'.$rs->project_statuscode,'title'=>$rs->status);
		$title=SG\getFirst($rs->title,'???');

		$tables->rows[]=array(
										'<td colspan="'.(8+$maxLevel).'"><h4>'.str_repeat('--', $rs->treeLevel).' <a href="'.url('paper/'.$rs->tpid).'">'.$title.'</a></h4>'.$rs->orgName.'</td>',
										sg_dropbox($ui->build('ul')),
										'config'=>$config,
										);
		unset($row);
		$row[]='';
		$row[]=$rs->pryear+543;
		$row[]=$rs->prset;
		$row[]=$rs->prbudget;
		$row[]=$rs->prbgtype.$rs->bdgroup;
		$row[]=$rs->praction;
		for ($i=0;$i<=$maxLevel;$i++) $row[]=$rs->treeLevel==$i?number_format($rs->budget,2):'';
		$row[]='';
		$row[]='';
		$row['config']=$config;

		$tables->rows[]=$row;
	}
	$ret .= $tables->build();
	if ($projectSetTree) {
		$ret .= '<div class="pagenv">'.$pagenv->show.'</div>'._NL;
		$ret.='<p>รวมทั้งสิ้น <strong>'.count($lists).'</strong> รายการ</p>';
	}

	//$ret.=print_o(post(),'post');
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}

function __project_admin_project_info($id) {
	$rs=project_model::get_project($id);
	$ret.='<h3>'.$rs->title.'</h3>';
	$ret.='<h4>รายละเอียด</h4>';
	$ret.='<h4>โครงการย่อย</h4>';
	$ret.='<h4>การใช้จ่ายงบประมาณ</h4>';
	$ret.=print_o($rs,'$rs');
	return $ret;
}

/**
* Create main project
* @return String
*/
function __project_admin_prset_create($id) {
	$isFirstLevel=empty($id);
	$ret.='<h3>โครงการหลัก'.($isFirstLevel?'ระดับแรกสุด':'ภายใต้โครงการ'.$id).'</h3>';

	$form = new Form([
		'variable'=> 'topic',
		'action' => url('project/admin/prset/'.$id.'/create'),
		'id' => 'project-add-prset',
		'class' => 'sg-form',
		'rel' => 'box',
		'done' => 'reload:'.url('project/admin/prset/'.$id),
		'children' => [
			'pryear' => [
				'type' => 'radio',
				'label' => 'ประจำปีงบประมาณ:',
				'options' => (function() {
					$options = [];
					for ($year=date('Y'); $year<=date('Y')+1; $year++) {
						$options[$year]=$year+543;
					}
					return $options;
				})(),
				'display' => 'inline',
				'value' => SG\getFirst($post->pryear,date('m')>9?date('Y')+1:date('Y')),
			],
			'title' => [
				'type' => 'text',
				'label' => 'ชื่อโครงการหลัก',
				'class' => '-fill',
				'require' => true,
				'value' => htmlspecialchars($post->title),
			],
			'orgid' => [
				'type' => 'hidden',
				'label' => 'หน่วยงานเจ้าของโครงการ',
				'type' => 'text',
				'class' => 'sg-autocomplete -fill',
				'attr' => ['data-query'=>url('org/api/org'), 'data-altfld'=>'edit-topic-orgid'],
				'placeholder' => 'ป้อนชื่อหน่วยงาน',
			],
			'prtype' => [
				'type' => 'radio',
				'label' => 'ประเภทโครงการ:',
				'options' => [2=>'แผนงาน',3=>'โครงการหลัก'],
				'value' => 3,
				'display' => 'inline',
			],
			//กลุ่มภารกิจ	รหัสงบประมาณ	แหล่งงบประมาณ	รหัสกิจกรรม	งบประมาณ	ได้รับการจัดสรร
			'<h4>ความสอดคล้องของโครงการ</h4>',
			'govplan' => [
				'label' => '1. ความสอดคล้องตามแผนปฏิบัติการแก้ไขปัญหาและพัฒนาของรัฐบาล:',
				'type' => 'radio',
				'options' => model::get_category('project:rel-govplan'),
				'value' => $post->govplan,
			],
			'southplan' => [
				'label' => '2. ความสอดคล้องกับยุทธศาสตร์และแผนปฏิบัติการพัฒนาจังหวัดชายแดนภาคใต้:',
				'type' => 'radio',
				'options' => model::get_category('project:rel-southplan'),
				'value' => $post->southplan,
			],
			'kpi' => [
				'label' => '3. ความสอดคล้องกับตัวชี้วัดแผนงานการแก้ปัญหาจังหวัดชายแดนภาคใต้:',
				'type' => 'radio',
				'options' => model::get_category('project:rel-kpi'),
				'value' => $post->kpi,
			],
			'save' => [
				'type' => 'button',
				'value' => '<i class="icon -material">done_all</i><span>บันทึกโครงการหลัก</span>',
				'pretext' => '<a class="sg-action btn -link -cancel" data-rel="close" href="javascript:void(0)"><i class="icon -material">cancel</i><span>{tr:CANCEL}</span></a>',
				'container' => '{class: "-sg-text-right"}',
			],
		],
	]);

	$ret .= $form->build();

	return $ret;
}

function __project_admin_delete() {
	$self->theme->title='รายชื่อโครงการแจ้งลบ';
	$para=para($para,'order=t.tpid','sort=DESC','items=1000');

	$where=array();
	$where=sg::add_condition($where,'t.`status`=:status','status',_DRAFT);
	$where=sg::add_condition($where,'p.`project_status`="ระงับโครงการ"');

	$stmt='SELECT DISTINCT t.`tpid`,t.`title`, o.`name` orgName
						, p.`project_status`
						, t.`uid`, u.`username`, u.`name` ownerName
					FROM %project% p
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %db_org% o USING(`orgid`)
						LEFT JOIN %users% u ON t.`uid`=u.`uid`
						'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
					ORDER BY CONVERT(`title` USING tis620) ASC';
	$dbs= mydb::select($stmt,$where['value']);

	$tables = new Table();
	$tables->thead=array('no'=>'','','ชื่อโครงการ','amt calendarTotals'=>'กิจกรรม(ตามแผน)','amt ownerActivity'=>'กิจกรรมในพื้นที่(ทำแล้ว)','date'=>'กิจกรรมล่าสุด','สถานะโครงการ','หน่วยงาน','');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
			++$no,
			'<a class="sg-action" href="'.url('project/list',array('u'=>$rs->uid)).'" data-rel="box"><img src="'.model::user_photo($rs->username).'" width="32" height="32" alt="'.htmlspecialchars($rs->ownerName).'" title="'.htmlspecialchars($rs->ownerName).'" /></a>',
			'<a href="'.url('paper/'.$rs->tpid).'" target="_blank">'.SG\getFirst($rs->title,'ไม่ระบุชื่อ').'</a>',
			$rs->calendarTotals?$rs->calendarTotals:'-',
			$rs->ownerActivity?$rs->ownerActivity:'-',
			$rs->lastReport?sg_date($rs->lastReport,'ว ดด ปปปป'):'-',
			'รอลบโครงการ',
			$rs->orgName,
			'<a class="sg-action" href="'.url('project/'.$rs->tpid.'/info.delete').'" data-rel="box">ลบ</a>',
		);
	}
	$ret .= $tables->build();
	return $ret;
}
?>