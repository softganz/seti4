<?php
/**
* Project situation
*
* @param Object $self
* @return String
*/
function project_situation_list($self) {
	project_model::set_toolbar($self,'สถานการณ์โครงการ');
	$para=para($para,'order=t.tpid','sort=DESC','items=10000');

	$projectset=SG\getFirst($para->set,post('set'));
	$year=SG\getFirst($para->year,post('year'));
	$province=SG\getFirst($para->province,post('province'));
	$trainer=SG\getFirst($para->trainer,post('trainer'));
	$owner=SG\getFirst($para->owner,post('owner'));
	$u=SG\getFirst($para->u,post('u'));
	$zone=post('zone');

	$order=SG\getFirst(post('order'),'t.`tpid`');

	$ui=new ui();

	if (!$trainer && !$owner && !$u) {
		$zoneList=cfg('zones');
		$provSelect.='<form method="get" action="'.url('project/situation').'"><input type="hidden" name="year" value="'.$year.'" />';
		if ($zoneList) {
			$provSelect.='<select name="zone" class="form-select" onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;"><option value="">ทุกภาค</option>';
			foreach ($zoneList as $zoneKey => $zoneItem) {
				$provSelect.='<option value="'.$zoneKey.'"'.($zoneKey==$zone?' selected="selected"':'').'>'.$zoneItem['name'].'</option>';
			}
			$provSelect.='</select> ';
		}


		$stmt='SELECT DISTINCT `changwat`, `provname`
						FROM %project% p
							LEFT JOIN %co_province% cop ON cop.`provid`=p.`changwat`
						'.($zone?'WHERE LEFT(p.`changwat`,1) IN ('.$zoneList[$zone]['zoneid'].')':'').'
						HAVING `provname`!=""
						ORDER BY CONVERT(`provname` USING tis620) ASC';
		$dbs=mydb::select($stmt);
		$provSelect.='<select name="province" class="form-select" onchange="notify(\'กำลังโหลด\');$(this).closest(\'form\').submit();return false;"><option value="">ทุกจังหวัด</option>';
		foreach ($dbs->items as $rs) {
			$provSelect.='<option value="'.$rs->changwat.'"'.($rs->changwat==$province?' selected="selected"':'').'>'.$rs->provname.'</option>';
		}
		$provSelect.='</select>';
		$provSelect.='</form>';
		$ui->add($provSelect);
	}

	foreach (mydb::select('SELECT DISTINCT `pryear` FROM %project% HAVING `pryear` ORDER BY `pryear` DESC')->items as $v) {
		$ui->add('<a href="'.url('project/list',array('set'=>$projectset, 'year'=>$v->pryear,'province'=>$province,'trainer'=>$trainer,'owner'=>$owner,'u'=>$u,'zone'=>post('zone'))).'">ปี '.sg_date($v->pryear,'ปปปป').'</a>');
	}
	$ret.='<div class="reportbar">'.$ui->build('ul').'</div>';

	$items=100;
	$page=post('page');
	$firstRow=$page>1 ? ($page-1)*$items : 0;

	$where=array();
	$where=sg::add_condition($where,'p.`prtype`="โครงการ"');
	if ($year) {
		$where=sg::add_condition($where,'`pryear`=:year ','year',$year);
		$text[]=' ปี '.($year+543);
	}
	if ($zone) {
		$where=sg::add_condition($where,'LEFT(t.`changwat`,1) IN (:zone)','zone','SET:'.$zoneList[$zone]['zoneid']);
		$text[]=' พื้นที่ '.$zoneList[$zone]['name'];
	}
	if ($province) {
		if (cfg('project.multiplearea')) {
			$where=sg::add_condition($where,'a.changwat=:changwat ','changwat',$province);
		} else {
			$where=sg::add_condition($where,'p.changwat=:changwat ','changwat',$province);
		}
		$text[]='จังหวัด'.mydb::select('SELECT provname FROM %co_province% WHERE provid=:provid LIMIT 1',':provid',$province)->provname;
	}


	$stmt='SELECT DISTINCT SQL_CALC_FOUND_ROWS
			  p.`tpid`, p.`agrno`, p.`prid`, p.`pryear`
			, p.`prtype`
			, p.`project_status`, p.`project_status`+0 project_statuscode
			, t.`title`, p.`date_from`, p.`date_end`
			, t.`status`
			, p.`projectset`, pset.`title` projectset_name
			, t.`uid`, u.`username`
			, u.`name` ownerName
			, t.`created`
			, t.`orgid`, o.`name` departmentName
			, (SELECT COUNT(*) FROM %calendar% pc WHERE pc.tpid=p.tpid) calendar_totals
			, (SELECT COUNT(*) FROM %project_tr% otr WHERE otr.tpid=p.tpid AND otr.formid="kamsaiindi" AND otr.part="title") `kamsaiindicator`
			, (SELECT COUNT(*) FROM %project_tr% ctr WHERE ctr.tpid=p.tpid AND ctr.formid="schooleat" AND ctr.part="title") `schooleat`
			, (SELECT COUNT(*) FROM %project_tr% ctr WHERE ctr.tpid=p.tpid AND ctr.formid="weight" AND ctr.part="title") `weight`
			, (SELECT COUNT(*) FROM %project_tr% tres WHERE tres.tpid=t.tpid AND tres.formid="ประเมิน") estimationAmt
			, (SELECT MAX(created) FROM %project_tr% lr WHERE lr.tpid=t.tpid AND formid="activity") last_report
		FROM %project% p
			LEFT JOIN %topic% t ON t.tpid=p.tpid
			LEFT JOIN %users% u ON u.uid=t.uid
			'.(cfg('project.multiplearea')?'LEFT JOIN %project_prov% a ON a.`tpid`=t.`tpid`':'').'
			LEFT JOIN %topic% pset ON p.projectset=pset.tpid
			LEFT JOIN %db_org% o ON o.`orgid`=t.`orgid`
			'.(projectcfg::enable('develop') ? '
			LEFT JOIN %project_tr% fo1 ON p.tpid=fo1.tpid AND fo1.formid="follow" AND fo1.part="title" AND fo1.period=1
			LEFT JOIN %project_tr% fo2 ON p.tpid=fo2.tpid AND fo2.formid="follow" AND fo2.part="title" AND fo2.period=2
			LEFT JOIN %project_tr% fo3 ON p.tpid=fo3.tpid AND fo3.formid="follow" AND fo3.part="title" AND fo3.period=3':'').'
		'.($where?'WHERE '.implode(' AND ',$where['cond']):'').'
		GROUP BY p.`tpid`
		ORDER BY '.$order.' '.$sort.'
		LIMIT '.$firstRow.' , 100';

	$dbs = mydb::select($stmt,$where['value']);

	$dbs->_start_row=$firstRow;
	//$ret.='Page='.$page.' first='.(($page-1)*$items);
	//if (i()->username=='softganz') $ret.=mydb()->_query;

	$totals = $dbs->_found_rows;

	if ($para->year) $pagePara['year']=$para->year;
	if (post('province')) $pagePara['province']=post('province');
	if (post('org')) $pagePara['org']=post('org');
	$pagePara['q']=post('q');
	$pagePara['page']=$page;
	$pagenv = new PageNavigator($items,$page,$totals,q(),false,$pagePara);
	$no=$pagenv?$pagenv->FirstItem():0;

	//$ret.='First item='.$pagenv->FirstItem();
	//$sql_cmd .= '  LIMIT '.$pagenv->FirstItem().','.$items;

	//$ret.='Total = '.$totals;

	//$ret.=print_o($dbs,'$dbs');

	$text[]='('.($totals?'จำนวน '.$totals.' โครงการ' : 'ไม่มีโครงการ').')';
	if ($text) $self->theme->title.=' '.implode(' ',$text);

	if ($para->order=="year") $dbs->_group='pryear';
	else if ($para->order=='projectset') $dbs->_group='projectset_name';
	//		if (i()->username=='softganz') $ret.=print_o($dbs,'$dbs');
	if ($dbs->_empty) {
		$ret.=message('error','ไม่มีรายชื่อโครงการตามเงื่อนไขที่ระบุ');
	} else {
		$ret .= '<div class="pagenv">'.$pagenv->show.'</div>'._NL;

		$ret.=__project_situation_list($dbs,$para);
		//$ret.=print_o($dbs,'$dbs');

		$ret .= '<div class="pagenv">'.$pagenv->show.'</div>'._NL;
		$ret.='<p>รวมทั้งสิ้น <strong>'.$totals.'</strong> โครงการ</p>';
	}
	return $ret;
}

function __project_situation_list($dbs,$para) {
	$statusText=array(
								_PROJECT_DRAFTREPORT=>'เริ่มทำรายงาน',
								_PROJECT_COMPLETEPORT=>'แจ้งรายงานเสร็จสมบูรณ์',
								_PROJECT_LOCKREPORT=>'ผ่านการตรวจสอบของพี่เลี้ยงโครงการ',
								_PROJECT_PASS_HSMI=>'ผ่านการตรวจสอบของ สจรส.',
								_PROJECT_PASS_SSS=>'ผ่านการตรวจสอบของ สสส.'
								);

	$tables = new Table();
	$tables->id='project-list';
	$tables->thead['no']='';
	$tables->thead['agrno']='ข้อตกลงเลขที่';
	$tables->thead['prid']='รหัสโครงการ';
	$tables->thead['profile']='';
	$tables->thead['title']='ชื่อโครงการ';
	$tables->thead['amt calendar']='กิจกรรม<br />(ตามแผน/ทำแล้ว)';
	if (projectcfg::enable('ง.1')) $tables->thead['amt Financial1']='ง๑<br /><a href="#ref" title="รายงานการเงิน ง๑"><sup>?</sup></a>';
	if (projectcfg::enable('ส.1')) $tables->thead['amt Progress-s1']='ส๑<br /><a href="#ref" title="รายงานความก้าวหน้า ส๑"><sup>?</sup></a>';
	if (projectcfg::enable('ส.2')) $tables->thead['amt Progress-s2']='ส๒<br /><a href="#ref" title="รายงานความก้าวหน้า ส๒"><sup>?</sup></a>';
	if (projectcfg::enable('ง.2')) $tables->thead['amt Financial2']='ง๒<br /><a href="#ref" title="รายงานการเงิน ง๒"><sup>?</sup></a>';
	if (projectcfg::enable('ส.3')) $tables->thead['amt Progress-s3']='ส๓<br /><a href="#ref" title="รายงานความก้าวหน้า ส๓"><sup>?</sup></a>';
	$tables->thead['amt kamsaiindicator']='แบบประเมิน';
	$tables->thead['amt schooleat']='สถานการณ์การกินอาหารและการออกกำลังกายของนักเรียน';
	$tables->thead['amt weight']='สถานการณ์ภาวะโภชนาการนักเรียน';
	$tables->thead['date']='กิจกรรมล่าสุด';
	$tables->thead[]='สถานะโครงการ';
	// $tables->thead='<thead><tr><th rowspan="2"></th><th>ข้อตกลงเลขที่</th></thead>';

	$no=$dbs->_start_row;
	$cgroup='';
	foreach ($dbs->items as $rs) {
		if ($dbs->_group) {
			if ($cgroup!=$rs->{$dbs->_group}) {
				$tables->rows[]='<tr><th colspan="13" style="text-align:left;font-size:1.3em;background:#ccc;padding:5px 10px;">'.($dbs->_group=='year'?'ปี '.sg_date($rs->{$dbs->_group},'ปปปป'):$rs->{$dbs->_group}).'</th></tr>';
				$cgroup=$rs->{$dbs->_group};
				$no=0;
			}
		}
		$s1=array();
		if ($rs->s1amt>0) $s1[]='<a href="'.url('/paper/'.$rs->tpid.'/member/owner/post/s1').'" title="'.$rs->s1amt.' รายการ">ส๑</a>';
		if ($rs->s2amt>0) $s1[]='<a href="'.url('/paper/'.$rs->tpid.'/member/owner/post/s2').'" title="'.$rs->s2amt.' รายการ">ส๒</a>';
		if ($rs->Progresses) $s1[]=$rs->Progresses;
		$sreport=$s1?implode('<br />',$s1):'-';

		$mReportArray=array();
		if ($rs->m1amt>0) $mReportArray[]='<a href="'.url('/paper/'.$rs->tpid.'/member/owner/post/m1').'" title="'.$rs->m1amt.' รายการ">ง๑</a>';
		if ($rs->Financials) $mReportArray[]=$rs->Financials;
		$mReportText=$mReportArray?implode('<br />',$mReportArray):'-';

		$m1Text='';
		if ($rs->m1Text) {
			foreach (explode(',', $rs->m1Text) as $item) {
				list($trid,$period,$status)=explode('|', $item);
				$m1Text.='<span><a class="project-report-status-'.$status.'" href="'.url('paper/'.$rs->tpid.'/member/owner/post/m1/period/'.$period).'" title="รายงาน ง๑ งวดที่ '.$period.' สถานะ '.$statusText[$status].'">#'.$period.'</a></span>';
			}
		}

		$m2Text=$rs->m2amt>0 ? '<a href="'.url('/paper/'.$rs->tpid.'/member/owner/post/m2').'" title="'.$rs->m2amt.' รายการ">&#10004;</a>' : ' ';

		$s1Text='';
		if ($rs->s1Text) {
			foreach (explode(',', $rs->s1Text) as $item) {
				list($trid,$period,$status)=explode('|', $item);
				$s1Text.='<span><a class="project-report-status-1" href="'.url('paper/'.$rs->tpid.'/member/owner/post/s1/period/'.$period).'">#'.$period.'</a></span>';
			}
		}

		$s2Text='';
		if ($rs->s2Text) {
			foreach (explode(',', $rs->s2Text) as $item) {
				list($trid,$period,$status)=explode('|', $item);
				$s2Text.='<span><a class="project-report-status-1" href="'.url('paper/'.$rs->tpid.'/member/owner/post/s2/period/'.$period).'">#'.$period.'</a></span>';
			}
		}

		$s3Text=$rs->s3amt>0 ? '<a href="'.url('/paper/'.$rs->tpid.'/member/owner/post/s3').'" title="'.$rs->s3amt.' รายการ">&#10004;</a>' : ' ';

		$followReportArray=array();
		$followStatusList[0]=array('&frac12;','ร่างรายงาน');
		$followStatusList[1]=array('&#10004;','เสร็จสมบูรณ์');
		$followStatusList[2]=array('&#8855;','ปิดรายงาน');
		if (isset($rs->followPeriod1)) {
			$followReportArray[]='<a class="period-1" data-flag="'.$rs->followPeriod1.'" href="'.url('/paper/'.$rs->tpid.'/member/trainer/post/follow/period/1').'" title="ติดตามครั้งที่ 1 '.$followStatusList[$rs->followPeriod1][1].'">'.$followStatusList[$rs->followPeriod1][0].'</a>';
		}
		if (isset($rs->followPeriod2)) {
			$followReportArray[]='<a class="period-2" data-flag="'.$rs->followPeriod2.'" href="'.url('/paper/'.$rs->tpid.'/member/trainer/post/follow/period/2').'" title="ติดตามครั้งที่ 2 '.$followStatusList[$rs->followPeriod2][1].'">'.$followStatusList[$rs->followPeriod2][0].'</a>';
		}
		if (isset($rs->followPeriod3)) {
			$followReportArray[]='<a class="period-3" data-flag="'.$rs->followPeriod3.'" href="'.url('/paper/'.$rs->tpid.'/member/trainer/post/follow/period/3').'" title="ติดตามครั้งที่ 3 '.$followStatusList[$rs->followPeriod3][1].'">'.$followStatusList[$rs->followPeriod3][0].'</a>';
		}

		if ($rs->Follows) $followReportArray[]=$rs->Follows;
		$followReportText=$followReportArray?implode(' ',$followReportArray):' ';

		$estimationReportText = $rs->estimationAmt > 0 ? '<a href="'.url('project/'.$rs->tpid.'/eval.valuation').'" title="'.$rs->estimationAmt.' รายการ">&#10004;</a>' : ' ';

		unset($row);
		$row[]=++$no;
		$row[]=$rs->agrno;
		$row[]=SG\getFirst($rs->prid,'???').'<br /><span>('.$rs->prtype.')</span>';
		//$row[]=i()->uid!=$rs->uid?'<a href="'.url('project/list',array('u'=>$rs->uid)).'"><img class="ownerphoto" src="'.model::user_photo($rs->username,false).'" width="32" height="32" alt="'.htmlspecialchars($rs->ownerName).'" title="'.htmlspecialchars($rs->ownerName).'" /></a>':'';
		$row[]='<a href="'.url('project/list',array('u'=>$rs->uid)).'"><img class="ownerphoto" src="'.model::user_photo($rs->username,false).'" width="32" height="32" alt="'.htmlspecialchars($rs->ownerName).'" title="'.htmlspecialchars($rs->ownerName).'" /></a>';
		$row[]='<a href="'.url('paper/'.$rs->tpid).'"><strong>'.SG\getFirst($rs->title,'<em>ไม่ระบุชื่อ</em>').'</strong></a>'
						.($rs->area?'<span>'.$rs->area.'</span>':'')
						.($rs->projectset_name?'<span>'.$rs->projectset_name.'</span>':'')
						.($rs->departmentName?'<span><strong>'.$rs->departmentName.'</strong> เมื่อ '.sg_date($rs->created,'ว ดด ปป H:i').' น.</span>':'');
		$row[]=($rs->calendar_totals?$rs->calendar_totals:'-').' / '.($rs->owner_reply?$rs->owner_reply:'-');
		if (projectcfg::enable('trainer')) $row[]=$rs->trainer_reply?$rs->trainer_reply:'-';
		if (projectcfg::enable('ง.1')) $row[]=$m1Text;
		if (projectcfg::enable('ส.1')) $row[]=$s1Text;
		if (projectcfg::enable('ส.2')) $row[]=$s2Text;
		if (projectcfg::enable('ง.2')) $row[]=$m2Text;
		if (projectcfg::enable('ส.3')) $row[]=$s3Text;
		$row[]='<a href="'.url('paper/'.$rs->tpid.'/situation/kamsaiindicator').'">'.$rs->kamsaiindicator.'</a>';
		$row[]='<a href="'.url('project/'.$rs->tpid.'/info.eat').'">'.$rs->schooleat.'</a>';
		$row[]='<a href="'.url('project/'.$rs->tpid.'/info.weight').'">'.$rs->weight.'</a>';
		if (projectcfg::enable('trainer')) $row[]=$followReportText;
		if (projectcfg::enable('ประเมิน')) $row[]=$estimationReportText;
		$row[]=$rs->last_report?sg_date($rs->last_report,'ว ดด ปป H:i'):'';
		$projectStatus=$rs->project_status;
		if ($rs->status==_DRAFT AND $rs->project_status=='ระงับโครงการ') $projectStatus='รอลบโครงการ';
		$row[]=$projectStatus;
		$row['config']=array('class'=>($rs->projectset?'project-set-'.$rs->projectset.' ':'').('project-status-'.$rs->project_statuscode));

		$tables->rows[]=$row;
	}
	$ret .= $tables->build();



	$ret.='<a name="ref">';
	if (projectcfg::enable('ส.1')) $ret.='ส๑ รายงานความก้าวหน้า<br />';
	if (projectcfg::enable('ส.2')) $ret.='ส๒ รายงานการติดตามสนับสนุนโครงการ<br />';
	if (projectcfg::enable('ส.3')) $ret.='ส๓ รายงานการดำเนินงานฉบับสมบูรณ์<br />';
	if (projectcfg::enable('ส.4')) $ret.='ส๔ รายงานสรุปเมื่อสิ้นสุดระยะเวลาโครงการ<br />';
	if (projectcfg::enable('ง.1')) $ret.='ง๑ รายงานการเงินประจำงวด<br />';
	if (projectcfg::enable('ง.2')) $ret.='ง๒ รายงานสรุปการเงินปิดโครงการ<br />';
	if (projectcfg::enable('trainer')) $ret.='ต. แบบบันทึกการติดตามสนับสนุนโครงการ<br />';
	if (projectcfg::enable('ประเมิน')) $ret.='ป. คือ แบบประเมินคุณค่าโครงการ<br />';
	foreach ($status as $v) $ret.=$v[0].' คือ '.$v[1].'<br />';
	$ret.='</a>';
	//$ret.=print_o($dbs,'$dbs');
	$ret.='<style type="text/css">
	table#project-list>thead>tr>th:nth-child(n+8) {white-space:normal;}
	</style>';
	return $ret;
}
?>