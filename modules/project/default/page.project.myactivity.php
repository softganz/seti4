<?php
/**
* Project my activity
*
* @param Object $self
* @return String
*/
function project_myactivity($self) {
	$self->theme->title='ใบส่งงาน';

	$year=SG\getFirst(post('year'),date('Y'));
	$month=SG\getFirst(post('month'),date('m'));
	$allProject=post('project');
	$uid=SG\getFirst(post('u'),i()->uid);
	$reportType=post('type');

	$reportTypes=array('activity'=>'การปฏิบัติงานประจำเดือน','monthly'=>'ผลการดำเนินงานประจำเดือน');

	$sidebar.='<form id="project-select" method="get" action="'.url('project/myactivity').'">'._NL;
	$sidebar.='<div class="box"><h3>รายงาน</h3>';
	$sidebar.='<select name="type" class="form-select">'._NL;
	foreach ($reportTypes as $key=>$value) {
		$sidebar.='<option value="'.$key.'" '.($key==$reportType?'selected="selected"':'').'>'.$value.'</option>'._NL;
	}
	$sidebar.='</select>'._NL;
	if (user_access('administer projects')) {
		$sidebar.='<input id="u" type="hidden" name="u" value="'.$uid.'" />';
		$sidebar.='<label>สำหรับสมาชิก</label><input class="sg-autocomplete" data-query="'.url('admin/get/username',array('r'=>'id')).'" data-callback="submit" data-altfld="u" type="text" id="search-box" size="30" value="'.$q.'" placeholder="Username or Name or Email">';
	}
	$sidebar.='<input type="checkbox" name="project" value="all" '.($allProject=='all'?'checked="checked"':'').' /> ทุกโครงการในความรับผิดชอบ';
	$sidebar.='</div>';

	$sidebar.='<div class="box"><h3>ช่วงเวลา</h3>';
	$sidebar.='<select name="year" class="form-select">'._NL;
	$yearDbs=mydb::select('SELECT DISTINCT `pryear` FROM %project% ORDER BY `pryear` DESC');
	foreach ($yearDbs->items as $rs) {
		$sidebar.='<option value="'.$rs->pryear.'" '.($rs->pryear==$year?'selected="selected"':'').'>พ.ศ.'.($rs->pryear+543).'</option>'._NL;
	}
	$sidebar.='</select>'._NL;
	$sidebar.='<select name="month" class="form-select"><option value="all">-- ทุกเดือน --</option>'._NL;
	for ($i=1;$i<=12;$i++) $sidebar.='<option value="'.sprintf('%02d',$i).'" '.($month==$i?'selected="selected"':'').'>'.sg_date($year.'-'.$i.'-01','ดดด ปปปป').'</option>';
	$sidebar.='</select>';
	$sidebar.='</div>';

	$sidebar.='<input type="submit" class="button floating" value="ดู" />';
	$sidebar.='</form>';
	$self->theme->sidebar=$sidebar;

	$where=array();
	if ($month=='all') $where['year']=$year;
	else $where['month']=$year.'-'.$month;
	if ($allProject=='all') {
		$stmt='SELECT `tpid` FROM %topic_user% WHERE `uid`=:uid';
		$myProject=mydb::select($stmt,':uid',$uid)->lists->text;
		if (empty($myProject)) $error='<p class="notify">ไม่มีโครงการในความรับผิดชอบ</p>';
		$where['tpid']=$myProject;
		$where['order']='tr.`uid` ASC, tr.`date1` ASC';
	} else {
		$where['owner']=$uid;
	}

	$ret.='<div id="info" class="info">';
	if ($error) {
		$ret.=$error;
	} else {
		switch ($reportType) {
			case 'monthly' :
				$self->theme->title='รายงานผลการดำเนินงานโครงการตามแผนปฏิบัติการพัฒนาจังหวัดชายแดนใต้ ประจำปีงบประมาณ '.($year+543);

				$year=SG\getFirst(post('y'));
				$province=post('p');
				$where=array();
				$where=sg::add_condition($where,'t.`uid`=:uid','uid',i()->uid);
				$where=sg::add_condition($where,'p.`prtype`="โครงการ"');

				$stmt='SELECT
									p.`tpid`, t.`title`
									, o.`sector`, p.`budget`, p.`otherbudget`, p.`totaltarget`, p.`area`
									, COUNT(`formid`) `totalReport`
									, SUM(tr.`num1`) paid
									, GROUP_CONCAT(tr.`num5` order by tr.`date1`) percentdone
									, GROUP_CONCAT(tr.`date1` order by tr.`date1`) lastreportdate
									, `text1` `msg`, `text2` `problem`, `text3` `recommendation`
								FROM %project% p
									LEFT JOIN %topic% t USING(`tpid`)
									LEFT JOIN %db_org% o USING(`orgid`)
									LEFT JOIN %project_tr% tr ON tr.`tpid`=p.`tpid` AND tr.`formid`="report"
								WHERE '.implode(' AND ',$where['cond']).'
								GROUP BY p.`tpid`
								ORDER BY p.`tpid` ASC, tr.`date1` ASC';
				$dbs=mydb::select($stmt,$where['value']);

				$tables = new Table();
				$tables->thead=array('amt no'=>'ลำดับ','แผนงาน ผลผลิต กิจกรรม/รายการ กิจกรรม-โครงการย่อย', 'money budget'=>'งบประมาณตามแผน(บาท)', 'money otherbudget'=>'งบประมาณนอกแผน(บาท)', 'money totalbudget'=>'รวมงบประมาณ(บาท)', 'amt projects'=>'จำนวนเป้าหมาย(คน)','พื้นที่ดำเนินการ','money paid'=>'จำนวนเงินเบิกจ่าย(บาท)','amt percentpaid'=>'ร้อยละ','amt percentdone'=>'ผลการดำเนินงาน(ร้อยละ)','date reportdate'=>'เดือนที่รายงานผลการดำเนินงานล่าสุด','amt totalreport'=>'จำนวนรายงาน(ครั้ง)');
				foreach ($dbs->items as $rs) {
					$projectBudget=$rs->budget+$rs->otherbudget;
					$percentdone=array_pop(explode(',', $rs->percentdone));
					$lastreportdate=array_pop(explode(',', $rs->lastreportdate));
					$tables->rows[]=array(
															++$no,
															'<td colspan="9"><a href="'.url('paper/'.$rs->tpid).'">'.$rs->title.'</a></td>');
					$tables->rows[]=array('','',
															number_format($rs->budget,2),
															number_format($rs->otherbudget,2),
															number_format(($projectBudget),2),
															number_format($rs->totaltarget,0),
															$rs->area,
															number_format($rs->paid,2),
															$projectBudget?number_format($rs->paid*100/$projectBudget,2).'%' : '-',
															$percentdone?number_format($percentdone).'%':'',
															$lastreportdate?sg_date($lastreportdate,'ดด ปป') : '',
															$rs->totalReport?$rs->totalReport:'-',
															);
					$totalProjects+=$rs->amt;
					$totalBudgets+=$rs->budget;
					$totalOtherBudgets+=$rs->otherbudget;
					$totalTarget+=$rs->totaltarget;
					$totalPaid+=$rs->paid;
					$totalReport+=$rs->totalReport;
				}
				$tables->rows[]=array(
															'',
															'<strong>รวม</strong>',
															'<strong>'.number_format($totalBudgets,2).'</strong>',
															'<strong>'.number_format($totalOtherBudgets,2).'</strong>',
															'<strong>'.number_format(($totalBudgets+$totalOtherBudgets),2).'</strong>',
															'<strong>'.number_format($totalTarget).'</strong>',
															'',
															'<strong>'.number_format($totalPaid,2).'</strong>',
															'<strong>'.number_format($totalPaid*100/($totalBudgets+$totalOtherBudgets),2).'%'.'</strong>',
															'',
															'',
															'<strong>'.$totalReport.'</strong>',
															);
				$ret .= $tables->build();
				$ret.='<p>หมายเหตุ : งบประมาณ : ล้านบาท ทศนิยม 4 ตำแหน่ง</p>';
				break;

			default :
				$activitys=project_model::get_activity($where);

				$ret.='<p align="right" class="noprint"><strong>จำนวนกิจกรรม '.number_format($activitys->_num_rows,0).' รายการ</strong></p>';
				//$ret.='<ul class="card--main">';
				foreach ($activitys->items as $rs) {
					if ($rs->uid!=$currentUid) {
						$periodStr=$month=='all' ? 'ปี พ.ศ. '.($year+543) : 'เดือน '.sg_date($year.'-'.$month.'-01','ดดด พ.ศ. ปปปป');
						$ret.='<div class="project--owner">';
						$ret.='<h2>รายละเอียดการส่งมอบงานจ้าง</h2><h2>ประจำ'.$periodStr.'</h2>';
						$ret.='<h2>ชื่อ '.$rs->ownerName.'</h2>';
						$ret.='<h2>รายละเอียดการปฏิบัติงานประจำ'.$periodStr.' ดังนี้</h2>';
						$ret.='</div>';
						$currentUid=$rs->uid;
					}
					$ret.='<div class="card">';
					$ret.='<h4>กิจกรรม : <a href="'.url('paper/'.$rs->tpid).'" target="_blank">'.$rs->title.'</a></h4>'._NL;
					$ret.='<h5>โครงการ : <a href="'.url('paper/'.$rs->tpid).'">'.$rs->projectTitle.'</a></h3>'._NL;
					if ($rs->photos) {
						//$activitys->items[15394]->photos [string] : 1139|pic555c01833a4d2.jpg,1142|pic555c0187c4b7d.jpg
						foreach (explode(',',$rs->photos) as $item) {
							list($photoid,$photofile)=explode('|',trim($item));
							$photo=model::get_photo_property($photofile);
							$ret.='<a class="sg-action" data-group="photo'.$rs->trid.'" href="'.$photo->_src.'" data-rel="img" title="'.htmlspecialchars($photo_alt).'">';
							$ret.='<img class="photo photo-'.($photo->_size->width>$photo->_size->height?'wide':'tall').'" src="'.$photo->_src.'" alt="photo '.$photo_alt.'" height="80" ';
							$ret.=' />';
							$ret.='</a> ';
						}
					}
					$ret.='<div><strong>วันที่ '.sg_date($rs->action_date,'ว ดดด ปปปป').' เวลา '.$rs->action_time.' น.</strong></div>';
					$ret.='<div><strong>กิจกรรมที่ปฎิบัติ :</strong> '.sg_text2html($rs->real_do).'</div>'._NL;
					$ret.='<div><strong>ผลการดำเนินงาน :</strong> '.sg_text2html($rs->real_work).'</div>'._NL;
					$ret.='</div>'._NL;
				}
				break;

		}

		//$ret.='</ul>'._NL;
		//$ret.=print_o($where,'$where');
		//$ret.=print_o($activitys,'$activitys');
		//$ret.=print_o(post(),'post');
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

?>