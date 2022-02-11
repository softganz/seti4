<?php
/**
* Project Local Report From บัณฑิตอาสา
*
* @param Object $self
* @return String
*/

function project_report_abstract($self, $tpid = NULL) {
	R::View('project.toolbar',$self,'รายงานบทคัดย่อโครงการ', 'report');

	$prset=post('prset');
	$prov=post('prov');
	$ampur=post('ampur');
	$year=post('year');
	$fundid=post('fund');
	$status=post('status');


	if ($tpid) {
		$stmt='SELECT t.`title`,tr.`text1` FROM %project_tr% tr LEFT JOIN %topic% t USING(`tpid`) WHERE `tpid`=:tpid AND `formid`="valuation" AND `part`="title" LIMIT 1';
		$rs=mydb::select($stmt,':tpid',$tpid);
		$ret.='<h2 class="title">บทคัดย่อ</h2>';
		$ret.='<h2>'.$rs->title.'</h2>';
		$ret.=sg_text2html($rs->text1);
		return $ret;
	}

	$yearList=mydb::select('SELECT DISTINCT `pryear` FROM %project% WHERE `prtype`="โครงการ" ORDER BY `pryear` ASC')->lists->text;

	$ret.='<nav class="nav -page">';
	$ret.='<form id="project-develop" method="get" action="'.url('project/report/abstract').'">';
	$ret.='<ul>';

	// Select project set
	$prsetDbs=mydb::select('SELECT `tpid`,`title` FROM %project% p LEFT JOIN %topic% t USING(`tpid`) WHERE `prtype` IN ("แผนงาน","ชุดโครงการ") AND `project_status`="กำลังดำเนินโครงการ" ORDER BY CONVERT(`title` USING tis620) ASC');
	$ret.='<li class="ui-nav"><select class="form-select" name="prset"><option value="">==ทุกแผนงาน==</option>';
	foreach ($prsetDbs->items as $item) {
		$ret.='<option value="'.$item->tpid.'" '.($item->tpid==$prset?'selected="selected"':'').'>'.$item->title.'</option>';
	}
	$ret.='</select></li>';


	// Select year
	if (strpos($yearList,',')) {
		$ret.='<li class="ui-nav"><select class="form-select" name="year" id="develop-year"><option value="">==ทุกปี==</option>';
		foreach (explode(',',$yearList) as $item) {
			$ret.='<option value="'.$item.'" '.($item==$year?'selected="selected"':'').'>พ.ศ. '.($item+543).'</option>';
		}
		$ret.='</select></li>';
	} else {
		$ret.='<input type="hidden" name="year" value="'.$yearList.'" />';
	}

	// Select status
	//$ret.='<select class="form-select"><option>==ทุกสถานะ==</option></select> ';
	$ret.='<li class="ui-nav">&nbsp;&nbsp;<button type="submit" class="btn -primary"><span>ดูรายงาน</span></button></li>';
	$ret.='</ul></form>';
	$ret.='</nav>';

	mydb::where('p.`prtype`="โครงการ" AND p.`projectset`>0');
	if ($prset) mydb::where('p.`projectset`=:prset',':prset',$prset);
	if ($year) mydb::where('p.`pryear`=:year',':year',$year);
	if ($prov) mydb::where('p.changwat=:changwat', ':changwat',$prov);
	if ($ampur) mydb::where('p.ampur=:ampur', ':ampur',$ampur);
	if ($fundid) mydb::where('d.fundid=:fundid', ':fundid',$fundid);


	$label='`projectSetName`';
	if ($prset) $label='`title`';

	$stmt='SELECT 
					  cop.`provname`
					, p.`tpid`
					, t.`title`, p.`agrno`, p.`prid`, p.`pryear`
					, p.`changwat`, p.`ampur` 
					, p.`budget`
					, p.`projectset`
					, pr.`title` `projectSetName`
						, p.`project_status`, p.`project_status`+0 `project_statuscode`
					FROM %project% p 
						LEFT JOIN %topic% t USING(`tpid`) 
						LEFT JOIN %topic% pr ON p.`projectset`=pr.`tpid`
						LEFT JOIN %project_tr% t1 ON p.`tpid`=t1.`tpid`
						LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat` 
						LEFT JOIN %co_district% cod ON cod.`distid`=CONCAT(p.`changwat`,p.`ampur`) 
					%WHERE%
					GROUP BY p.`tpid`
					ORDER BY CONVERT(t.`title` USING tis620) ASC';

	$dbs=mydb::select($stmt,$where['value']);


	$tables = new Table();
	$tables->addClass('project-list');
	$tables->thead=array('no'=>'','ข้อตกลงเลขที่','รหัสโครงการ','ปี','จังหวัด','title'=>'ชื่อโครงการ','สถานะโครงการ','');

	foreach ($dbs->items as $rs) {
		$subTotal=$rs->expGr1+$rs->expGr2+$rs->expGr3+$rs->expGr4+$rs->expGr5+$rs->expGr6;

		$label=empty($rs->label)?'N/A':$rs->label;

		if ($prset) $link='<a href="'.url('paper/'.$rs->tpid).'">';
		else $link='<a href="'.url('project/report/expplan',array('prset'=>$rs->projectset, 'year'=>$year)).'">';

		$tables->rows[]=array(++$no,
												$rs->agrno,
												$rs->prid,
												$rs->pryear?sg_date($rs->pryear,'ปปปป'):'',
												$rs->provname,
												'<a href="'.url('paper/'.$rs->tpid.'/member/trainer/post/valuation').'">'.$rs->title.'</a>',$rs->project_status,
												'<a class="sg-action" href="'.url('project/report/abstract/'.$rs->tpid).'" data-rel="box"><i class="icon -viewdoc"></i><span>บทคัดย่อ</span></a>',
												'config'=>array('class'=>'project-status-'.$rs->project_statuscode));	}

	$ret .= $tables->build();

	//$ret.=print_o($dbs,'$dbs');

	head('<script type="text/javascript">
		$(document).on("change","form#project-develop select",function() {
			var $this=$(this)
			if ($this.attr("name")=="prov") $("#input-ampur").val("");
			var para=$this.closest("form").serialize()
			notify("กำลังโหลด")
			location.replace(window.location.pathname+"?"+para)
		});
		</script>');
	$ret.='<style type="text/css">
	.item td:nth-child(n+2) {text-align:center;}
	.item td.col-title {text-align: left;}
	</style>';
	return $ret;
}
?>