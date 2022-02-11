<?php
/**
* Project owner
*
* @param Object $self
* @param Int $orgid
* @param String $action
* @param Int $tranId
* @return String
*/
function project_org($self, $orgid = NULL, $action = NULL, $tranId = NULL) {
	project_model::set_toolbar($self,'รายชื่อองค์กร');

	$orgid=post('org');//SG\getFirst(post('org'),1);
	$suborg=post('sorg');
	$govplan=post('gp');
	$southplan=post('sp');

	$stmt='SELECT o.`orgid`, o.`name`, IF(t.`tpid` IS NULL,0,COUNT(*)) amt
		FROM %db_org% o
			LEFT JOIN %topic% t USING(`orgid`)
		WHERE o.`parent` IS NULL
		GROUP BY `orgid`
		ORDER BY
			CASE
				WHEN `orgid`=1 THEN 0
				WHEN `orgid`!=1 THEN CONVERT(`name` USING tis620)
			END ASC';
	$dbs=mydb::select($stmt);

	$sidebar.='<form id="project-select" method="get" action="'.url('project/org').'">';
	$sidebar.='<select name="org" class="form-select"><option value="">==เลือกองค์กร==</option>';
	foreach ($dbs->items as $rs) {
		$sidebar.='<option value="'.$rs->orgid.'" '.($rs->orgid==$orgid?'selected="selected"':'').'>'.$rs->name.' ('.$rs->amt.')</option>';
	}
	$sidebar.='</select>';
	if ($orgid) {
		$sidebar.='<div class="box">';
		$stmt='SELECT o.`orgid`, o.`name`, IF(t.`tpid` IS NULL,0,COUNT(*)) amt
			FROM %db_org% o
				LEFT JOIN %topic% t USING(`orgid`)
			WHERE o.`parent`=:orgid
			GROUP BY `orgid`
			ORDER BY CONVERT(`name` USING tis620) ASC';
		$cdbs=mydb::select($stmt,':orgid',$orgid);
		$sidebar.='<select name="sorg" class="form-select"><option value="">--ทุกหน่วยงาน--</option>';
		foreach ($cdbs->items as $srs) {
			$sidebar.='<option value="'.$srs->orgid.'" '.($suborg==$srs->orgid?'selected="selected"':'').'>'.$srs->name.' ('.$srs->amt.')</option>';
		}
		$sidebar.='</select>';
		if ($orgid) {
			$relDbs=mydb::select('SELECT * FROM %tag% WHERE `taggroup`="project:rel-govplan"');
			$sidebar.='<select name="gp" class="form-select"><option value="">ทุกภาระกิจ</option>';
			foreach ($relDbs->items as $item) {
				$sidebar.='<option value="'.$item->tid.'" '.($govplan==$item->tid?'selected="selected"':'').'>'.$item->name.'</option>';
			}
			$sidebar.='</select>';

			$relDbs=mydb::select('SELECT * FROM %tag% WHERE `taggroup`="project:rel-southplan"');
			$sidebar.='<select name="sp" class="form-select"><option value="">ทุกแผนงาน</option>';
			foreach ($relDbs->items as $item) {
				$sidebar.='<option value="'.$item->tid.'" '.($southplan==$item->tid?'selected="selected"':'').'>'.$item->name.'</option>';
			}
			$sidebar.='</select>';

			//$ret.='<select class="form-select"><option>ทุกแผนงาน</option></select>';
		}
		//$sidebar.=print_o(post(),'post');
		$sidebar.='</div>';
		//$sidebar.=print_o($cdbs);
	}
	$sidebar.='<input type="submit" class="button floating" value="ดู" />';
	$sidebar.='</form><ul><li><a href="'.url('project').'">แผนที่ภาพรวมโครงการ</a></li></ul>';
	$self->theme->sidebar=$sidebar;
	
	$ret.='
	<script>
	$("#project-select select[name=org]").change(function() {
		$("select[name=sorg]").empty()
	});
	$(".sidebar select").change(function() {
		$("#project-select").submit()
	});
	</script>';


	$ret.='<div id="info" class="info">';

	$project_org=SG\getFirst($suborg,$orgid);
	$stmt='SELECT o.`orgid`, o.`name`, COUNT(*) amt, SUM(`budget`) budgets
		FROM %db_org% o
			LEFT JOIN %topic% t USING(`orgid`)
			LEFT JOIN %project% USING(`tpid`)
		WHERE o.`parent`=:orgid
		GROUP BY o.`orgid`
		ORDER BY CONVERT(`name` USING tis620) ASC';
	$dbs=mydb::select($stmt,':orgid',$project_org);
	if ($dbs->_num_rows) {
		$no=0;

		$tables = new Table();
		$tables->thead=array('no'=>'','หน่วยงานในสังกัด','amt'=>'โครงการ','money'=>'งบประมาณ(บาท)');
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(++$no,'<a href="'.url('project/org',array('org'=>$orgid,'sorg'=>$rs->orgid,'gp'=>$govplan,'sp'=>$southplan)).'">'.$rs->name.'</a>',$rs->amt,number_format($rs->budgets,2));
		}
		$ret.='<h3>หน่วยงานในสังกัด</h3>'.$tables->build();
	}

	$ret .= __project_org_list($project_org)->build();

	if ($project_org) {
		$where=array();
		if ($project_org) $where=sg::add_condition($where,'t.`orgid`=:orgid AND p.`prtype` IN ("โครงการ")','orgid',$project_org);
		if ($govplan) $where=sg::add_condition($where,'gp.`parent`=:govplan','govplan',$govplan);
		if ($southplan) $where=sg::add_condition($where,'sp.`parent`=:southplan','southplan',$southplan);
		$stmt='SELECT DISTINCT t.`tpid`, t.`title`, p.`budget`
			FROM %project% p
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %project_tr% gp
					ON gp.`tpid`=p.`tpid`
						AND gp.`formid`="info"
						AND gp.`part`="rel"
				LEFT JOIN %project_tr% sp
					ON sp.`tpid`=p.`tpid`
						AND sp.`formid`="info"
						AND sp.`part`="rel"
			'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
			ORDER BY CONVERT(t.`title` USING tis620) ASC
			';
		$dbs=mydb::select($stmt,$where['value']);
		//$dbs=mydb::select($stmt,':orgid',$project_org);
		if ($dbs->_num_rows) {
			unset($tables);
			$no=0;
			$tables = new Table();
			$tables->thead=array('no'=>'','กิจกรรม/โครงการ','money'=>'งบประมาณ(บาท)');
			foreach ($dbs->items as $rs) {
				$tables->rows[]=array(++$no,'<a href="'.url('paper/'.$rs->tpid).'">'.$rs->title.'</a>',number_format($rs->budget,2));
			}
			$ret.='<h3>กิจกรรม/โครงการ '.$dbs->_num_rows.' โครงการ</h3>'.$tables->build();
		} else $ret.='<p class="notify">ไม่มีโครงการตามเงื่อนไข</p>';
	}

	//$ret.=print_o($dbs,'$dbs');

	$ret.='</div>';




	if (!$self->theme->title) $self->theme->title='รายชื่อองค์กร';
	$ret.='<style>
	.col-prtype {width:10em;white-space:nowrap;text-align:center;}
	.col-money {width:10em;text-align:right;}
	table.item table.item td:last-child {text-align:right;}
	tr.bold {font-weight:bold;}
	</style>';

	return $ret;
}

function __project_org_list($orgid,$parent=0) {
	$stmt='SELECT t.`tpid`, t.`title`, p.`budget`, tp.`parent`,p.`prtype`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
			LEFT JOIN %topic_parent% tp USING(`tpid`)
		WHERE '.($orgid?'t.`orgid`=:orgid AND ':'').'tp.`parent`=:parent';
	$dbs=mydb::select($stmt,':orgid',$orgid,':parent',$parent);
	//print_o($dbs,'$dbs',1);
	if ($dbs->_num_rows) {
		$no=0;

		$tables = new Table();
		if ($parent==0) $tables->thead=array('','แผนงาน/ชุดโครงการ/โครงการ','money'=>'งบประมาณ(บาท)');
		foreach ($dbs->items as $rs) {
			$budget=number_format($rs->budget,2);
			$tables->rows[]=array(++$no,'<a href="'.url('paper/'.$rs->tpid).'">'.$rs->title.'</a> ('.$rs->prtype.')',$parent==0?'<strong>'.$budget.'</strong>':$budget,'config'=>array('class'=>$parent==0?'bold':''));
		}
	}
	return $tables;
}
?>