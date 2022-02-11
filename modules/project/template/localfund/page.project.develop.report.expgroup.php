<?php
/**
* Project Local Report From บัณฑิตอาสา
*
* @param Object $self
* @return String
*/

function project_develop_report_expgroup($self) {
	project_model::set_toolbar($self,'รายงานงบประมาณโครงการพัฒนาแยกตามหมวด');

	$prov=post('prov');
	$ampur=post('ampur');
	$year=post('year');
	$fundid=post('fund');
	$status=post('status');

	$yearList=mydb::select('SELECT DISTINCT `pryear` FROM %project_dev% ORDER BY `pryear` ASC')->lists->text;

	$ret.='<div class="toolbar -sub">';
	$ret.='<form id="project-develop" method="get" action="'.url('project/develop/report/expgroup').'">';
	$ret.='<ul>';
	// Select province
	$ret.='<li><select class="form-select" name="prov"><option value="">==ทุกจังหวัด==</option>';
	$provDb=mydb::select('SELECT `changwat`,`provname`,COUNT(*) FROM %topic% t LEFT JOIN %co_province% cop ON cop.`provid`=t.`changwat` WHERE t.`type`="project-develop" GROUP BY `changwat` HAVING `provname`!="" ORDER BY CONVERT(`provname` USING tis620) ASC');
	foreach ($provDb->items as $item) {
		$ret.='<option value="'.$item->changwat.'" '.($item->changwat==$prov?'selected="selected"':'').'>'.$item->provname.'</option>';
	}
	$ret.='</select></li>';

	// Select ampur
	if ($prov) {
		$ret.='<select class="form-select" name="ampur" id="input-ampur"><option value="">==ทุกอำเภอ==</option>';
		$stmt='SELECT DISTINCT `ampur`,`nameampur` FROM %project_fund% WHERE `changwat`=:prov ORDER BY CONVERT(`nameampur` USING tis620) ASC';
		$dbs=mydb::select($stmt,':prov',$prov);
		foreach ($dbs->items as $item) {
			$ret.='<option value="'.$item->ampur.'" '.($item->ampur==$ampur?'selected="selected"':'').'>'.$item->nameampur.'</option>';
			
		}
		$ret.='</select>';
	}

	// Select year
	if (strpos(',', $yearList)) {
		$ret.='<li><select class="form-select" name="year" id="develop-year"><option value="">==ทุกปี==</option>';
		foreach (explode(',',$yearList) as $item) {
			$ret.='<option value="'.$item.'" '.($item==$year?'selected="selected"':'').'>พ.ศ. '.($item+543).'</option>';
		}
		$ret.='</select></li>';
	} else {
		$ret.='<input type="hidden" name="year" value="'.$yearList.'" />';
	}

	// Select status
	//$ret.='<select class="form-select"><option>==ทุกสถานะ==</option></select> ';
	$ret.='<li>&nbsp;&nbsp;<input type="submit" class="button" value="ดู" /></li>';
	$ret.='</ul></form>';
	$ret.='</div>';

	$where=array();
	$where=sg::add_condition($where,'t1.`formid`="develop" AND t1.`part`="exptr"');
	if ($year) $where=sg::add_condition($where,'d.`pryear`=:year','year',$year);	
	if ($year) $where=sg::add_condition($where,'d.`pryear`=:year','year',$year);
	if ($prov) $where=sg::add_condition($where, 'd.changwat=:changwat', 'changwat',$prov);
	if ($ampur) $where=sg::add_condition($where, 'd.ampur=:ampur', 'ampur',$ampur);
	if ($fundid) $where=sg::add_condition($where, 'd.fundid=:fundid', 'fundid',$fundid);
	if (post('q')) $where=sg::add_condition($where, 't.`title` LIKE :search OR r.`email` LIKE :search', 'search','%'.post('q').'%');
	$whereCond=$where?'WHERE '.implode(' AND ',$where['cond']):'';


	$label='CONCAT("จังหวัด",cop.`provname`)';
	if ($fundid) $label='t.`title`';
	else if ($ampur) $label='CONCAT("กองทุนตำบล",f.`fundname`)';
	else if ($prov) $label='CONCAT("อำเภอ",cod.`distname`)';
	/*
	$stmt='SELECT '.$label.' `label`
						, d.`changwat`
						, cop.`provname`
						, d.`ampur`
						, cod.`distname`
						, d.`status`
						, d.`pryear`
						, f.`fundid`
						, f.`fundname`
						, COUNT(*) amt
					FROM (
						SELECT t1.`catid` expGroupID,t1.`name` expGroupName %tags% WHERE `taggroup`="project:expgr"
						) eg
					--	LEFT JOIN %project_tr% 
					--	LEFT JOIN %project_dev% d ON eg.`
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %project_fund% f USING(`fundid`)
						LEFT JOIN %co_province% cop ON cop.`provid`=d.`changwat`
						LEFT JOIN %co_district% cod ON cod.`distid`=CONCAT(d.`changwat`,d.`ampur`)
					'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
					GROUP BY `label`,`status`
					ORDER BY `provname` ASC, `status` ASC';
					*/
	$stmt="SELECT
			$label `label`
			, t1.`tpid`, d.`changwat`, d.`ampur`, d.`fundid`
	    , SUM(IF(ec.`catparent`=1,t1.`num4`,0)) expGr1
	    , SUM(IF(ec.`catparent`=2,t1.`num4`,0)) expGr2
	    , SUM(IF(ec.`catparent`=3,t1.`num4`,0)) expGr3
	    , SUM(IF(ec.`catparent`=4,t1.`num4`,0)) expGr4
	    , SUM(IF(ec.`catparent`=5,t1.`num4`,0)) expGr5
	    , SUM(IF(ec.`catparent`=6,t1.`num4`,0)) expGr6
	FROM sgz_project_tr t1
		LEFT JOIN %project_dev% d USING(`tpid`)
		LEFT JOIN %topic% t USING(`tpid`)
		LEFT JOIN %tag% ec ON ec.`taggroup`='project:expcode' AND ec.`catid`=t1.`gallery`
		LEFT JOIN %project_fund% f USING(`fundid`)
		LEFT JOIN %co_province% cop ON cop.`provid`=d.`changwat`
		LEFT JOIN %co_district% cod ON cod.`distid`=CONCAT(d.`changwat`,d.`ampur`)
	$whereCond
	GROUP BY `label`";

	$dbs=mydb::select($stmt,$where['value']);
	//$ret.=print_o($dbs,'$dbs');

	$expGroupList=model::get_category('project:expgr','catid');

	$tables = new Table();
	$tables->addClass('project-develop-status');
	$tables->thead['prov']='พื้นที่';
	$tables->tfoot[1]['prov']='รวม';
	foreach ($expGroupList as $key=>$value) {
		$tables->thead[$key]=$value;
		$tables->tfoot[1][$key]=0;
	}
	$tables->thead['total']='รวม';
	$tables->tfoot[1]['total']=0;

	$subTotal=0;
	$totalGr1=$totalGr2=$totalGr3=$totalGr4=$totalGr5=$totalGr6=0;
	foreach ($dbs->items as $rs) {
		$subTotal=$rs->expGr1+$rs->expGr2+$rs->expGr3+$rs->expGr4+$rs->expGr5+$rs->expGr6;
		$tables->rows[]=array(
											$fundid?$rs->label:'<a href="'.url('project/develop/report/expgroup',array('prov'=>$rs->changwat,'ampur'=>empty($prov)?NULL:$rs->ampur,'fund'=>empty($ampur)?NULL:$rs->fundid,'year'=>$year)).'">'.$rs->label.'</a>',
											number_format($rs->expGr1,2),
											number_format($rs->expGr2,2),
											number_format($rs->expGr3,2),
											number_format($rs->expGr4,2),
											number_format($rs->expGr5,2),
											number_format($rs->expGr6,2),
											number_format($subTotal,2),
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
							'สัดส่วน(%)',
							round($totalGr1*100/$totalAll).'%',
							round($totalGr2*100/$totalAll).'%',
							round($totalGr3*100/$totalAll).'%',
							round($totalGr4*100/$totalAll).'%',
							round($totalGr5*100/$totalAll).'%',
							round($totalGr6*100/$totalAll).'%',
							round($totalAll*100/$totalAll).'%',
							);

	$ret .= $tables->build();

	$ret.='<p>หมายเหตุ <ul><li>คลิกบนตัวเลขจำนวนโครงการในตารางเพื่อดูรายชื่อโครงการ</li></ul></p>';
	//		$ret.=print_o($tables->tfoot,'$tfoot');
	//		$ret.=print_o($tables,'$tables');

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