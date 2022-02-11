<?php
/**
* Project Develope Home
*
* @param Object $self
* @return String
*/

function project_develop_home($self) {
	page_class('-develop');
	$year=post('year');
	$prov=post('prov');
	$ampur=post('ampur');

	R::View('project.toolbar',$self,'พัฒนาโครงการ'.($year?'ของปี '.($year+543):'ของทุกปี'),'develop');


	$yearList=mydb::select('SELECT DISTINCT `pryear` FROM %project_dev% ORDER BY `pryear` ASC')->lists->text;

	$ret.='<nav class="nav -page">';
	$ret.='<form id="project-develop" method="get" action="'.url('project/develop/list').'">';
	// Select province
	$ret.='<select class="form-select" name="prov"><option value="">==ทุกจังหวัด==</option>';
	$provDb=mydb::select('SELECT `changwat`,`provname`,COUNT(*) FROM %topic% t LEFT JOIN %co_province% cop ON cop.`provid`=t.`changwat` WHERE t.`type`="project-develop" GROUP BY `changwat` HAVING `provname`!="" ORDER BY CONVERT(`provname` USING tis620) ASC');
	foreach ($provDb->items as $item) {
		$ret.='<option value="'.$item->changwat.'" '.($item->changwat==$prov?'selected="selected"':'').'>'.$item->provname.'</option>';
	}
	$ret.='</select> ';

	// Select ampur
	if ($prov) {
		$ret.='<select class="form-select" name="ampur" id="input-ampur"><option value="">==ทุกอำเภอ==</option>';
		$stmt='SELECT DISTINCT `ampur`,`nameampur` FROM %project_fund% WHERE `changwat`=:prov ORDER BY CONVERT(`nameampur` USING tis620) ASC';
		$dbs=mydb::select($stmt,':prov',$prov);
		foreach ($dbs->items as $item) {
			$ret.='<option value="'.$item->ampur.'" '.($item->ampur==$ampur?'selected="selected"':'').'>'.$item->nameampur.'</option>';
			
		}
		$ret.='</select> ';
	}

	// Select year
	if (strpos(',', $yearList)) {
		$ret.='<select class="form-select" name="year" id="develop-year"><option value="">==ทุกปี==</option>';
		foreach (explode(',',$yearList) as $item) {
			$ret.='<option value="'.$item.'" '.($item==$year?'selected="selected"':'').'>พ.ศ. '.($item+543).'</option>';
		}
		$ret.='</select> ';
	} else {
		$ret.='<input type="hidden" name="year" value="'.$yearList.'" />';
	}

	// Select status
	//$ret.='<select class="form-select"><option>==ทุกสถานะ==</option></select> ';
	$ret.=' <button class="btn -primary" type="submit">ดูรายชื่อ</button>';
	$ret.='</form>';
	$ret.='</nav>';

	$where=array();
	if ($year) $where=sg::add_condition($where,'d.`pryear`=:year','year',$year);
	if ($prov) $where=sg::add_condition($where, 'd.changwat=:changwat', 'changwat',$prov);
	if ($ampur) $where=sg::add_condition($where, 'd.ampur=:ampur', 'ampur',$ampur);
	if (post('q')) $where=sg::add_condition($where, 't.`title` LIKE :search', 'search','%'.post('q').'%');

	$label='CONCAT("จังหวัด",cop.`provname`)';
	if ($ampur) $label='CONCAT("กองทุนตำบล",f.`fundname`)';
	else if ($prov) $label='CONCAT("อำเภอ",cod.`distname`)';
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
					FROM %project_dev% d
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %project_fund% f USING(`fundid`)
						LEFT JOIN %co_province% cop ON cop.`provid`=d.`changwat`
						LEFT JOIN %co_district% cod ON cod.`distid`=CONCAT(d.`changwat`,d.`ampur`)
					'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
					GROUP BY `label`,`status`
					ORDER BY `provname` ASC, `status` ASC';
	$dbs=mydb::select($stmt,$where['value']);

	$tables = new Table();
	$tables->addClass('project-develop-status');
	$tables->thead['prov']='พื้นที่';
	$tables->tfoot[1]['prov']='รวม';
	foreach (project_base::$statusList as $key=>$value) {
		$tables->thead[$key]=$value;
		$tables->tfoot[1][$key]=0;
	}
	$tables->thead['total']='รวม';
	$tables->tfoot[1]['total']=0;

	foreach ($dbs->items as $rs) {
		$data[$rs->label][$rs->status]=$rs;
	}

	foreach ($data as $rs) {
		$rs=reset($rs);
		unset($row);
		$row['prov']=$ampur ? $rs->label : '<a href="'.url('project/develop',array('prov'=>$rs->changwat,'ampur'=>empty($prov)?NULL:$rs->ampur)).'">'.$rs->label.'</a>';
		foreach (project_base::$statusList as $key=>$value) $row[$key]='-';
		$row['total']=0;
		$tables->rows[$rs->label]=$row;
	}
	foreach ($dbs->items as $rs) {
		$tables->rows[$rs->label][$rs->status]='<a href="'.url('project/develop/list',array('prov'=>$rs->changwat,'ampur'=>empty($prov)?NULL:$rs->ampur,'fund'=>empty($ampur)?NULL:$rs->fundid,'status'=>$rs->status,'year'=>$year)).'">'.$rs->amt.'</a>';
		$tables->rows[$rs->label]['total']+=$rs->amt;
		$tables->tfoot[1][$rs->status]+=$rs->amt;
		$tables->tfoot[1]['total']+=$rs->amt;
	}

	$total=$tables->tfoot[1]['total'];
	$total_reg=$tables->tfoot[1][-1];
	$total_develop=$tables->tfoot[1][1];
	$total_2=$tables->tfoot[1][2];
	$total_3=$tables->tfoot[1][3];
	$total_pass=$tables->tfoot[1][5];
	$total_notpass=$tables->tfoot[1][8];
	$total_cancel=$tables->tfoot[1][9];
	$total_process=$tables->tfoot[1][10];

	foreach ($tables->rows as $key=>$row) {
		$tables->rows[$key]['total']='<a href="'.url('project/develop/list',array('prov'=>NULL,'ampur'=>NULL,'year'=>$year)).'">'.$row['total'].'</a>';
	}
	foreach ($tables->tfoot[1] as $key=>$value) {
		if (in_array($key, array('prov'))) continue;
		$tables->tfoot[1][$key]=$value>0 ? '<a href="'.url('project/develop/list',array('prov'=>$prov,'ampur'=>$ampur,'status'=>$key=='total'?NULL:$key,'year'=>$year)).'">'.$value.'</a>' : '-';
	}
	$tables->tfoot[2]=array('',
							round($total_reg*100/$total).'%',
							round($total_develop*100/$total).'%',
							round($total_2*100/$total).'%',
							round($total_3*100/$total).'%',
							round($total_pass*100/$total).'%',
							round($total_notpass*100/$total).'%',
							round($total_cancel*100/$total).'%',
							round($total_process*100/$total).'%',
							'100%',
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