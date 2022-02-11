<?php
/**
* Project Org Proposal
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function project_org_proposal($self, $orgId) {
	$orgInfo = is_object($orgId) ? $orgId : R::Model('project.org.get', $orgId, '{initTemplate:true}');
	$orgId = $orgInfo->orgid;

	$ret = '';

	//R::View('project.toolbar',$self,'โครงการอยู่ระหว่างการพัฒนาโครงการ','develop');
	R::View('project.toolbar', $self, 'พัฒนาโครงการ @'.$orgInfo->name, 'org', $orgInfo);

	$prov = post('prov');
	$ampur = post('ampur');
	$year = post('year');
	$status = post('status');

	$statusList = project_base::$statusList;

	$orders = array('changwat'=>'provname', 'title'=>'CONVERT(t.title USING tis620)', 'create'=>'t.created', 'modify'=>'t.changed','hsmi'=>'t.commenthsmidate','sss'=>'t.commentsssdate', 'status'=>'t.status');
	$sorts = array('changwat'=>'ASC', 'title'=>'ASC','status'=>'ASC, t.changed DESC');
	$yearList = mydb::select('SELECT DISTINCT `pryear` FROM %project_dev% ORDER BY `pryear` ASC')->lists->text;

	$ret .= '<nav class="nav -page">';
	$ret .= '<form id="project-develop" method="get" action="'.url('project/develop/list').'">'._NL;

	// Select province
	$ret .= '<select class="form-select" name="prov">'._NL.'<option value="">==ทุกจังหวัด==</option>'._NL;
	$provDb = mydb::select('SELECT `changwat`,`provname`,COUNT(*) FROM %topic% t LEFT JOIN %co_province% cop ON cop.`provid`=t.`changwat` WHERE t.`type`="project-develop" GROUP BY `changwat` HAVING `provname`!="" ORDER BY CONVERT(`provname` USING tis620) ASC');
	foreach ($provDb->items as $item)
		$ret.='<option value="'.$item->changwat.'" '.($item->changwat==post('prov')?'selected="selected"':'').'>'.$item->provname.'</option>'._NL;
	$ret .= '</select> '._NL;

	// Select ampur
	if ($prov && mydb::table_exists('project_fund')) {
		$ret .= '<select class="form-select" name="ampur" id="input-ampur"><option value="">==ทุกอำเภอ==</option>';
		$stmt = 'SELECT DISTINCT `ampur`,`nameampur` FROM %project_fund% WHERE `changwat`=:prov ORDER BY CONVERT(`nameampur` USING tis620) ASC';
		$dbs = mydb::select($stmt,':prov',$prov);
		foreach ($dbs->items as $item) {
			$ret .= '<option value="'.$item->ampur.'" '.($item->ampur==$ampur?'selected="selected"':'').'>'.$item->nameampur.'</option>';
			
		}
		$ret .= '</select> ';
	}

	// Select year
	if (strpos($yearList,',')) {
		$ret .= '<select class="form-select" name="year" id="develop-year"><option value="">==ทุกปี==</option>';
		foreach (explode(',',$yearList) as $item) {
			$ret .= '<option value="'.$item.'" '.($item==$year?'selected="selected"':'').'>พ.ศ. '.($item+543).'</option>';
		}
		$ret .= '</select> ';
	} else {
		$ret .= '<input type="hidden" name="year" value="'.$yearList.'" />';
	}

	$ret .= '<select class="form-select" name="status">'._NL.'<option value="">==ทุกสถานะ==</option>';
	foreach ($statusList as $key => $value) {
		$ret .= '<option value="'.$key.'" '.($key==$status?'selected="selected"':'').'>'.$value.'</option>'._NL;
	}
	$ret .= '</select> '._NL;
	$ret .= '<button class="btn -primary" type="submit">ดูรายชื่อ</button></form>'._NL;
	$ret .= '</nav>'._NL;

	mydb::where('t.`orgid` = :orgid', ':orgid', $orgId);
	if ($prov) mydb::where('t.changwat = :changwat', ':changwat',$prov);
	if ($ampur) mydb::where('d.ampur = :ampur', ':ampur',$ampur);
	if ($year) mydb::where('d.`pryear` = :year',':year',$year);
	if ($status) mydb::where('d.status = :status', ':status',$status);
	if (post('q')) mydb::where('t.`title` LIKE :search OR r.`email` LIKE :search', ':search','%'.post('q').'%');

	$stmt='SELECT t.*
						, p.`tpid` isProject 
						, u.`name`, cop.`provname`, r.`email` prid
						, d.`prid`, d.`status`, ps.`title` projectSetName
					FROM %project_dev% d
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %topic_revisions% r USING(`revid`)
						LEFT JOIN %project% p ON p.`tpid`=d.`tpid`
						LEFT JOIN %topic% ps ON t.`parent`=ps.`tpid`
						LEFT JOIN %users% u ON u.`uid`=t.`uid`
						LEFT JOIN %co_province% cop ON cop.`provid`=t.`changwat`
					%WHERE%
					ORDER BY '.SG\getFirst($orders[post('o')],'t.`changed`').'  '.SG\getFirst($sorts[post('o')],'DESC');
	$dbs = mydb::select($stmt,$where['value']);
	//$ret.=print_o($dbs,'$dbs');

	$isAdmin = user_access('administer projects');
	if ($isAdmin) {
		$inlineAttr['data-update-url'] = url('project/develop/update/');
		if (post('debug')) $inlineAttr['data-debug'] = 'yes';
	}

	$tables = new Table();
	$tables->addClass(($isAdmin ? ' inline-edit' : '').' -developlist');
	$tables->addConfig('id','project-develop-list');
	if ($inlineAttr) $tables->attr=sg_implode_attr($inlineAttr);
	$tables->caption = 'โครงการอยู่ระหว่างการพัฒนาโครงการ';
	$tables->thead = array(
					'no' => '',
					'สถานะ <a href="'.url(q(),array('year'=>$year,'prov'=>$prov,'status'=>$status,'o'=>'status')).'"><i class="icon -sort"></i></a>',
					'จังหวัด <a href="'.url(q(),array('year'=>$year,'prov'=>$prov,'status'=>$status,'o'=>'changwat')).'"><i class="icon -sort"></i></a>',
					'ชื่อโครงการ <a href="'.url(q(),array('year'=>$year,'prov'=>$prov,'status'=>$status,'o'=>'title')).'"><i class="icon -sort"></i></a>',
					'วันที่เริ่มพัฒนา <a href="'.url(q(),array('year'=>$year,'prov'=>$prov,'status'=>$status,'o'=>'create')).'"><i class="icon -sort"></i></a>',
					'date changed' => 'แก้ไขล่าสุด <a href="'.url(q(),array('year'=>$year,'prov'=>$prov,'status'=>$status,'o'=>'modify')).'"><i class="icon -sort"></i></a>',
					'date changed-hsmi' => 'ความเห็นพี่เลี้ยง <!-- <a href="'.url(q(),array('year'=>$year,'prov'=>$prov,'status'=>$status,'o'=>'hsmi')).'"><i class="icon -sort"></i></a>-->',
					'date changed-sss' => 'ความเห็นผู้ทรงคุณวุฒิ <!-- <a href="'.url(q(),array('year'=>$year,'prov'=>$prov,'status'=>$status,'o'=>'sss')).'"><i class="icon -sort"></i></a>-->',
					'icons -hover-parent' => ''
				);
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
				$changed=sg_date($rs->changed,'H:i:s').' น.';
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
			$row[] = $statusList[$rs->status];
			//$row[]=view::inlineedit(array('group'=>'dev','fld'=>'status','tpid'=>$rs->tpid),$statusList[$rs->status],$isAdmin,'select',$statusList)
			//				.($rs->isProject ? '<p><a href="'.url('paper/'.$rs->tpid).'" target="_blank">โครงการติดตาม</a></p>' : ($isAdmin && $rs->status==10 ? '<p id="move-'.$rs->tpid.'"><a class="sg-action button" data-rel="#move-'.$rs->tpid.'" href="'.url('project/develop/'.$rs->tpid.'/createproject').'" data-confirm="ยืนยันการสร้างโครงการติดตาม">สร้างโครงการติดตาม</a></p>':''));
			$row[]=$rs->provname;
			$row[]='<a href="'.url('project/develop/'.$rs->tpid).'" title="คลิกเพื่อพัฒนาโครงการต่อ"><strong>'.SG\getFirst($rs->title,'<em>ไม่ระบุชื่อ</em>').'</strong></a>'
							.($prSet?'<br />ชุดโครงการ '.view::inlineedit(array('group'=>'topic','fld'=>'parent','tpid'=>$rs->tpid),$rs->projectSetName,$isAdmin,'select',$prSet) : '')
							.'<br />รหัสโครงการ : '.view::inlineedit(array('group'=>'dev','fld'=>'prid','tpid'=>$rs->tpid, 'class'=>'inline'),$rs->prid,$isAdmin)
							.'<br />โดย '.$rs->name;
			$row[]=$created;
			$row[]=$changed;
			$row[]=$rs->commenthsmidate?sg_date($rs->commenthsmidate,'ว ดด ปป H:i'):'';
			$row[]=$rs->commentsssdate?sg_date($rs->commentsssdate,'ว ดด ปป H:i'):'';
			$row[]='<nav class="nav iconset -hover"><a href="'.url('project/develop/'.$rs->tpid).'"><i class="icon -viewdoc"></i></a><a href="'.url('project/develop/'.$rs->tpid).'" onclick="printExternal(this.href);return false;"><i class="icon -print"></i></a></nav>';
			$row['config']=array('class'=>'project-develop-status-'.$rs->status);
			$tables->rows[]=$row;
		}
		$ret .= $tables->build();
	} else {
		$ret .= message('notify','ไม่มีโครงการที่กำลังพัฒนาตามเงื่อนไขที่ระบุ');
	}



	head('<script type="text/javascript" >
function printExternal(str) {
	printWindow = window.open(  str,"mywindow");
	setTimeout("printWindow.print()", 2000);
	//printWindow.close()
	setTimeout("printWindow.close()", 2000);
}
$(document).on("change","form#project-develop select",function() {
	var $this=$(this)
	var para=$this.closest("form").serialize()
	notify("กำลังโหลด")
	location.replace(window.location.pathname+"?"+para)
});

</script>');
	return $ret;
}
?>