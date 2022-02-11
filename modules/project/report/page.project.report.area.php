<?php
/**
* Project owner
*
* @param Object $self
* @return String
*/
function project_report_area($self) {
	R::View('project.toolbar', $self, 'รายชื่อโครงการแยกตามพื้นที่ดำเนินการ', 'report');

	$changwat=post('pv');//SG\getFirst(post('org'),1);
	$ampur=post('ap');
	$tambon=post('tb');
	$village=post('vi');

	$stmt='SELECT pv.`changwat`, cop.`provname`, COUNT(*) amt
		FROM %project_prov% pv
			LEFT JOIN %project% t USING(`tpid`)
			LEFT JOIN %co_province% cop ON cop.`provid`=pv.`changwat`
		GROUP BY pv.`changwat`
		ORDER BY CONVERT(cop.`provname` USING tis620) ASC';
	$dbs=mydb::select($stmt);

	$sidebar.='<form id="project-select" method="get" action="'.url('project/report/area').'">'._NL;
	$sidebar.='<select name="pv" class="form-select"><option value="">==เลือกจังหวัด==</option>'._NL;
	foreach ($dbs->items as $rs) {
		$sidebar.='<option value="'.$rs->changwat.'" '.($rs->changwat==$changwat?'selected="selected"':'').'>'.$rs->provname.' ('.$rs->amt.' โครงการ)</option>'._NL;
	}
	$sidebar.='</select>'._NL;
	if ($changwat) {
		$stmt='SELECT
				cod.*
				, pv.`changwat`,pv.`ampur`
				, COUNT(`ampur`) amt
			FROM %co_district% cod
				LEFT JOIN %project_prov% pv ON CONCAT(pv.`changwat`,pv.`ampur`)=cod.`distid`
			WHERE LEFT(cod.`distid`,2)=:changwat
			GROUP BY `distid`
			ORDER BY CONVERT(cod.`distname` USING tis620) ASC';
		$cdbs=mydb::select($stmt,':changwat',$changwat);
		$sidebar.='<select name="ap" class="form-select"><option value="">--ทุกอำเภอ--</option>'._NL;
		foreach ($cdbs->items as $srs) {
			$id=substr($srs->distid, 2,2);
			$sidebar.='<option value="'.$id.'" '.($ampur==$id?'selected="selected"':'').'>'.$srs->distname.' ('.$srs->amt.' โครงการ)</option>'._NL;
		}
		$sidebar.='</select>'._NL;
	}

	if ($ampur) {
		$stmt='SELECT
				cot.*
				, pv.`changwat`,pv.`ampur`
				, COUNT(`tambon`) amt
			FROM %co_subdistrict% cot
				LEFT JOIN %project_prov% pv ON CONCAT(pv.`changwat`,pv.`ampur`,pv.`tambon`)=cot.`subdistid`
			WHERE LEFT(cot.`subdistid`,4)=:ampur
			GROUP BY `subdistid`
			ORDER BY CONVERT(cot.`subdistname` USING tis620) ASC';
		$cdbs=mydb::select($stmt,':ampur',$changwat.$ampur);
		$sidebar.='<select name="tb" class="form-select"><option value="">--ทุกตำบล--</option>'._NL;
		foreach ($cdbs->items as $srs) {
			$id=substr($srs->subdistid, 4,2);
			$sidebar.='<option value="'.$id.'" '.($tambon==$id?'selected="selected"':'').'>'.$srs->subdistname.' ('.$srs->amt.' โครงการ)</option>'._NL;
		}
		$sidebar.='</select>'._NL;
		//$ret.='ampur='.$ampur;
		//$ret.=print_o($cdbs,'$cdbs');
	}

	$sidebar.='<input type="submit" class="button floating" value="ดู" />';
	$sidebar.='</form>';
	$self->theme->sidebar=$sidebar;

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

	$ret.='<div id="info" class="info">';
	if (!$changwat) $ret.='<p class="notify">กรุณาเลือกจังหวัด</p>';
	else {
		$where=array();
		if ($changwat) $where=sg::add_condition($where,'pv.`changwat`=:changwat','changwat',$changwat);
		if ($ampur) $where=sg::add_condition($where,'pv.`ampur`=:ampur','ampur',$ampur);
		if ($tambon) $where=sg::add_condition($where,'pv.`tambon`=:tambon','tambon',$tambon);
		$stmt='SELECT DISTINCT t.`tpid`,t.`title`, o.`name` orgName
				, p.`project_status`
				, (SELECT COUNT(*) FROM %calendar% pc WHERE pc.`tpid`=pv.`tpid`) calendarTotals
				, (SELECT COUNT(*) FROM %project_tr% otr WHERE otr.`tpid`=pv.`tpid` AND otr.`formid`="activity" AND otr.`part`="owner") ownerActivity
				, (SELECT MAX(created) FROM %project_tr% lr WHERE lr.`tpid`=pv.`tpid` AND formid="activity" AND lr.`part`="owner") lastReport
			FROM %project_prov% pv
				LEFT JOIN %project% p USING(`tpid`)
				LEFT JOIN %topic% t USING(`tpid`)
				LEFT JOIN %db_org% o USING(`orgid`)

				'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
			ORDER BY CONVERT(`title` USING tis620) ASC';
		$dbs= mydb::select($stmt,$where['value']);


		$tables = new Table();
		$tables->thead=array('no'=>'','ชื่อโครงการ','amt calendarTotals'=>'กิจกรรม(ตามแผน)','amt ownerActivity'=>'กิจกรรมในพื้นที่(ทำแล้ว)','date'=>'กิจกรรมล่าสุด','สถานะโครงการ','หน่วยงาน');
		foreach ($dbs->items as $rs) {
			$tables->rows[]=array(
				++$no,
				'<a href="'.url('paper/'.$rs->tpid).'">'.$rs->title.'</a>',
				$rs->calendarTotals?$rs->calendarTotals:'-',
				$rs->ownerActivity?$rs->ownerActivity:'-',
				$rs->lastReport?sg_date($rs->lastReport,'ว ดด ปปปป'):'-',
				$rs->project_status,
				$rs->orgName
			);
		}

		$ret .= $tables->build();


		//$ret.=print_o($dbs,'$dbs');
	}

	$ret.='</div>';
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
		unset($tables);
		$no=0;

		$tables = new Table();
		if ($parent==0) $tables->thead=array('','แผนงาน/ชุดโครงการ/โครงการ','money'=>'งบประมาณ(บาท)');
		foreach ($dbs->items as $rs) {
			$budget=number_format($rs->budget,2);
			$tables->rows[]=array(++$no,'<a href="'.url('paper/'.$rs->tpid).'">'.$rs->title.'</a> ('.$rs->prtype.')',$parent==0?'<strong>'.$budget.'</strong>':$budget,'config'=>array('class'=>$parent==0?'bold':''));
			$subtable=__project_org_list(NULL,$rs->tpid);
			if ($subtable) $tables->rows[]=array('<td colspan="3" style="text-align:left;padding-left:15px;">'.$subtable->build().'</td>');
		}
	}
	return $tables;
}
?>