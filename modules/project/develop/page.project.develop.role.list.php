<?php
/**
* project :: Proposal List By Role
* Created 2020-01-01
* Modify  2021-01-13
*
* @param Object $self
* @param Array $_POST
* @return String
*
* @usage project/develop/role/list
*/

$debug = true;

function project_develop_role_list($self) {
	// Data Model
	$getRole = SG\getFirst(post('role'));
	$getProv = SG\getFirst(post('prov'));
	$ampur = SG\getFirst(post('ampur'));
	$getYear = SG\getFirst(post('year'));
	$getStatus = SG\getFirst(post('status'));

	$isAdmin = is_admin('project');

	$statusList = array(
		1 => 'กำลังพัฒนา',
		2 => 'ส่งกองทุน',
		3 => 'กองทุนรับพิจารณา',
		4 => 'ผ่านการอนุมัติ'
	);

	$orders = array(
		'changwat' => 'provname',
		'title' => 'CONVERT(t.title USING tis620)',
		'create' => 't.created',
		'modify' => 't.changed',
		'hsmi' => 't.commenthsmidate',
		'sss' => 't.commentsssdate',
		'status' => 't.status'
	);

	$sorts = array(
		'changwat' => 'ASC',
		'title' => 'ASC',
		'status' => 'ASC, t.changed DESC'
	);

	if ($getRole) {
		mydb::where('u.`roles` LIKE :role', ':role', '%'.$getRole.'%');
	} else {
		mydb::where('u.`roles` != ""');
	}

	$stmt = 'SELECT DISTINCT d.`pryear`
		FROM %project_dev% d
		LEFT JOIN %topic% t USING(`tpid`)
		LEFT JOIN %users% u USING(`uid`)
		%WHERE%
		ORDER BY `pryear` ASC;
		-- {reset: false}';
	$yearList = mydb::select($stmt)->lists->text;

	// Select province
	$stmt = 'SELECT d.`changwat`,`provname`,COUNT(*)
		FROM %project_dev% d
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %users% u USING(`uid`)
			LEFT JOIN %co_province% cop ON cop.`provid` = t.`changwat`
		%WHERE%
		GROUP BY `changwat`
		HAVING `provname` != ""
		ORDER BY CONVERT(`provname` USING tis620) ASC;
		-- {reset: false}';
	$provDb = mydb::select($stmt);


	if ($getProv) mydb::where('t.changwat = :changwat', ':changwat',$getProv);
	if ($ampur) mydb::where('d.ampur = :ampur', ':ampur',$ampur);
	if ($getYear) mydb::where('d.`pryear` = :year',':year',$getYear);
	if ($getStatus == 1) {
		mydb::where('(d.`toorg` IS NULL AND p.`tpid` IS NULL)');
	} else if ($getStatus == 2) {
		mydb::where('(d.`toorg` IS NOT NULL AND d.`fundid` IS NULL AND p.`tpid` IS NULL)');
	} else if ($getStatus == 3) {
		mydb::where('d.`fundid` IS NOT NULL');
	} else if ($getStatus == 4) {
		mydb::where('p.`tpid` IS NOT NULL');
	}
	if (post('q')) mydb::where('t.`title` LIKE :search OR r.`email` LIKE :search', ':search','%'.post('q').'%');

	$stmt = 'SELECT t.*
			, p.`tpid` `isProject` 
			, u.`name`, cop.`provname`, r.`email` prid
			, d.`prid`, d.`status`, ps.`title` projectSetName
			, d.`budget`
			, (SELECT COUNT(*) FROM %project_tr% WHERE `formid` = "develop" AND `part` = "activity" AND `tpid` = d.`tpid`) `activity`
		FROM %project_dev% d
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %topic_revisions% r USING(`revid`)
			LEFT JOIN %project% p ON p.`tpid`=d.`tpid`
			LEFT JOIN %topic% ps ON t.`parent`=ps.`tpid`
			LEFT JOIN %users% u ON u.`uid`=t.`uid`
			LEFT JOIN %co_province% cop ON cop.`provid`=t.`changwat`
		%WHERE%
		ORDER BY '.SG\getFirst($orders[post('o')],'t.`changed`').'  '.SG\getFirst($sorts[post('o')],'DESC');

	$proposalDbs = mydb::select($stmt);
	//$ret .= mydb()->_query;
	//$ret.=print_o($proposalDbs,'$proposalDbs');




	// View Model
	R::View('project.toolbar',$self,'รายชื่อพัฒนาโครงการ '.number_format($proposalDbs->count()).' โครงการ','develop');

	$ret .= '<nav class="nav -page">';
	$ret .= '<form id="project-develop" method="get" action="'.url('project/develop/role/list').'">'._NL;

	$ret .= '<select id="role" class="form-select" name="role">'._NL.'<option value="">==ทุกกลุ่มสมาชิก==</option>'._NL;
	foreach (cfg('roles') as $key => $value) {
		if (in_array($key, array('admin','anonymous','member'))) continue;
		$ret .= '<option value="'.$key.'" '.($getRole == $key ? 'selected="selected" ':'').'>'.$key.'</option>';
	}
	$ret .= '</select> ';

	$ret .= '<select id="prov" class="form-select" name="prov">'._NL.'<option value="">==ทุกจังหวัด==</option>'._NL;
	foreach ($provDb->items as $item)
		$ret.='<option value="'.$item->changwat.'" '.($item->changwat==post('prov')?'selected="selected"':'').'>'.$item->provname.'</option>'._NL;
	$ret .= '</select> '._NL;

	// Select year
	$ret .= '<select id="year" class="form-select" name="year"><option value="">==ทุกปี==</option>';
	if (strpos($yearList,',')) {
		foreach (explode(',',$yearList) as $item) {
			$ret .= '<option value="'.$item.'" '.($item==$getYear?'selected="selected"':'').'>พ.ศ. '.($item+543).'</option>';
		}
	}
	$ret .= '</select> ';

	$ret .= '<select class="form-select" name="status">'._NL.'<option value="">==ทุกสถานะ==</option>';
	foreach ($statusList as $key => $value) {
		$ret .= '<option value="'.$key.'" '.($key==$getStatus?'selected="selected"':'').'>'.$value.'</option>'._NL;
	}
	$ret .= '</select> '._NL;
	$ret .= '<button class="btn -primary" type="submit"><i class="icon -material">search</i><span>ดูรายชื่อ</span></button></form>'._NL;
	$ret .= '</nav>'._NL;

	$sortPara = array('role'=>$getRole,'year'=>$getYear,'prov'=>$getProv,'status'=>$getStatus);
	$tables = new Table();
	$tables->addClass('-developlist');
	$tables->addConfig('id','project-develop-list');
	$tables->thead = array(
		'no' => '',
		'จังหวัด <a href="'.url(q(),$sortPara+array('o'=>'changwat')).'"><i class="icon -sort"></i></a>',
		'ชื่อโครงการ <a href="'.url(q(),$sortPara+array('o'=>'title')).'"><i class="icon -sort"></i></a>',
		'activity -amt' => 'กิจกรรม',
		'budget -money' => 'งบประมาณ',
		'created -date' => 'วันที่เริ่มพัฒนา <a href="'.url(q(),$sortPara+array('o'=>'create')).'"><i class="icon -sort"></i></a>',
		'change -date -hover-parent' => 'แก้ไขล่าสุด <a href="'.url(q(),$sortPara+array('o'=>'modify')).'"><i class="icon -sort"></i></a>',
	);

	$no = 0;

	foreach ($proposalDbs->items as $rs) {
		$today = date('Y-m-d');
		if (empty($rs->changed)) {
			$changed = '';
		} else if ($today == sg_date($rs->changed,'Y-m-d')) {
			$changed = sg_date($rs->changed,'H:i:s').' น.';
		} else {
			$changed = sg_date($rs->changed,'ว ดด ปป H:i').' น.';
		}
		if (sg_date($rs->created,'Y-m-d') == $today) {
			$created = 'วันนี้ '.sg_date($rs->created,'H:i').' น.';
		} else {
			$created = sg_date($rs->created,'ว ดด ปป');
		}

		$ui = new Ui();
		$ui->addConfig('container', '{tag: "nav", class: "nav -icons -hover"}');
		$ui->add('<a href="'.url('project/develop/'.$rs->tpid).'" title="พัฒนาโครงการ"><i class="icon -material">find_in_page</i></a>');
		$ui->add('<a href="'.url('project/develop/'.$rs->tpid).'" onclick="sgPrintPage(this.href);return false;"><i class="icon -material">print</i></a>');
		if ($rs->isProject) {
			$ui->add('<a href="'.url('project/'.$rs->isProject).'" target="_blank" title="ติดตามโครงการ"><i class="icon -material">pageview</i></a>');
		}

		$tables->rows[] = array(
			++$no,
			$rs->provname,
			'<a href="'.url('project/develop/'.$rs->tpid).'" title="คลิกเพื่อพัฒนาโครงการต่อ"><strong>'.SG\getFirst($rs->title,'<em>ไม่ระบุชื่อ</em>').'</strong></a>'
			. '<br />โดย '.$rs->name,
			$rs->activity,
			number_format($rs->budget,2),
			$created,
			$changed
			.$ui->build(),
			'config' => array(
				'class'=>'project-develop-list -status-'.$rs->status.($rs->isProject ? ' -status-pass' : '')
			)
		);
	}

	if (!$proposalDbs->count()) {
		$ret .= message('notify','ไม่มีพัฒนาโครงการตามเงื่อนไขที่ระบุ');
	}

	$ret .= $tables->build();

	/*
	head('<style type="text/css">
	.project-develop-list.-status-1>td {}
	.project-develop-list.-status-2>td {background-color: red;}
	.project-develop-list.-status-3>td {background-color: blue;}
	</style>');
	*/

	head('<script type="text/javascript" >
function printExternal(str) {
	printWindow = window.open(  str,"mywindow");
	setTimeout("printWindow.print()", 2000);
	//printWindow.close()
	setTimeout("printWindow.close()", 2000);
}
$(document).on("change","form#project-develop select",function() {
	var $this=$(this)
	if ($this.attr("id") == "role") {
		$("#prov").val("")
		$("#year").val("")
	}
	var para=$this.closest("form").serialize()
	notify("กำลังโหลด")
	location.replace(window.location.pathname+"?"+para)
});
</script>');
	return $ret;
}
?>