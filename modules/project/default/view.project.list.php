<?php
function view_project_list($dbs,$para = NULL) {
	$statusText = array(
		_PROJECT_DRAFTREPORT=>'เริ่มทำรายงาน',
		_PROJECT_COMPLETEPORT=>'แจ้งรายงานเสร็จสมบูรณ์',
		_PROJECT_LOCKREPORT=>'ผ่านการตรวจสอบของพี่เลี้ยงโครงการ',
		_PROJECT_PASS_HSMI=>'ผ่านการตรวจสอบของ '.cfg('project.grantpass'),
		_PROJECT_PASS_SSS=>'ผ่านการตรวจสอบของ '.cfg('project.grantby')
	);

	$tables = new Table();
	$tables->id='project-list';
	$tables->addClass('project-list');
	$tables->thead['no']='';
	//$tables->thead['agrno']='ข้อตกลงเลขที่';
	$tables->thead['prid']='รหัสโครงการ';
	$tables->thead['profile']='';
	$tables->thead['title']='ชื่อโครงการ';
	$tables->thead['amt calendar']='กิจกรรม<br />(ตามแผน)';
	$tables->thead['amt owner']='กิจกรรมในพื้นที่<br />(ทำแล้ว)';
	if (projectcfg::enable('trainer')) $tables->thead['amt trainer']='กิจกรรมพี่เลี้ยง<br />(ทำแล้ว)';
	if (projectcfg::enable('ง.1')) $tables->thead['amt Financial1']='ง๑<br /><a href="#ref" title="รายงานการเงิน ง๑"><sup>?</sup></a>';
	if (projectcfg::enable('ส.1')) $tables->thead['amt Progress-s1']='ส๑<br /><a href="#ref" title="รายงานความก้าวหน้า ส๑"><sup>?</sup></a>';
	if (projectcfg::enable('ส.2')) $tables->thead['amt Progress-s2']='ส๒<br /><a href="#ref" title="รายงานความก้าวหน้า ส๒"><sup>?</sup></a>';
	if (projectcfg::enable('ง.2')) $tables->thead['amt Financial2']='ง๒<br /><a href="#ref" title="รายงานการเงิน ง๒"><sup>?</sup></a>';
	if (projectcfg::enable('ส.3')) $tables->thead['amt Progress-s3']='ส๓<br /><a href="#ref" title="รายงานความก้าวหน้า ส๓"><sup>?</sup></a>';
	if (projectcfg::enable('trainer')) $tables->thead['Follow']='ต.<br /><a href="#ref" title="ต. คือ รายงานการติดตามของพี่เลี้ยง"><sup>?</sup></a>';
	if (projectcfg::enable('ประเมิน')) $tables->thead['amt Estimation']='ป.<br /><a href="#ref" title="ป. คือ แบบประเมินคุณค่าโครงการ"><sup>?</sup></a>';
	$tables->thead['date']='กิจกรรมล่าสุด';
	$tables->thead['status']='สถานะโครงการ';
	// $tables->thead='<thead><tr><th rowspan="2"></th><th>ข้อตกลงเลขที่</th></thead>';

	$no=$dbs->_start_row;
	$cgroup='';
	foreach ($dbs->items as $rs) {
		if ($dbs->_group) {
			if ($cgroup!=$rs->{$dbs->_group}) {
				$tables->rows[]='<tr><th colspan="16" style="text-align:left;font-size:1.3em;background:#ccc;padding:5px 10px;">'.($dbs->_group=='year'?'ปี '.sg_date($rs->{$dbs->_group},'ปปปป'):$rs->{$dbs->_group}).'</th></tr>';
				$cgroup=$rs->{$dbs->_group};
				$no=0;
			}
		}


		$s1=array();
		if ($rs->s1amt>0) $s1[]='<a href="'.url('project/'.$rs->tpid.'/operate.result').'" title="'.$rs->s1amt.' รายการ">ส๑</a>';
		if ($rs->s2amt>0) $s1[]='<a href="'.url('project/'.$rs->tpid.'/operate').'" title="'.$rs->s2amt.' รายการ">ส๒</a>';
		if ($rs->Progresses) $s1[]=$rs->Progresses;
		$sreport=$s1?implode('<br />',$s1):'-';


		$mReportArray=array();
		if ($rs->m1amt>0) $mReportArray[]='<a href="'.url('project/'.$rs->tpid.'/operate.m1').'" title="'.$rs->m1amt.' รายการ">ง๑</a>';
		if ($rs->Financials) $mReportArray[]=$rs->Financials;
		$mReportText=$mReportArray?implode('<br />',$mReportArray):'-';


		$m1Text='';
		if ($rs->m1Text) {
			foreach (explode(',', $rs->m1Text) as $item) {
				list($trid,$period,$status)=explode('|', $item);
				$m1Text.='<span><a class="project-report-status -status-'.$status.'" href="'.url('project/'.$rs->tpid.'/operate.m1/'.$period).'" title="รายงาน ง๑ งวดที่ '.$period.' สถานะ '.$statusText[$status].'">#'.$period.'</a></span>';
			}
		}


		$m2Text=$rs->m2amt>0 ? '<a href="'.url('project/'.$rs->tpid.'/operate.m2').'" title="'.$rs->m2amt.' รายการ">&#10004;</a>' : ' ';


		$s1Text='';
		if ($rs->s1Text) {
			foreach (explode(',', $rs->s1Text) as $item) {
				list($trid,$period,$status)=explode('|', $item);
				$s1Text.='<span><a class="project-report-status -status-1" href="'.url('project/'.$rs->tpid.'/operate.result/'.$period).'">#'.$period.'</a></span>';
			}
		}


		$s2Text='';
		if ($rs->s2Text) {
			foreach (explode(',', $rs->s2Text) as $item) {
				list($trid,$period,$status)=explode('|', $item);
				$s2Text.='<span><a class="project-report-status -status-1" href="'.url('project/'.$rs->tpid.'/operate.result/'.$period).'">#'.$period.'</a></span>';
			}
		}

		$s3Text=$rs->s3amt>0 ? '<a href="'.url('project/'.$rs->tpid.'/summary').'" title="'.$rs->s3amt.' รายการ">&#10004;</a>' : ' ';

		$followReportArray=array();
		$followStatusList[0]=array('&frac12;','ร่างรายงาน');
		$followStatusList[1]=array('&#10004;','เสร็จสมบูรณ์');
		$followStatusList[2]=array('&#8855;','ปิดรายงาน');
		if (isset($rs->followPeriod1)) {
			$followReportArray[]='<a class="period-1" data-flag="'.$rs->followPeriod1.'" href="'.url('project/'.$rs->tpid.'/operate.trainer/1').'" title="ติดตามครั้งที่ 1 '.$followStatusList[$rs->followPeriod1][1].'">'.$followStatusList[$rs->followPeriod1][0].'</a>';
		}
		if (isset($rs->followPeriod2)) {
			$followReportArray[]='<a class="period-2" data-flag="'.$rs->followPeriod2.'" href="'.url('project/'.$rs->tpid.'/operate.trainer/2').'" title="ติดตามครั้งที่ 2 '.$followStatusList[$rs->followPeriod2][1].'">'.$followStatusList[$rs->followPeriod2][0].'</a>';
		}
		if (isset($rs->followPeriod3)) {
			$followReportArray[]='<a class="period-3" data-flag="'.$rs->followPeriod3.'" href="'.url('project/'.$rs->tpid.'/operate.trainer/3').'" title="ติดตามครั้งที่ 3 '.$followStatusList[$rs->followPeriod3][1].'">'.$followStatusList[$rs->followPeriod3][0].'</a>';
		}


		if ($rs->Follows) $followReportArray[]=$rs->Follows;
		$followReportText=$followReportArray?implode(' ',$followReportArray):' ';

		$estimationReportText=$rs->estimationAmt>0 ? '<a href="'.url('project/'.$rs->tpid.'/eval.valuation').'" title="'.$rs->estimationAmt.' รายการ">&#10004;</a>' : ' ';


		unset($row);
		$row[] = '<td class="col -no" rowspan="2">'.(++$no).'</td>';
		//$row[] = '<td rowspan="2">'.$rs->agrno.'</td>';
		$row[] = '<td rowspan="2">'
						. SG\getFirst($rs->prid,'???').'<br />'
						. ' <span>('.$rs->prtype.')</span>'
						. ($rs->pryear ? '<span>ปี '.($rs->pryear+543).'</span>' : '')
						.'</td>';
		//$row[]=i()->uid!=$rs->uid?'<a href="'.url('project/list',array('u'=>$rs->uid)).'"><img class="ownerphoto" src="'.model::user_photo($rs->username,false).'" width="32" height="32" alt="'.htmlspecialchars($rs->ownerName).'" title="'.htmlspecialchars($rs->ownerName).'" /></a>':'';
		$row[] = '<td rowspan="2"><a href="'.url('project/list',array('u'=>$rs->uid)).'"><img class="ownerphoto" src="'.model::user_photo($rs->username,false).'" width="32" height="32" alt="'.htmlspecialchars($rs->ownerName).'" title="'.htmlspecialchars($rs->ownerName).'" /></a></td>';
		$row[]='<td colspan="12" class="col-title"><a href="'.url('project/'.$rs->tpid).'"><strong>'.SG\getFirst($rs->title,'<em>ไม่ระบุชื่อ</em>').'</strong></a>'
						.($rs->area?'<span>'.$rs->area.'</span>':'')
						.($rs->projectset_name?'<span>'.$rs->projectset_name.'</span>':'')
						.($rs->departmentName?'<span><strong>'.$rs->departmentName.'</strong> เมื่อ '.sg_date($rs->created,'ว ดด ปป H:i').' น.</span>':'')
						.'</td>';
		$row['config']=array('class'=>'-title-row');
		$tables->rows[]=$row;
		unset($row);
		$row[]='<td></td>';
		$row[]=$rs->calendar_totals?$rs->calendar_totals:'-';
		$row[]=$rs->owner_reply?$rs->owner_reply:'-';
		if (projectcfg::enable('trainer')) $row[]=$rs->trainer_reply?$rs->trainer_reply:'-';
		if (projectcfg::enable('ง.1')) $row[]=$m1Text;
		if (projectcfg::enable('ส.1')) $row[]=$s1Text;
		if (projectcfg::enable('ส.2')) $row[]=$s2Text;
		if (projectcfg::enable('ง.2')) $row[]=$m2Text;
		if (projectcfg::enable('ส.3')) $row[]=$s3Text;
		if (projectcfg::enable('trainer')) $row[]=$followReportText;
		if (projectcfg::enable('ประเมิน')) $row[]=$estimationReportText;
		$row[]=$rs->last_report?sg_date($rs->last_report,'ว ดด ปป H:i'):'';
		$projectStatus=$rs->project_status;
		if ($rs->status==_DRAFT AND $rs->project_status=='ระงับโครงการ') $projectStatus='รอลบโครงการ';
		$row[]='<span class="project-status-icon -status-'.$rs->project_statuscode.'">'.$projectStatus.'</span>';
		$row['config']=array('class'=>($rs->projectset?'project-set-'.$rs->projectset.' ':'').('project-status-'.$rs->project_statuscode));

		$tables->rows[]=$row;
	}


	$ret .= '<div class="-sg-scroll-width">';
	$ret .= $tables->build();
	$ret .= '</div><!-- -sg-scroll-width -->';

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
	return $ret;
}
?>