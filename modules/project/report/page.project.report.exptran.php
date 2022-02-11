<?php
/**
* Project Report Expanse transaction
*
* @param Object $self
* @return String
*/

function project_report_exptran($self) {
	R::View('project.toolbar',$self,'รายงานบันทึกการจ่ายเงิน', 'report');

	$prset=post('prset');
	$prov=post('prov');
	$ampur=post('ampur');
	$year=post('year');
	$fundid=post('fund');
	$status=post('status');
	$fromDate=post('fromdate')?sg_date(post('fromdate'),'Y-m-d'):date('Y-m-01');
	$toDate=post('todate')?sg_date(post('todate'),'Y-m-d'):date('Y-m-d');

	$isAccessActivityExpense=user_access('access activity expense') || $isOwner;

	if (!$isAccessActivityExpense) return message('error','access denied');

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
	$ret.='<form id="project-develop" class="form-report" method="get" action="'.url('project/report/exptran').'">';
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
		$dbs = mydb::select(
			'SELECT DISTINCT SUBSTR(`distid`, 3, 2) `ampurId`, `distname` `ampurName` FROM %co_district% WHERE LEFT(`distid`, 2) = :prov ORDER BY CONVERT(`ampurName` USING tis620) ASC',
			[':prov' => $prov]
		);
		foreach ($dbs->items as $item) {
			$ret.='<option value="'.$item->ampurId.'" '.($item->ampurId == $ampur?'selected="selected"':'').'>'.$item->ampurName.'</option>';

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

	$ret.='<li class="ui-nav">ช่วงวันที่ <input type="text" name="fromdate" class="form-text sg-datepicker" size="8" value="'.sg_date($fromDate,'d/m/Y').'" />-<input type="text" name="todate" class="form-text sg-datepicker" size="8" value="'.sg_date($toDate,'d/m/Y').'" /></li>';

	// Select status
	//$ret.='<select class="form-select"><option>==ทุกสถานะ==</option></select> ';
	$ret.='<li class="ui-nav">&nbsp;&nbsp;<button type="submit" class="btn -primary"><i class="icon -material">search</i><span>ดูรายงาน</span></button></li>';
	$ret.='</ul></form>';
	$ret.='</nav>';

	mydb::where('t1.`formid`="activity" AND t1.`part`="owner" AND t1.`num7`>0');
	mydb::where('t1.`date1` BETWEEN :fromdate AND :todate',':fromdate',$fromDate,':todate',$toDate);
	if ($prset) mydb::where('p.`projectset`=:prset',':prset',$prset);
	if ($year) mydb::where('p.`pryear`=:year',':year',$year);
	if ($prov) mydb::where('LEFT(t.`areacode`, 2) = :changwat', ':changwat',$prov);
	if ($ampur) mydb::where('SUBSTR(t.`areacode`, 3, 2) = :ampur', ':ampur',$ampur);
	if ($fundid) mydb::where('d.fundid=:fundid', ':fundid',$fundid);


	$label='CONCAT("จังหวัด",`provname`)';
	if ($prov) $label='`title`';
	else if ($ampur) $label='CONCAT("กองทุนตำบล",f.`fundname`)';
	else if ($prov) $label='CONCAT("อำเภอ",cod.`distname`)';

	$stmt = 'SELECT
		cop.`provname`
		, t1.`date1`
		, c.`title` `calTitle`
		, t.`title`
		, t1.`tpid`
		, p.`budget`
		, t1.`num1` expGr1
		, t1.`num2` expGr2
		, t1.`num3` expGr3
		, t1.`num4` expGr4
		, t1.`num5` expGr5
		, t1.`num6` expGr6
		, t1.`num7` expTotal
		FROM %project% p
			LEFT JOIN %project_tr% t1 USING(`tpid`) LEFT JOIN `sgz_topic` t USING(`tpid`)
			LEFT JOIN %calendar% c ON c.`id`=t1.`calid`
			LEFT JOIN %co_province% cop ON cop.`provid` = LEFT(t.`areacode`, 2)
			LEFT JOIN %co_district% cod ON cod.`distid` = LEFT(t.`areacode`, 4)
		%WHERE%
		ORDER BY `date1` ASC
		';

	$dbs = mydb::select($stmt,$where['value']);

	$expGroupList=model::get_category('project:expgr','catid');

	$tables = new Table();
	$tables->thead = array(
		'date' => 'วันที่',
		'title' => 'กิจกรรม/โครงการ',
		'total gr1' => 'ค่าตอบแทน',
		'total gr2' => 'ค่าจ้าง',
		'total gr3' => 'ค่าใช้สอย',
		'total gr4' => 'ค่าวัสดุ',
		'total gr5' => 'ค่าสาธารณูปโภค',
		'total gr6' => 'อื่น ๆ',
		'total gr7' => 'รวมจ่าย',
	);

	$subTotal=$totalBudget=0;
	$totalGr1=$totalGr2=$totalGr3=$totalGr4=$totalGr5=$totalGr6=0;
	foreach ($dbs->items as $rs) {
		$expSum=$rs->expGr1+$rs->expGr2+$rs->expGr3+$rs->expGr4+$rs->expGr5+$rs->expGr6;

		$tables->rows[]=array(
			$rs->date1?sg_date($rs->date1,'ว ดด ปปปป'):'',
			$rs->calTitle.'<br />'.$rs->title,
			$rs->expGr1==0?'-':number_format($rs->expGr1,2),
			$rs->expGr2==0?'-':number_format($rs->expGr2,2),
			$rs->expGr3==0?'-':number_format($rs->expGr3,2),
			$rs->expGr4==0?'-':number_format($rs->expGr4,2),
			$rs->expGr5==0?'-':number_format($rs->expGr5,2),
			$rs->expGr6==0?'-':number_format($rs->expGr6,2),
			'<span class="'.($expSum!=$rs->expTotal?'error':'').'">'.number_format($rs->expTotal,2).'</span>',
		);

		$totalGr1+=$rs->expGr1;
		$totalGr2+=$rs->expGr2;
		$totalGr3+=$rs->expGr3;
		$totalGr4+=$rs->expGr4;
		$totalGr5+=$rs->expGr5;
		$totalGr6+=$rs->expGr6;
	}
	$totalAll=$totalGr1+$totalGr2+$totalGr3+$totalGr4+$totalGr5+$totalGr6;

	$tables->tfoot[1]=array(
		'',
		'รวม',
		number_format($totalGr1,2),
		number_format($totalGr2,2),
		number_format($totalGr3,2),
		number_format($totalGr4,2),
		number_format($totalGr5,2),
		number_format($totalGr6,2),
		number_format($totalAll,2),
	);

	$tables->tfoot[2]=array(
		'',
		'สัดส่วน(%)',
		round($totalGr1*100/$totalAll,2).'%',
		round($totalGr2*100/$totalAll,2).'%',
		round($totalGr3*100/$totalAll,2).'%',
		round($totalGr4*100/$totalAll,2).'%',
		round($totalGr5*100/$totalAll,2).'%',
		round($totalGr6*100/$totalAll,2).'%',
		'',
	);

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
	.item td:nth-child(n+3) {text-align:center;}
	.item tfoot td {padding-left:8px;}
	.error {color:red;}
	</style>';
	return $ret;
}
?>