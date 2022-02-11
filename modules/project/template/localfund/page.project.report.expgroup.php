<?php
/**
* Project Local Report From บัณฑิตอาสา
*
* @param Object $self
* @return String
*/

function project_report_expgroup($self) {
	project_model::set_toolbar($self,'รายงานงบประมาณ/การจ่ายเงินของโครงการจำแนกตามพื้นที่');

	$prset=post('prset');
	$prov=post('prov');
	$ampur=post('ampur');
	$year=post('year');
	$fundid=post('fund');
	$status=post('status');

	$yearList=mydb::select('SELECT DISTINCT `pryear` FROM %project% ORDER BY `pryear` ASC')->lists->text;
	$expGroupList=model::get_category('project:expgr','catid');

	$ret.='<nav class="nav -page">';
	$ret.='<form id="project-develop" method="get" action="'.url('project/report/expgroup').'">';
	$ret.='<ul>';

	// Select province
	$ret.='<li class="ui-nav"><select class="form-select" name="prov"><option value="">==ทุกจังหวัด==</option>';
	$provDb=mydb::select('SELECT `changwat`,`provname`,COUNT(*) FROM %topic% t LEFT JOIN %co_province% cop ON cop.`provid`=t.`changwat` WHERE t.`type`="project" GROUP BY `changwat` HAVING `provname`!="" ORDER BY CONVERT(`provname` USING tis620) ASC');
	foreach ($provDb->items as $item) {
		$ret.='<option value="'.$item->changwat.'" '.($item->changwat==$prov?'selected="selected"':'').'>'.$item->provname.'</option>';
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
	$ret.='<li class="ui-nav">&nbsp;&nbsp;<button type="submit" class="btn -primary"><span>ดูรายงาน</span></button></li>';
	$ret.='</ul></form>';
	$ret.='</nav>';

	$where=array();
	$where=sg::add_condition($where,'t1.`formid`="activity" AND t1.`part`="owner"');
	if ($prset) $where=sg::add_condition($where,'p.`projectset`=:prset','prset',$prset);
	if ($year) $where=sg::add_condition($where,'p.`pryear`=:year','year',$year);
	if ($prov) $where=sg::add_condition($where, 'p.changwat=:changwat', 'changwat',$prov);
	if ($ampur) $where=sg::add_condition($where, 'p.ampur=:ampur', 'ampur',$ampur);
	if ($fundid) $where=sg::add_condition($where, 'd.fundid=:fundid', 'fundid',$fundid);
	$whereCond=$where?'WHERE '.implode(' AND ',$where['cond']):'';


	$label='CONCAT("จังหวัด",`provname`)';
	if ($prov) $label='`title`';
	else if ($ampur) $label='CONCAT("กองทุนตำบล",f.`fundname`)';
	else if ($prov) $label='CONCAT("อำเภอ",cod.`distname`)';

	$stmt="SELECT
			$label `label`
			, t1.`tpid`, p.`changwat`, p.`ampur`
			, p.`budget` totalBudget
			, SUM(t1.`num1`) expGr1
			, SUM(t1.`num2`) expGr2
			, SUM(t1.`num3`) expGr3
			, SUM(t1.`num4`) expGr4
			, SUM(t1.`num5`) expGr5
			, SUM(t1.`num6`) expGr6
			, SUM(t1.`num7`) expTotal
	FROM %project% p
		LEFT JOIN %project_tr% t1 USING(`tpid`)
		LEFT JOIN %topic% t USING(`tpid`)
		LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat`
		LEFT JOIN %co_district% cod ON cod.`distid`=CONCAT(p.`changwat`,p.`ampur`)
	$whereCond
	GROUP BY `label`";

	$stmt="SELECT
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
				, t1.`tpid`, p.`changwat`, p.`ampur` 
				, p.`budget`
				, SUM(t1.`num1`) expGr1
				, SUM(t1.`num2`) expGr2
				, SUM(t1.`num3`) expGr3
				, SUM(t1.`num4`) expGr4
				, SUM(t1.`num5`) expGr5
				, SUM(t1.`num6`) expGr6
				, SUM(t1.`num7`) expTotal 
				FROM `sgz_project` p 
					LEFT JOIN `sgz_project_tr` t1 USING(`tpid`) LEFT JOIN `sgz_topic` t USING(`tpid`) 
					LEFT JOIN `sgz_co_province` cop ON cop.`provid`=p.`changwat` 
					LEFT JOIN `sgz_co_district` cod ON cod.`distid`=CONCAT(p.`changwat`,p.`ampur`) 
				$whereCond
				GROUP BY `tpid`) a
			GROUP BY `label`";

	$dbs=mydb::select($stmt,$where['value']);


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

		if ($prov) $link='<a href="'.url('paper/'.$rs->tpid).'">';
		else $link='<a href="'.url('project/report/expgroup',array('prov'=>$rs->changwat,'ampur'=>empty($prov)?NULL:$rs->ampur,'fund'=>empty($ampur)?NULL:$rs->fundid,'year'=>$year)).'">';

		$tables->rows[]=array(
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
											);
		$totalGr1+=$rs->expGr1;
		$totalGr2+=$rs->expGr2;
		$totalGr3+=$rs->expGr3;
		$totalGr4+=$rs->expGr4;
		$totalGr5+=$rs->expGr5;
		$totalGr6+=$rs->expGr6;
		$totalBudget+=$rs->totalBudget;
	}
	$totalAll=$totalGr1+$totalGr2+$totalGr3+$totalGr4+$totalGr5+$totalGr6;

	$tables->tfoot[1]=array(
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
											);
	$tables->tfoot[2]=array(
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
							);

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