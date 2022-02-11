<?php
/**
* Project owner
*
* @param Object $self
* @return String
*/
function project_report_subproject($self) {
	R::View('project.toolbar', $self, 'รายงานกิจกรรมแผนงาน/ชุดโครงการ', 'report');

	$tpid=post('id');
	$changwat=post('pv');//SG\getFirst(post('org'),1);
	$ampur=post('ap');
	$tambon=post('tb');
	$village=post('vi');
	$year=post('year');
	$month=post('month');
	$groupBy=SG\getFirst(post('gr'),'area');

	$sidebar.='<form id="project-select" method="get" action="'.url('project/report/subproject').'">'._NL;
	$sidebar.='<div class="box"><h3>เงื่อนไข</h3>';

	// เลือกชุดโครงการ/แผนงาน
	$stmt='SELECT p.`tpid`, t.`title`, COUNT(DISTINCT tp.`tpid`) amt
					FROM %project% p
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %topic_parent% tp ON tp.`parent`=p.`tpid`
					WHERE p.`prtype` IN ("แผนงาน","ชุดโครงการ")
					GROUP BY p.`tpid`
					HAVING `amt`>0
					ORDER BY CONVERT(t.`title` USING tis620) ASC';
	$dbsSubProject=mydb::select($stmt);
	$sidebar.='<select name="id" class="form-select"><option value="">-- เลือกชุดโครงการ --</option>'._NL;
	foreach ($dbsSubProject->items as $rs) {
		$sidebar.='<option value="'.$rs->tpid.'" '.($rs->tpid==$tpid?'selected="selected"':'').'>'.$rs->title.' ('.$rs->amt.' โครงการย่อย)</option>'._NL;
	}
	$sidebar.='</select>'._NL;

	// เลือกจังหวัด
	if ($tpid) {
		$stmt='SELECT
						tp.`tpid`
						, pv.`changwat`, cop.`provname`
						, COUNT(DISTINCT tp.`tpid`) amt
						FROM %topic_parent% tp
							LEFT JOIN %project_prov% pv ON pv.`tpid`=tp.`tpid`
							LEFT JOIN %co_province% cop ON cop.`provid`=pv.`changwat`
						WHERE tp.`parent`=:tpid AND pv.`changwat` IS NOT NULL
						GROUP BY pv.`changwat`
						ORDER BY CONVERT(cop.`provname` USING tis620) ASC
						';
		$dbs=mydb::select($stmt,':tpid',$tpid);
		$sidebar.='<select name="pv" class="form-select"><option value="">-- ทุกจังหวัด --</option>'._NL;
		foreach ($dbs->items as $rs) {
			$sidebar.='<option value="'.$rs->changwat.'" '.($rs->changwat==$changwat?'selected="selected"':'').'>'.$rs->provname.' ('.$rs->amt.' โครงการ)</option>'._NL;
		}
		$sidebar.='</select>'._NL;
	}

	if ($changwat) {
		$stmt='SELECT
							cod.*
						FROM %co_district% cod
						WHERE LEFT(cod.`distid`,2)=:changwat
						GROUP BY `distid`
						ORDER BY CONVERT(cod.`distname` USING tis620) ASC';
		$cdbs=mydb::select($stmt,':changwat',$changwat);
		$sidebar.='<select name="ap" class="form-select"><option value="">-- ทุกอำเภอ --</option>'._NL;
		foreach ($cdbs->items as $srs) {
			$id=substr($srs->distid, 2,2);
			$sidebar.='<option value="'.$id.'" '.($ampur==$id?'selected="selected"':'').'>'.$srs->distname.'</option>'._NL;
		}
		$sidebar.='</select>'._NL;
	}


	if ($tpid) {
		$sidebar.='<select name="year" class="form-select"><option value="">-- ทุกปี --</option>'._NL;
		$yearDbs=mydb::select('SELECT DISTINCT `pryear` FROM %project% ORDER BY `pryear` DESC');
		foreach ($yearDbs->items as $rs) {
			$sidebar.='<option value="'.$rs->pryear.'" '.($rs->pryear==$year?'selected="selected"':'').'>พ.ศ.'.($rs->pryear+543).'</option>'._NL;
		}
		$sidebar.='</select>'._NL;
	}
	if ($year) {
		$sidebar.='<select name="month" class="form-select"><option value="">-- ทุกเดือน --</option>'._NL;
		for ($i=1;$i<=12;$i++) $sidebar.='<option value="'.sprintf('%02d',$i).'" '.($month==$i?'selected="selected"':'').'>'.sg_date($year.'-'.$i.'-01','ดดด ปปปป').'</option>';
		$sidebar.='</select>'._NL;
	}

	$groupList=array('area'=>'พื้นที่','title'=>'ชื่อโครงการ','owner'=>'ผู้บันทึกกิจกรรม');
	$sidebar.='<label for="gr">จัดกลุ่มโดย :</label><select name="gr" class="form-select">'._NL;
	foreach ($groupList as $key => $value) {
	 	$sidebar.='<option value="'.$key.'" '.($key==$groupBy?'selected="selected"':'').'>'.$value.'</option>';
	 }
	$sidebar.='</select>'._NL;

	$sidebar.='</div><!--box-->'._NL;

	$sidebar.='<input type="submit" class="button floating" value="ดู" />'._NL;
	$sidebar.='</form>'._NL;

	$self->theme->sidebar=$sidebar;

	if ($tpid) {
		$projectInfo=mydb::select('SELECT * FROM %topic% WHERE `tpid`=:tpid LIMIT 1',':tpid',$tpid);
		$ret.='<h3>'.$projectInfo->title.'</h3>';

		$where=array();
		$where=sg::add_condition($where,'tp.`parent`=:tpid','tpid',$tpid);
		if ($changwat) $where=sg::add_condition($where,'pv.`changwat`=:changwat','changwat',$changwat);
		if ($ampur) $where=sg::add_condition($where,'pv.`ampur`=:ampur','ampur',$ampur);
		if ($year) $where=sg::add_condition($where,'p.`pryear`=:year','year',$year);
		if ($month) $where=sg::add_condition($where,'MONTH(tr.`date1`)=:month','month',$month);
		//$where=sg::add_condition($where,'tr.`formid`="activity" AND tr.`part`="owner"');

		if ($groupBy=='area') {
			if ($ampur) {
				$groupByField='pv.`tambon`';
				$labelField='cos.`subdistname`';
			} else if ($changwat) {
				$groupByField='pv.`ampur`';
				$labelField='cod.`distname`';
			} else {
				$groupByField='pv.`changwat`';
				$labelField='cop.`provname`';
			}
		} else if ($groupBy=='owner') {
			$groupByField='pt.`uid`';
			$labelField='u.`name`';
		} else if ($groupBy=='title') {
			$groupByField='pt.`tpid`';
			$labelField='pt.`title`';
		}
		$whereCond=implode(' AND ', $where['cond']);
		$stmt="SELECT $labelField `label`
						,p.`tpid`,t.`title`, tp.`parent`, tp.`tpid` subtpid, pv.`changwat`, cop.`provname`
						, COUNT(DISTINCT tp.`tpid`) amtProject
						, COUNT(DISTINCT tr.`trid`) amtActivity
					FROM %project% p
						LEFT JOIN %topic% t USING (`tpid`)
						LEFT JOIN %topic_parent% tp ON tp.`parent`=p.`tpid`
						LEFT JOIN %topic% pt ON pt.`tpid`=tp.`tpid`
						LEFT JOIN %users% u ON u.`uid`=pt.`uid`
						LEFT JOIN %project_prov% pv ON tp.`tpid`=pv.`tpid`
						LEFT JOIN %co_province% cop ON cop.`provid`=pv.`changwat`
						LEFT JOIN %co_district% cod ON cod.`distid`=CONCAT(pv.`changwat`,pv.`ampur`)
						LEFT JOIN %co_subdistrict% cos ON cos.`subdistid`=CONCAT(pv.`changwat`,pv.`ampur`,pv.`tambon`)
						LEFT JOIN %project_tr% tr ON tr.`tpid`=tp.`tpid` AND tr.`formid`='activity' 
					WHERE $whereCond
					GROUP BY $groupByField
					ORDER BY CONVERT(`label` USING tis620) ASC
					";
		$dbs=mydb::select($stmt,$where['value']);

		$totalProject=$totalActivity=0;

		$tables = new Table();
		$tables->thead=array('พื้นที่','amt project'=>'จำนวนโครงการ','amt activity'=>'จำนวนกิจกรรม');
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(SG\getFirst($rs->label,'ไม่ระบุ'),$rs->amtProject,$rs->amtActivity);
			$totalProject+=$rs->amtProject;
			$totalActivity+=$rs->amtActivity;
		}
		$tables->rows[]=array('รวม',$totalProject,$totalActivity);

		$ret .= $tables->build();

		//$ret.=print_o($dbs,'$dbs');
	} else {
		$tables = new Table();
		$tables->thead=array('ชุดโครงการ/แผนงาน','amt'=>'จำนวนโครงการ');
		foreach ($dbsSubProject->items as $rs) {
			$tables->rows[]=array('<a href="'.url('project/report/subproject',array('id'=>$rs->tpid)).'">'.$rs->title.'</a>',$rs->amt);
		}

		$ret .= $tables->build();
	}

	$ret.='
	<script>
	$("#project-select select[name=pv]").change(function() {
		$("select[name=ap]").empty()
		$("select[name=tb]").empty()
	});
	$("#project-select select[name=ap]").change(function() {
		$("select[name=tb]").empty()
	});

	$(".sidebar select").change(function() {
		$("#project-select").submit()
	});
	</script>';
	return $ret;
}
?>