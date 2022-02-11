<?php
/**
* Project :: Report By Expense Group
*
* @param Object $self
* @return String
*/

function project_report_expgroup($self) {
	R::View('project.toolbar', $self, 'รายงานงบประมาณ/การจ่ายเงินของโครงการจำแนกตามพื้นที่', 'report');

	$prset = post('prset');
	$prov = post('prov');
	$ampur = post('ampur');
	$year = post('year');
	$status = post('status');

	$expGroupList = model::get_category('project:expgr','catid');

	$yearList = mydb::select(
		'SELECT DISTINCT `pryear` FROM %project% WHERE `pryear` != "" ORDER BY `pryear` ASC'
	)->lists->text;

	$prsetDbs = mydb::select(
		'SELECT `tpid`,`title`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
		WHERE `prtype` IN ("แผนงาน","ชุดโครงการ") AND `project_status`="กำลังดำเนินโครงการ"'
	);

	$provDb = mydb::select(
		'SELECT cop.`provid` `changwatId`, cop.`provname` `changwatName`, COUNT(*) `totalProject`
		FROM %project% p
			LEFT JOIN %topic% t ON t.`tpid` = p.`tpid`
			LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(t.`areacode`, 2)
		GROUP BY `changwatId`
		HAVING `changwatName` != ""
		ORDER BY CONVERT(`changwatName` USING tis620) ASC'
	);

	$ret.='<nav class="nav -page">';
	$ret.='<form id="project-develop" class="form-report" method="get" action="'.url('project/report/expgroup').'">';
	$ret.='<ul>';

	// Select project set
	$projectSets=array();
	$ret.='<li class="ui-nav"><select class="form-select" name="prset"><option value="">==ทุกแผนงาน==</option>';
	foreach ($prsetDbs->items as $item) {
		$ret.='<option value="'.$item->tpid.'" '.($item->tpid==$prset?'selected="selected"':'').'>'.$item->title.'</option>';
	}
	//$projectSets[$item->tpid]=$item->title;
	$ret.='</select></li>';

	// Select province
	$ret.='<li class="ui-nav"><select class="form-select" name="prov"><option value="">==ทุกจังหวัด==</option>';

	foreach ($provDb->items as $item) {
		$ret .= '<option value="'.$item->changwatId.'" '.($item->changwatId==$prov?'selected="selected"':'').'>'.$item->changwatName.'</option>';
	}
	$ret.='</select></li>';

	// Select ampur
	if ($prov) {
		$ret.='<li class="ui-nav"><select class="form-select" name="ampur" id="input-ampur"><option value="">==ทุกอำเภอ==</option>';
		$stmt='SELECT DISTINCT `distid`,`distname` FROM %co_district% WHERE LEFT(`distid`,2) = :prov ORDER BY CONVERT(`distname` USING tis620) ASC';
		$dbs=mydb::select($stmt,':prov',$prov);
		foreach ($dbs->items as $item) {
			$ret.='<option value="'.$item->distid.'" '.($item->distid==$ampur?'selected="selected"':'').'>'.$item->distname.'</option>';

		}
		$ret.='</select></li>';
	}

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
	$ret.='<li class="ui-nav">&nbsp;&nbsp;<button type="submit" class="btn -primary"><i class="icon -material">search</i><span>ดูรายงาน</span></button></li>';
	$ret.='</ul></form>';
	$ret.='</nav>';

	mydb::where('t1.`formid` = "activity" AND t1.`part` = "owner"');
	if ($prset) mydb::where('t.`parent` = :prset', ':prset', $prset);
	if ($year) mydb::where('p.`pryear` IN ( :year)', ':year', 'SET:'.$year);
	if ($prov) mydb::where('LEFT(t.`areacode`, 2) = :changwat', ':changwat', $prov);
	if ($ampur) mydb::where('SUBSTR(t.`areacode`, 3, 2) = :ampur', ':ampur', $ampur);


	$label='CONCAT("จังหวัด",`provname`)';
	if ($prov) $label='`title`';
	else if ($prov) $label='CONCAT("อำเภอ",cod.`distname`)';

	$stmt = "SELECT
		$label `label`
		, a.*
		, SUM(`budget`) totalBudget
		, SUM(expGr1) expGr1
		, SUM(expGr2) expGr2
		, SUM(expGr3) expGr3
		, SUM(expGr4) expGr4
		, SUM(expGr5) expGr5
		, SUM(expGr6) expGr6
		, SUM(expTotal) expTotal
		FROM (
			SELECT
			cop.`provname`
			, t.`title`
			, t1.`tpid`
			, LEFT(t.`areacode`, 2) `changwat`
			, SUBSTR(t.`areacode`, 3, 2) `ampur`
			, p.`budget`
			, SUM(t1.`num1`) expGr1
			, SUM(t1.`num2`) expGr2
			, SUM(t1.`num3`) expGr3
			, SUM(t1.`num4`) expGr4
			, SUM(t1.`num5`) expGr5
			, SUM(t1.`num6`) expGr6
			, SUM(t1.`num7`) expTotal
			FROM %project% p
				LEFT JOIN %topic% t ON t.`tpid` = p.`tpid`
				LEFT JOIN %project_tr% t1 ON t1.`tpid` = p.`tpid`
				LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(t.`areacode`, 2)
				LEFT JOIN %co_district% cod ON cod.`distid` = LEFT(t.`areacode`, 4)
			%WHERE%
			GROUP BY p.`tpid`
		) a
		GROUP BY `label`";

	$dbs = mydb::select($stmt);
	// debugMsg(mydb()->_query);


	$tables = new Table();
	$tables->addClass('project-develop-status');
	$tables->thead['prov']='พื้นที่';
	foreach ($expGroupList as $key=>$value) $tables->thead[$key]=$value;
	$tables->thead['total']='รวมจ่าย';
	$tables->thead['budget']='งบประมาณ';
	$tables->thead['percent']='%';

	$subTotal=$totalBudget=0;
	$totalGr1=$totalGr2=$totalGr3=$totalGr4=$totalGr5=$totalGr6=0;
	foreach ($dbs->items as $rs) {
		$subTotal=$rs->expGr1+$rs->expGr2+$rs->expGr3+$rs->expGr4+$rs->expGr5+$rs->expGr6;

		$label=empty($rs->label)?'ไม่ระบุจังหวัด':$rs->label;

		if ($prov) $link='<a href="'.url('project/'.$rs->tpid).'">';
		else $link='<a href="'.url('project/report/expgroup',array('prov'=>$rs->changwat,'ampur'=>empty($prov)?NULL:$rs->ampur,'year'=>$year)).'">';

		$tables->rows[] = [
			$link.$label.'</a>',
			number_format($rs->expGr1,2),
			number_format($rs->expGr2,2),
			number_format($rs->expGr3,2),
			number_format($rs->expGr4,2),
			number_format($rs->expGr5,2),
			number_format($rs->expGr6,2),
			number_format($subTotal,2),
			number_format($rs->totalBudget,2),
			round($subTotal*100/$rs->totalBudget).'%',
			//.($subTotal==$rs->expTotal+0?'':'['.$subTotal.'='.$rs->expTotal.']'),
		];
		$totalGr1+=$rs->expGr1;
		$totalGr2+=$rs->expGr2;
		$totalGr3+=$rs->expGr3;
		$totalGr4+=$rs->expGr4;
		$totalGr5+=$rs->expGr5;
		$totalGr6+=$rs->expGr6;
		$totalBudget+=$rs->totalBudget;
	}
	$totalAll=$totalGr1+$totalGr2+$totalGr3+$totalGr4+$totalGr5+$totalGr6;

	$tables->tfoot[] = [
		'รวม',
		number_format($totalGr1,2),
		number_format($totalGr2,2),
		number_format($totalGr3,2),
		number_format($totalGr4,2),
		number_format($totalGr5,2),
		number_format($totalGr6,2),
		number_format($totalAll,2),
		number_format($totalBudget,2),
		round($totalAll*100/$totalBudget).'%',
	];

	$tables->tfoot[] = [
		'สัดส่วน(%)',
		round($totalGr1*100/$totalAll).'%',
		round($totalGr2*100/$totalAll).'%',
		round($totalGr3*100/$totalAll).'%',
		round($totalGr4*100/$totalAll).'%',
		round($totalGr5*100/$totalAll).'%',
		round($totalGr6*100/$totalAll).'%',
		round($totalAll*100/$totalAll).'%',
		'',
		''
	];

	$ret .= $tables->build();

	//$ret.='<pre>'.mydb()->_query.'</pre>';
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
	</style>';
	return $ret;
}
?>