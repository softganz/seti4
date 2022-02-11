<?php
/**
 * Order report
 *
 * @param Object $self
 * @return String
 */
function project_admin_report_calendar($self) {
	R::View('project.toolbar',$self,'รายงานปฎิทินกิจกรรม','admin');
	$self->theme->sidebar=R::View('project.admin.menu','report');

	$q=post('q');
	$id=SG\getFirst(post('id'),$id);
	$action=post('action');
	$order=SG\getFirst($para->order,post('o'),'oid');
	$sort=SG\getFirst($para->sort,post('s'),2);
	$year=post('year');
	$status=post('st');
	$itemPerPage=SG\getFirst(post('i'),100);
	$type=post('t');
	$org=SG\getFirst(post('org'),'');

	$orders = array(
		'oid'=>array('อินเด็กซ์','`id`'),
	);

	$statusList=array('1'=>'ไม่มีกิจกรรมหลัก');
	$navbar.='<header class="header -hidden"><h3>Project Management</h3></header>'._NL;
	$navbar.='<form id="search-member" method="get" action="'.url('project/admin/report/calendar').'">'._NL;
	$navbar.='<input type="hidden" name="id" id="id" />'._NL;
	$navbar.='<ul>'._NL;
	$navbar.='<li>เงื่อนไข ';
	$navbar.='<label></label><select class="form-select" name="st"><option value="">** ทุกสถานะ **</option>';
	foreach ($statusList as $key => $value) $navbar.='<option value="'.$key.'"'.($status!='' && $key==$status?' selected="selected"':'').'>'.$value.'</option>';
	$navbar.='</select>';
	$navbar.='</li>'._NL;
	$navbar.='<li><span class="search-box"><input type="text" name="q" size="20" value="'.$q.'" placeholder="ค้นหากิจกรรม"></span></li>'._NL;
	$navbar.=' <input type="submit" class="button" value="แสดง" />'._NL;
	$navbar.='</ul>'._NL;
	$navbar.='เรียงลำดับ <select class="form-select" name="o">';
	foreach ($orders as $key=>$item) $navbar.='<option value="'.$key.'" '.($key===$order?' selected="selected"':'').'>'.$item[0].'</option>';
	$navbar.='</select> '._NL;
	$navbar.='<input type="radio" name="s" value="1"'.($sort==1?' checked="checked"':'').' /> น้อยไปมาก <input type="radio" name="s" value="2"'.($sort!=1?' checked="checked"':'').' /> มากไปน้อย '._NL;
	$navbar.='<label>จำนวน</label><select class="form-select" name="i"><option value="-1">** ทั้งหมด **</option>';
	for($i=100;$i<=1000;$i=$i+100) $navbar.='<option value="'.$i.'"'.($i==$itemPerPage?' selected="selected"':'').'>'.$i.' รายการ</option>';
	$navbar.='</select>'._NL;
	$navbar.='</form>'._NL;
	$self->theme->navbar=$navbar;


	if (post('uid')) mydb::where('o.`uid` = :uid',':uid',post('uid'));
	if ($year) mydb::where('YEAR(o.`orderdate`) = :year',':year',$year);
	if ($q) {
		$q=preg_replace('/\s+/', ' ', $q);
		if (is_numeric($q)) {
			mydb::where('o.`oid` = :q OR o.`orderno` = :q',':q',$q);
		} else {
			mdb::where('(c.`title` LIKE :q)',':q','%'.$q.'%');
		}
	}

	if ($status==1) $where=sg::add_condition($where,'a.`mainact` IS NULL OR m.`trid` IS NULL');

	$page=post('page');
	if ($itemPerPage==-1) {
	} else {
		$firstRow=$page>1 ? ($page-1)*$itemPerPage : 0;
		$limit='LIMIT '.$firstRow.' , '.$itemPerPage;
	}

	$stmt = 'SELECT SQL_CALC_FOUND_ROWS
		c.`id`, c.`tpid`, c.`from_date`, c.`title`
		, a.`mainact`
		, m.`trid` mainActId
		, c.`created_date`
		, t.`title` projectTitle
		FROM %calendar% c
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %project_activity% a ON a.`calid`=c.`id`
			LEFT JOIN %project_tr% m ON m.`trid`=a.`mainact`
		%WHERE%
		ORDER BY '.$orders[$order][1].($sort==1?'ASC':'DESC').'
		'.$limit;

	$dbs = mydb::select($stmt);
	//$ret.=mydb()->_query;

	$totals = $dbs->_found_rows;

	$pagePara['q']=post('q');
	$pagePara['st']=$status;
	$pagePara['o']=$order;
	$pagePara['s']=$sort;
	$pagePara['i']=$itemPerPage;
	$pagePara['page']=$page;
	$pagenv = new PageNavigator($itemPerPage,$page,$totals,q(),false,$pagePara);
	$no=$pagenv?$pagenv->FirstItem():0;

	if ($dbs->_empty) {
		$ret.=message('error','ไม่มีข้อมูลตามเงื่อนไขที่ระบุ');
	} else {
		$ret .= '<div class="pagenv">'.$pagenv->show.'</div>'._NL;
		$tables = new Table();
		$tables->header['date from']='From date';
		$tables->header[]='Title';
		$tables->header[]='Project';
		$tables->header[]='MainAct';
		$tables->header[]='MainActId';
		$tables->header['date created']='Created';
		foreach ($dbs->items as $rs) {
			unset($row);
			$row[]=$rs->from_date;
			$row[]=$rs->title;
			$row[]='<a href="'.url('project/'.$rs->tpid).'" target="_blank">'.$rs->projectTitle.'</a>';
			$row[]=$rs->mainact!=$rs->mainActId?'<font color="red">'.$rs->mainact.'</font>':$rs->mainact;
			$row[]=$rs->mainActId;
			$row[]=sg_date($rs->created_date,'Y-m-d H:i');
			$tables->rows[]=$row;
		}


		$ret .= $tables->build();
		if ($dbs->_num_rows) {
			$ret .= '<div class="pagenv">'.$pagenv->show.'</div>'._NL;
			$ret.='<p>รวมทั้งสิ้น <strong>'.$totals.'</strong> รายการ</p>';
		}
	}
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>