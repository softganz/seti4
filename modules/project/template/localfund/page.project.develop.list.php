<?php
/**
* Project Local Report From บัณฑิตอาสา
*
* @param Object $self
* @return String
*/

function project_develop_list($self) {
	R::View('project.toolbar',$self,'โครงการอยู่ระหว่างการพัฒนาโครงการ','develop');

	$prov=post('prov');
	$ampur=post('ampur');
	$year=post('year');
	$status=post('status');

	$statusList=project_base::$statusList;

	$orders=array('changwat'=>'CONVERT(CONCAT(`provname`,`nameampur`,`fundname`) USING tis620)', 'title'=>'CONVERT(t.title USING tis620)', 'create'=>'t.created', 'modify'=>'t.changed','hsmi'=>'t.commenthsmidate','sss'=>'t.commentsssdate', 'status'=>'t.status');
	$sorts=array('changwat'=>'ASC', 'title'=>'ASC','status'=>'ASC, t.changed DESC');

	$yearList=mydb::select('SELECT DISTINCT `pryear` FROM %project_dev% ORDER BY `pryear` ASC')->lists->text;

	$ret.='<nav class="nav -page -project-develop">';
	$ret.='<form id="project-develop" method="get" action="'.url('project/develop/list').'">'._NL;

	// Select province
	$provDb = mydb::select('SELECT LEFT(t.`areacode`,2) `changwat`,cop.`provname`,COUNT(*)
		FROM %project_dev% d
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(t.`areacode`,2)
		GROUP BY `changwat`
		HAVING `provname` IS NOT NULL
		ORDER BY CONVERT(`provname` USING tis620) ASC'
	);

	$ret .= '<select class="form-select" name="prov">'._NL.'<option value="">==ทุกจังหวัด==</option>'._NL;
	foreach ($provDb->items as $item) $ret.='<option value="'.$item->changwat.'" '.($item->changwat==post('prov')?'selected="selected"':'').'>'.$item->provname.'</option>'._NL;
	$ret .= '</select>'._NL;

	// Select ampur
	if ($prov) {
		$dbs = mydb::select('SELECT DISTINCT CONCAT(`changwat`,`ampur`) `ampur`,`nameampur`
			FROM %project_fund%
			WHERE `changwat` = :prov
			ORDER BY CONVERT(`nameampur` USING tis620) ASC',
			':prov',$prov
		);

		$ret .= '<select class="form-select" name="ampur" id="input-ampur"><option value="">==ทุกอำเภอ==</option>';
		foreach ($dbs->items as $item) {
			$ret .= '<option value="'.$item->ampur.'" '.($item->ampur==$ampur?'selected="selected"':'').'>'.$item->nameampur.'</option>';
		}
		$ret .= '</select> ';
	}

	// Select year
	if (strpos($yearList,',')) {
		$ret.='<select class="form-select" name="year" id="develop-year"><option value="">==ทุกปี==</option>';
		foreach (explode(',',$yearList) as $item) {
			$ret.='<option value="'.$item.'" '.($item==$year?'selected="selected"':'').'>พ.ศ. '.($item+543).'</option>';
		}
		$ret.='</select> ';
	} else {
		$ret.='<input type="hidden" name="year" value="'.$yearList.'" />';
	}

	$ret.='<select class="form-select" name="status">'._NL.'<option value="">==ทุกสถานะ==</option>';
	foreach ($statusList as $key => $value) {
		$ret.='<option value="'.$key.'" '.($key==$status?'selected="selected"':'').'>'.$value.'</option>'._NL;
	}
	$ret.='</select> '._NL;
	$ret.='<button class="btn -primary" type="submit"><i class="icon -search -white"></i></button></form>'._NL;
	$ret.='</nav>'._NL;

	if ($ampur) mydb::where('LEFT(t.`areacode`,4) = :ampur', ':ampur',$ampur);
	else if ($prov) mydb::where('LEFT(t.`areacode`,2) = :changwat', ':changwat',$prov);
	if ($year) mydb::where('d.`pryear`=:year',':year',$year);
	if ($status) mydb::where('d.status=:status', ':status',$status);
	if (post('q')) mydb::where('t.`title` LIKE :search OR r.`email` LIKE :search', ':search','%'.post('q').'%');

	mydb::value('$SORT',SG\getFirst($sorts[post('o')],'DESC'));
	mydb::value('$ORDER','ORDER BY '.SG\getFirst($orders[post('o')],'t.`changed`'));

	$stmt='SELECT t.*
			, d.`tpid` `proposalId`
			, p.`tpid` isProject
			, u.`name`, r.`email` prid
			, d.`prid`, d.`status`, d.`budget`
			, ps.`title` projectSetName
			, t.`orgid`
			, o.`shortname`
			, o.`name` `orgName`
			, cod.`distname` `ampurName`
			, cop.`provname` `changwatName`
			, (SELECT COUNT(*) FROM %project_tr% a WHERE a.`tpid` = t.`tpid` AND a.`formid` = "develop" AND a.`part` = "activity") `activity`
		FROM %project_dev% d
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %topic_revisions% r USING(`revid`)
			LEFT JOIN %db_org% o USING(`orgid`)
			LEFT JOIN %project_fund% f ON f.`fundid`=d.`fundid`
			LEFT JOIN %project% p ON p.`tpid`=d.`tpid`
			LEFT JOIN %topic% ps ON t.`parent`=ps.`tpid`
			LEFT JOIN %users% u ON u.`uid`=t.`uid`
			LEFT JOIN %co_province% cop ON cop.`provid`=o.`changwat`
			LEFT JOIN %co_district% cod ON cod.`distid`=CONCAT(o.`changwat`,o.`ampur`)
		%WHERE%
		HAVING `tpid` IS NOT NULL
		$ORDER $SORT
		LIMIT 1000;';
	$dbs=mydb::select($stmt);

	// debugMsg(mydb()->_query);
	// debugMsg($dbs,'$dbs');


	$isAdmin=user_access('administer projects');
	if ($isAdmin) {

		$inlineAttr['data-update-url']=url('project/develop/update/');
		if (post('debug')) $inlineAttr['data-debug']='yes';
	}

	$tables = new Table();
	$tables->addClass(($isAdmin?' inline-edit':'').' -developlist');
	$tables->id='project-develop-list';
	if ($inlineAttr) $tables->attr=sg_implode_attr($inlineAttr);
	$tables->caption='โครงการอยู่ระหว่างการพัฒนาโครงการ';
	$tables->thead['no']='';
	$tables->thead[]='สถานะ <a href="'.url(q(),array('year'=>$year,'prov'=>$prov,'status'=>$status,'o'=>'status')).'"><i class="icon -sort"></i></a>';
	$tables->thead[]='ชื่อกองทุน';
	$tables->thead[]='อำเภอ';
	$tables->thead[]='จังหวัด <a href="'.url(q(),array('year'=>$year,'prov'=>$prov,'status'=>$status,'o'=>'changwat')).'"><i class="icon -sort"></i></a>';
	$tables->thead['title']='ชื่อโครงการ <a href="'.url(q(),array('year'=>$year,'prov'=>$prov,'status'=>$status,'o'=>'title')).'"><i class="icon -sort"></i></a>';
	$tables->thead['budget -money']='งบประมาณ<br />(บาท)';
	$tables->thead['activity -amt']='กิจกรรม';
	$tables->thead['created -date']='วันที่เริ่มพัฒนา <a href="'.url(q(),array('year'=>$year,'prov'=>$prov,'status'=>$status,'o'=>'create')).'"><i class="icon -sort"></i></a>';
	$tables->thead['date changed']='แก้ไขล่าสุด <a href="'.url(q(),array('year'=>$year,'prov'=>$prov,'status'=>$status,'o'=>'modify')).'"><i class="icon -sort"></i></a>';

	// ผู้พัฒนา
	// ส่งกองทุน
	// รับไว้พิจารณา
	// ผ่าน/อนุมัติ
	// ดำเนินการเสร็จสิ้น
	// ยอดเงิน/ใบฎีกา

	//$tables->thead['date changed-hsmi']='ความเห็นพี่เลี้ยง <a href="'.url(q(),array('year'=>$year,'prov'=>$prov,'status'=>$status,'o'=>'hsmi')).'"><i class="icon -sort"></i></a>';
	//$tables->thead['changed-sss -date']='ความเห็นผู้ทรงคุณวุฒิ <a href="'.url(q(),array('year'=>$year,'prov'=>$prov,'status'=>$status,'o'=>'sss')).'"><i class="icon -sort"></i></a>';
	$tables->thead[]='';
	$no=0;

	//$prSet=array('101'=>'โครงการร่วมสร้างชุมชนน่าอยู่','3074'=>'โครงการร่วมสร้างชุมชนน่าอยู่ - ชุดเล็ก');

	$prSets=mydb::select('SELECT * FROM %project% p LEFT JOIN %topic% USING(`tpid`) WHERE `prtype`="ชุดโครงการ" ORDER BY CONVERT(`title` USING tis620) ASC');
	foreach ($prSets->items as $item) $prSet[$item->tpid]=$item->title;

	if ($dbs->_num_rows) {
		foreach ($dbs->items as $rs) {
			$today=date('Y-m-d');
			if (empty($rs->changed)) {
				$changed='';
			} else if ($today==sg_date($rs->changed,'Y-m-d')) {
				$changed=sg_date($rs->changed,'H:i').' น.';
			} else {
				$changed=sg_date($rs->changed,'ว ดด ปป H:i').' น.';
			}
			if (sg_date($rs->created,'Y-m-d')==$today) {
				$created='วันนี้ '.sg_date($rs->created,'H:i').' น.';
			} else {
				$created=sg_date($rs->created,'ว ดด ปป');
			}
			unset($row);
			$row[]=++$no;
			$row[]=view::inlineedit(array('group'=>'dev','fld'=>'status','tpid'=>$rs->tpid),$statusList[$rs->status],$isAdmin,'select',$statusList)
							.($rs->isProject ? '<p><a href="'.url('paper/'.$rs->tpid).'" target="_blank">โครงการติดตาม</a></p>' : ($isAdmin && $rs->status==10 ? '<p id="move-'.$rs->tpid.'"><a class="sg-action button" data-rel="#move-'.$rs->tpid.'" href="'.url('project/develop/'.$rs->tpid.'/createproject').'" data-confirm="ยืนยันการสร้างโครงการติดตาม">สร้างโครงการติดตาม</a></p>':''));
			$row[]=$rs->orgName;
			$row[]=$rs->ampurName;
			$row[]=$rs->changwatName;
			$row[]='<a href="'.url('project/develop/'.$rs->tpid).'" title="คลิกเพื่อพัฒนาโครงการต่อ"><strong>'.SG\getFirst(trim($rs->title),'<em>ไม่ระบุชื่อ</em>').'</strong></a>'
							.($prSet?'<br />ชุดโครงการ '.view::inlineedit(array('group'=>'topic','fld'=>'parent','tpid'=>$rs->tpid),$rs->projectSetName,$isAdmin,'select',$prSet) : '')
							.'<br />รหัสโครงการ : '.view::inlineedit(array('group'=>'dev','fld'=>'prid','tpid'=>$rs->tpid, 'class'=>'inline'),$rs->prid,$isAdmin)
							.'<br />โดย '.($rs->shortname!=''?'<a href="'.url('project/fund/'.$rs->orgid).'">'.$rs->name.'</a>':$rs->name);
			$row[]=number_format($rs->budget,2);
			$row[]=$rs->activity;
			$row[]=$created;
			$row[]=$changed;
			//$row[]=$rs->commenthsmidate?sg_date($rs->commenthsmidate,'ว ดด ปป H:i'):'';
			//$row[]=$rs->commentsssdate?sg_date($rs->commentsssdate,'ว ดด ปป H:i'):'';
			$row[]='<a href="'.url('project/develop/'.$rs->tpid).'" onclick="printExternal(this.href);return false;">พิมพ์</a>';
			$row['config']=array('class'=>'project-develop-status-'.$rs->status);
			$tables->rows[]=$row;
			$totalBudget+=$rs->budget;
		}
		$tables->tfoot[]=array('','','','','','รวมงบประมาณ',number_format($totalBudget,2),'','');
	} else {
		$tables->rows[]=array('<td colspan="11">ไม่มีโครงการที่กำลังพัฒนา <a href="'.url('project/develop/create').'">คลิกที่นี่</a> เพื่อเริ่มต้นพัฒนาโครงการใหม่</td>');
	}
	$ret .= $tables->build();
	head('<script type="text/javascript" >
function printExternal(str) {
	printWindow = window.open(  str,"mywindow");
	setTimeout("printWindow.print()", 2000);
	//printWindow.close()
	setTimeout("printWindow.close()", 2000);
}
$(document).on("change","form#project-develop select",function() {
	var $this=$(this)
	if ($this.attr("name")=="prov") $("#input-ampur").val("");
	var para=$this.closest("form").serialize()
	notify("กำลังโหลด")
	location.replace(window.location.pathname+"?"+para)
});

</script>');
	return $ret;
}
?>