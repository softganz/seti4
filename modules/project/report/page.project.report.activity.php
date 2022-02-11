<?php
/**
* Project owner
*
* @param Object $self
* @param Object $topic
* @param Object $para
* @param Object $body
* @return String
*/
function project_report_activity($self) {
	R::View('project.toolbar', $self, 'รายงานบันทึกผลการทำกิจกรรมแยกตามพื้นที่ดำเนินการ', 'report');

	$changwat=post('pv');//SG\getFirst(post('org'),1);
	$ampur=post('ap');
	$tambon=post('tb');
	$village=post('vi');
	$year=SG\getFirst(post('year'),date('Y'));
	$month=SG\getFirst(post('month'),date('m'));

	$stmt='SELECT pv.`changwat`, cop.`provname`, COUNT(*) amt
		FROM %project_prov% pv
			LEFT JOIN %project% t USING(`tpid`)
			LEFT JOIN %co_province% cop ON cop.`provid`=pv.`changwat`
		GROUP BY pv.`changwat`
		ORDER BY CONVERT(cop.`provname` USING tis620) ASC';
	$dbs=mydb::select($stmt);

	$sidebar.='<form id="project-select" method="get" action="'.url('project/report/activity').'">'._NL;
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
		$sidebar.='<select name="ap" class="form-select"><option value="">-- เลือกอำเภอ --</option>'._NL;
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
		//$ret.='ampur='.$ampur;
		//$ret.=print_o($cdbs,'$cdbs');
	}
	$sidebar.='</div><!--box-->';

	$sidebar.='<div class="box"><h3>ช่วงเวลา</h3>';
	$sidebar.='<select name="year" class="form-select">'._NL;
	$yearDbs=mydb::select('SELECT DISTINCT `pryear` FROM %project% ORDER BY `pryear` DESC');
	foreach ($yearDbs->items as $rs) {
		$sidebar.='<option value="'.$rs->pryear.'" '.($rs->pryear==$year?'selected="selected"':'').'>พ.ศ.'.($rs->pryear+543).'</option>'._NL;
	}
	$sidebar.='</select>'._NL;
	$sidebar.='<select name="month" class="form-select">'._NL;
	for ($i=1;$i<=12;$i++) $sidebar.='<option value="'.sprintf('%02d',$i).'" '.($month==$i?'selected="selected"':'').'>'.sg_date($year.'-'.$i.'-01','ดดด ปปปป').'</option>';
	$sidebar.='</select>';
	$sidebar.='</div><!--box-->';

	$sidebar.='<input type="submit" class="button floating" value="ดู" />';
	$sidebar.='</form>';

	$where=array();
	$where=sg::add_condition($where,'tr.`formid`="activity" AND tr.`part`="owner"');
	if ($changwat) $where['changwat']=$changwat;
	if ($ampur) $where['ampur']=$ampur;
	if ($tambon) $where['tambon']=$tambon;
	$where['month']=$year.'-'.$month;
	$self->theme->sidebar=$sidebar;

	if (!$changwat) $ret.='<p class="notify">กรุณาเลือกจังหวัด</p>';
	else if (!$ampur) $ret.='<p class="notify">กรุณาเลือกอำเภอ</p>';
	else {
		$activitys=project_model::get_activity($where);
		$ret.='<div id="info" class="info">';

		$ret.='<p align="right"><strong>จำนวนกิจกรรม '.number_format($activitys->_num_rows,0).' รายการ</strong></p>';
		//$ret.='<ul class="card--main">';
		foreach ($activitys->items as $rs) {
			$ret.='<div class="card">';
			$ret.='<h4>กิจกรรม : <a href="'.url('paper/'.$rs->tpid).'" target="_blank">'.$rs->title.'</a></h4>'._NL;
			$ret.='<h5>โครงการ : <a href="'.url('paper/'.$rs->tpid).'">'.$rs->projectTitle.'</a></h5>'._NL;
			if (0 && $rs->photos) {
				//$activitys->items[15394]->photos [string] : 1139|pic555c01833a4d2.jpg,1142|pic555c0187c4b7d.jpg
				foreach (explode(',',$rs->photos) as $item) {
					list($photoid,$photofile)=explode('|',trim($item));
					$photo=model::get_photo_property($photofile);
					$ret.='<a class="sg-action" data-group="photo'.$rs->trid.'" href="'.$photo->_src.'" data-rel="box" title="'.htmlspecialchars($photo_alt).'">';
					$ret.='<img class="photo photo-'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo '.$photo_alt.'" height="80" ';
					$ret.=' />';
					$ret.='</a> ';
				}
			}
			$ret.='<div><strong>วันที่ '.sg_date($rs->action_date,'ว ดดด ปปปป').' เวลา '.$rs->action_time.' น.</strong></div>';
			$ret.='<div><strong>กิจกรรมที่ปฎิบัติ :</strong> '.sg_text2html($rs->real_do).'</div>'._NL;
			$ret.='<div><strong>ผลการดำเนินงาน :</strong> '.sg_text2html($rs->real_work).'</div>'._NL;
			$ret.='</div><!--card-->'._NL;
		}
		//$ret.='</ul>'._NL;
		//$ret.=print_o($activitys,'$activitys');
	}
	$ret.='</div><!--info-->';

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