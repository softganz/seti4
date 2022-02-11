<?php
/**
 * OrgDg report สถานะ
 *
 */
function org_report_status($self) {
	$gt=post('gt');
	$org=post('org');
	$myorg=SG\getFirst(org_model::get_my_org(),0);

	if (!$gt) {
		$ret.='<div class="sg-tabs"><ul class="tabs"><li class="-active"><a href="'.url('org/report/status',array('gt'=>'year')).'">กราฟรายปี</a></li><li><a href="'.url('org/report/status',array('gt'=>'month')).'">กราฟรายเดือน</a></li><li><a href="'.url('org/report/status',array('gt'=>'table')).'">ตาราง</a></li></ul>';
		$ret.='<div>';
	}

	$where=array();
	if ($org) $where=sg::add_condition($where,'o.`orgid`=:orgid','orgid',$org);
	else $where=sg::add_condition($where,'(o.`orgid` IN (:myorg) OR o.`uid`=:uid)','myorg','SET:'.$myorg,'uid',i()->uid);
	$stmt='SELECT COUNT(*) total FROM %org_ojoin% o
					'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
					LIMIT 1';
	$all_org=mydb::select($stmt,$where['value'])->total;
//		$all_org=db_count('%org_ojoin%');
//		$all_member=db_count('%org_mjoin%');

	$stmt='SELECT COUNT(*) total FROM %org_mjoin% o
					'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
					LIMIT 1';
	$all_member=mydb::select($stmt,$where['value'])->total;

	$ret .= '<p>จำนวนองค์กรทั้งหมด <strong>'.$all_org.'</strong> องค์กร จำนวนสมาชิกทั้งหมด <strong>'.$all_member.'</strong> คน';

	// จำนวนสมาชิกเข้าใหม่แต่ละเดือน
	$stmt = 'SELECT DATE_FORMAT(joindate,"%Y-%m") AS jointmonth,count(*) AS peoples
								FROM %org_mjoin% o
							'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
								GROUP BY DATE_FORMAT(joindate,"%Y-%m") ';
	$dbs=mydb::select($stmt,$where['value']);
	foreach ($dbs->items as $rs) $summary[$rs->jointmonth]['peoples']=$rs->peoples;

	// จำนวนองค์กรเข้าใหม่แต่ละเดือน
	$stmt = 'SELECT DATE_FORMAT(joindate,"%Y-%m") AS joindate,count(*) AS orgs
								FROM %org_ojoin% o
							'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
								GROUP BY DATE_FORMAT(joindate,"%Y-%m") ';
	$dbs=mydb::select($stmt,$where['value']);
	foreach ($dbs->items as $rs) $summary[$rs->joindate]['orgs']=$rs->orgs;
	ksort($summary);
	$keys = array_keys($summary);

	$graph->title='จำนวนองค์กร-สมาชิกเข้าใหม่';
	$data[]=array('เดือน-ปี','จำนวนคน','จำนวนองค์กร');
	$minMonth=key($summary);
	$maxMonth=end($keys);
	$gType=post('gt');
	for ($month=$minMonth;$month<=$maxMonth;true) {
		if ($gType=='month') {
			$data[]=array(sg_date($month.'-01','ดด ปป'),intval($summary[$month]['peoples']),intval($summary[$month]['orgs']));
		} else {
			list($year)=explode('-', $month);
			$year+=543;
			$data[$year]=array($year.'',
														$data[$year][1]+intval($summary[$month]['peoples']),
														$data[$year][2]+intval($summary[$month]['orgs']));
		}
		if ($month=='0000-00') {
			foreach ($keys as $k) {
				if ($k>$month) {
					$month=$k;
					break;
				}
			}
		}
		list($y,$m)=explode('-',$month);
		$month=date('Y-m',mktime(0,0,0,$m+1,1,$y+0));
	}
	$graph->items=array_values($data);
	$chartTypes=array('bar'=>'BarChart','pie'=>'PieChart','col'=>'ColumnChart','line'=>'LineChart');
	$graphType='col';
	$ret.='<div id="chart_div" style="width: 100%; height: 400px;"></div>';
	$ret.='<script type="text/javascript">
			google.load("visualization", "1", {packages:["corechart"], callback: drawChart});

			google.setOnLoadCallback(drawChart);
			function drawChart() {
				var data = google.visualization.arrayToDataTable('.json_encode($graph->items).');
				var options = {title: "'.$graph->title.'",
													vAxes: {	0: {logScale: false},
																		1: {logScale: false}},
													series: {
																		0:{targetAxisIndex:0},
																		1:{targetAxisIndex:1},
																		2:{targetAxisIndex:1}
													}
												};
				var chart = new google.visualization.'.$chartTypes[$graphType].'(document.getElementById("chart_div"));
				chart.draw(data, options);
			}
		</script>';
	//		$ret.=print_o($graph,'$graph');
	//		$ret.=print_o($summary,'$summary');

	// start report
		if (post('gt')=='table') {
		$tables = new Table();
		$tables->caption=$self->theme->title;
		$tables->header=array('date'=>'เดือน-ปี','amt amt_org'=>'จำนวนองค์กร','amt amt_member'=>'จำนวนคน');
		foreach ($summary as $register_month=>$item) {
			$month=substr($register_month,5,2).'-'.substr($register_month,0,4);
			$total_org+=$item['orgs'];
			$total_member+=$item['peoples'];
			$tables->rows[]=array(sg_date($register_month.'-01','ดดด ปปปป'),
												'<a href="'.url('org/report/status',array('gt'=>$gt,'show'=>'org','month'=>$month),null,'report').'">'.($item['orgs']?$item['orgs']:'-').'</a>',
												'<a href="'.url('org/report/status',array('gt'=>$gt,'show'=>'name','month'=>$month),null,'report').'">'.($item['peoples']?$item['peoples']:'-').'</a>'
												);
		}
		$tables->tfoot[]=array('รวม',$total_org,$total_member);
		$ret .= $tables->build();
		$ret.='คลิกบนจำนวนองค์กรเพื่อดูรายชื่อองค์กร หรือ จำนวนคนเพื่อดูรายชื่อของสมาชิกในแต่ละเดือน';
	}
	if (!$gt) $ret.='</div></div>';
	return $ret;		$self->theme->title=$title;

	$stmt='SELECT d.`tpid` , t.`title` , COUNT( * ) amt
					FROM %org_doings% d
						LEFT JOIN %topic% t USING(`tpid`)
					WHERE d.`tpid` IS NOT NULL
					GROUP BY d.`tpid` ';
	$dbs=mydb::select($stmt);

	$tablesName = new Table();
	$tablesName->thead=array('no'=>'ลำดับ','โครงการ','amt'=>'จำนวนกิจกรรม');
	$no=0;
	foreach ($dbs->items as $rs) {
		$tablesName->rows[]=array(
															++$no,
															'<a href="'.url('org/'.$rs->orgid.'/meeting','tpid='.$rs->tpid).'">'.$rs->title.'</a>',
															number_format($rs->amt),
															);
	}
	$ret .= $tablesName->build();
	return $ret;
}
?>