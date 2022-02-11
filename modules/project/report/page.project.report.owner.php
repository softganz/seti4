<?php
/**
* Project report by owner
*
* @param Object $self
* @return String
*/
function project_report_owner($self) {
	R::View('project.toolbar', $self, 'รายงานผู้รับผิดชอบโครงการ', 'report');

	$reportType=SG\getFirst(post('type'),'project');
	$changwat=post('pv');//SG\getFirst(post('org'),1);
	$ampur=post('ap');
	$tambon=post('tb');
	$year=SG\getFirst(post('year'),date('Y'));

	$stmt='SELECT pv.`changwat`, cop.`provname`, COUNT(*) amt
					FROM %project_prov% pv
						LEFT JOIN %project% t USING(`tpid`)
						LEFT JOIN %co_province% cop ON cop.`provid`=pv.`changwat`
					GROUP BY pv.`changwat`
					ORDER BY CONVERT(cop.`provname` USING tis620) ASC';
	$dbs=mydb::select($stmt);

	$sidebar.='<form id="project-select" method="get" action="'.url('project/report/owner').'">'._NL;
	$sidebar.='<div class="box"><h3>พื้นที่</h3>';
	$sidebar.='<select name="pv" class="form-select"><option value="">-- เลือกจังหวัด --</option>'._NL;
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
		$sidebar.='<select name="ap" class="form-select"><option value="">-- ทุกอำเภอ --</option>'._NL;
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
		$sidebar.='<select name="tb" class="form-select"><option value="">-- ทุกตำบล --</option>'._NL;
		foreach ($cdbs->items as $srs) {
			$id=substr($srs->subdistid, 4,2);
			$sidebar.='<option value="'.$id.'" '.($tambon==$id?'selected="selected"':'').'>'.$srs->subdistname.' ('.$srs->amt.' โครงการ)</option>'._NL;
		}
		$sidebar.='</select>'._NL;
		//$sidebar.='ampur='.$ampur;
		//$sidebar.=print_o($cdbs,'$cdbs');
	}
	$sidebar.='</div><!--section-->';
	$sidebar.='<div class="box"><h3>ช่วงเวลา</h3>';
	$sidebar.='<select name="year" class="form-select">'._NL;
	$yearDbs=mydb::select('SELECT DISTINCT `pryear` FROM %project% ORDER BY `pryear` DESC');
	foreach ($yearDbs->items as $rs) {
		$sidebar.='<option value="'.$rs->pryear.'" '.($rs->pryear==$year?'selected="selected"':'').'>พ.ศ.'.($rs->pryear+543).'</option>'._NL;
	}
	$sidebar.='</select>'._NL;
	$sidebar.='</div>';

	$sidebar.='<div class="box"><h3>รายงาน</h3>';
	$sidebar.='<select name="type" class="form-select">'._NL;
	$reportTypes=array('project'=>'จำนวนโครงการ','activity'=>'จำนวนกิจกรรม');
	foreach ($reportTypes as $key=>$value) {
		$sidebar.='<option value="'.$key.'" '.($key==$reportType?'selected="selected"':'').'>'.$value.'</option>'._NL;
	}
	$sidebar.='</select>'._NL;
	$sidebar.='</div>';

	$sidebar.='<input type="submit" class="button floating" value="ดู" />';
	$sidebar.='</form>';
	$self->theme->sidebar=$sidebar;

	$where=array();
	$where=sg::add_condition($where,'tr.`formid`="activity"');
	if ($changwat) $where['changwat']=$changwat;
	if ($ampur) $where['ampur']=$ampur;
	if ($tambon) $where['tambon']=$tambon;
	$where['year']=$year;

	$ret.='<div id="info" class="info">';
	switch ($reportType) {
		case 'project': $ret.=__report_project($where); break;
		case 'activity': $ret.=__report_activity($where); break;
		default:
			# code...
			break;
	}
	$ret.='</div>';
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

function __report_activity($para) {
	$where=array();
	$where=sg::add_condition($where,'`formid`="activity" AND `part`="owner"');
	if ($para['year']) $where=sg::add_condition($where,'DATE_FORMAT(`date1`,"%Y")=:year','year',$para['year']);
	if ($para['changwat']) $where=sg::add_condition($where,'pv.`changwat`=:changwat','changwat',$para['changwat']);
	if ($para['ampur']) $where=sg::add_condition($where,'pv.`ampur`=:ampur','ampur',$para['ampur']);
	if ($para['tambon']) $where=sg::add_condition($where,'pv.`tambon`=:tambon','tambon',$para['tambon']);

	$stmt='SELECT a.*, `username`, `name`, COUNT(*) amt
				FROM
					(SELECT DISTINCT `trid`,`uid`, DATE_FORMAT(`date1`,"%Y-%m") `month`
					FROM %project_tr% tr
						LEFT JOIN %project_prov% pv USING(`tpid`)
						'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
					) a
					LEFT JOIN %users% u USING(`uid`)
				GROUP BY `uid`, `month`
				ORDER BY CONVERT(`name` USING tis620) ASC';
	$dbs=mydb::select($stmt,$where['value']);

	$tables = new Table();
	$tables->caption='จำนวนกิจกรรม ประจำปี '.($para['year']+543);
	$tables->thead=array('ชื่อผู้รับผิดชอบโครงการ');
	$tables->tfoot[0]['name']='';
	for ($i=1;$i<=12;$i++) {
		$tables->thead['amt month-'.$i]=sg_date($para['year'].'-'.$i.'-01','ดด');
		$tables->tfoot[0][$para['year'].'-'.sprintf('%02d',$i)]='0';
	}
	$tables->tfoot[0]['config']=array('style'=>'text-align:center;');

	foreach ($dbs->items as $rs) {
		if (!$tables->rows[$rs->uid]) {
			$tables->rows[$rs->uid]=array('<a href="'.url('project/list','owner='.$rs->uid).'" ><img class="ownerphoto" src="'.model::user_photo($rs->username).'" alt="'.htmlspecialchars($rs->name).'" width="32" height="32" />'.$rs->name.'</a>');
			for ($i=1;$i<=12;$i++) $tables->rows[$rs->uid][$para['year'].'-'.sprintf('%02d',$i)]='-';
		}
		$tables->rows[$rs->uid][$rs->month]=$rs->amt;
		$tables->tfoot[0][$rs->month]+=$rs->amt;
	}
	$tables->tfoot[0]['name']='รวม '.count($tables->rows).' คน';

	$ret .= $tables->build();
	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}

function __report_project($para) {
	$where=array();
	//$where=sg::add_condition($where,'`membership`="Owner"');
	if ($para['year']) $where=sg::add_condition($where,'`pryear`=:pryear','pryear',$para['year']);
	if ($para['changwat']) $where=sg::add_condition($where,'pv.`changwat`=:changwat','changwat',$para['changwat']);
	if ($para['ampur']) $where=sg::add_condition($where,'pv.`ampur`=:ampur','ampur',$para['ampur']);
	if ($para['tambon']) $where=sg::add_condition($where,'pv.`tambon`=:tambon','tambon',$para['tambon']);

	// Query น่าจะมีปัญหานับจำนวนโครงการเกินจากโครงการที่กำหนดพื้นที่ดำเนินการมากกว่า 1 พื้นที่
	$stmt='SELECT t.`uid`, u.`username`, u.`name`, COUNT(*) projects
						FROM %project% p
						LEFT JOIN %topic% t USING(`tpid`)
						LEFT JOIN %users% u USING(`uid`)
						LEFT JOIN %project_prov% pv USING (`tpid`)
					'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
					GROUP BY t.`uid`
					ORDER BY CONVERT(`name` USING tis620) ASC';
	$dbs=mydb::select($stmt,$where['value']);

	$tables = new Table();
	$tables->caption='จำนวนโครงการ ประจำปี '.($para['year']+543);
	$tables->thead=array('ชื่อผู้รับผิดชอบโครงการ','amt'=>'จำนวนโครงการ');
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
											'<a href="'.url('project/list','owner='.$rs->uid).'" ><img class="ownerphoto" src="'.model::user_photo($rs->username).'" alt="'.htmlspecialchars($rs->name).'" width="32" height="32" />'.$rs->name.'</a>',
											$rs->projects);
		$totals+=$rs->projects;
	}
	$tables->tfoot[]=array('จำนวน '.number_format($dbs->_num_rows,0).' คน',number_format($totals,0),'config'=>array('style'=>'text-align:center;'));

	$ret .= $tables->build();
	return $ret;
}
?>