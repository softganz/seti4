<?php
/**
* Project detail
*
* @param Object $self
* @param Object $rs
* @param Object $para
* @return String
*/
function view_project_nav_default($rs,$options) {
	$tpid=$rs->tpid;
	$submenu=q(2);
	$ret='';
	$isAdmin=user_access('administer projects');
	$isEdit=user_access('administer projects','edit own project content',$rs->uid) || project_model::is_owner_of($tpid) || project_model::is_trainer_of($tpid);


	if ($rs->tpid) {
		$ui=new Ui(NULL,'ui-nav -info');
		$ui->add('<a href="'.url('project').'"><i class="icon -home"></i><span class="-hidden">โครงการ</span></a>');
		$ret.=$ui->build();

		// Check for notify message
		if (i()->ok) {
			$where=array();
			$where=sg::add_condition($where,'tr.`period`<3 AND tu.uid=:uid','uid',i()->uid);

			$stmt='SELECT p.`tpid`, tr.`trid`, tr.`formid`, tr.`part`,
								tr.`period`, tr.`flag`, tr.`date2`,
								t.`title`
							FROM %project% p
								LEFT JOIN %topic% t USING(tpid)
								LEFT JOIN %project_tr% tr ON tr.`tpid`=p.`tpid` AND tr.`formid`="info" AND tr.`part`="period" AND tr.`flag`<2
									LEFT JOIN %topic_user% tu ON tu.`tpid`=p.`tpid` AND tu.`uid`=:uid '
							.($where?'WHERE '.implode(' AND ',$where['cond']):'')
							.' ORDER BY tr.date2 ASC';

			$dbs=mydb::select($stmt,$where['value']);
			$notifyM1=0;
			$notifyMsg='';
			foreach ($dbs->items as $item) {
				$CheckInX = explode("-", $item->date2);
				$CheckOutX =  explode("-", date('Y-m-d'));
				$date1 =  mktime(0, 0, 0, $CheckInX[1],$CheckInX[2],$CheckInX[0]);
				$date2 =  mktime(0, 0, 0, $CheckOutX[1],$CheckOutX[2],$CheckOutX[0]);
				 $dayLate =($date2 - $date1)/(3600*24);
	     	if ($dayLate <= 0) continue;
	     	$notifyM1++;
	 			$notifyMsg.='<li><a href="'.url('paper/'.$item->tpid.'/member/owner/post/m1').'"><strong>รายงานการเงิน ง.1 งวด '.$item->period.' ล่าช้า '.$dayLate.' วัน</strong><br />'.$item->title.'</a></li>'._NL;
			}

			if (cfg('project.notify.activityreport')) {
				$where=array();
				$where=sg::add_condition($where,'a.`calowner`=1');
				$where=sg::add_condition($where,'p.`project_status`="กำลังดำเนินโครงการ"');
				$where=sg::add_condition($where,'DATEDIFF(:curdate , c.`to_date`)>'.cfg('project.notify.activityreport'),'curdate',date('Y-m-d'));
				$where=sg::add_condition($where,'tu.`uid`=:uid AND (tr.trid IS NULL OR (tr.trid IS NOT NULL AND tr.flag=0))','uid',i()->uid);

 				$stmt='SELECT DATEDIFF(:curdate,c.`to_date`) late,
									p.`tpid`, p.`project_status`,
									tr.`trid`, tr.`formid`, tr.`part`, tr.`period`, tr.`flag`, tr.`date2`, t.`title`, c.`title` calendar_title, c.`to_date`
									FROM `sgz_project` p
									LEFT JOIN `sgz_topic` t USING(tpid)
									LEFT JOIN `sgz_calendar` c USING(`tpid`)
									LEFT JOIN `sgz_project_activity` a ON a.`calid`=c.`id`
									LEFT JOIN `sgz_project_tr` tr ON tr.`calid`=c.`id`
									LEFT JOIN `sgz_topic_user` tu ON tu.`tpid`=p.`tpid` AND tu.`uid`=:uid '
								.($where?'WHERE '.implode(' AND ',$where['cond']):'')
								.' ORDER BY c.to_date ASC';
				$dbs=mydb::select($stmt,$where['value']);

				$notifyActivity=0;
				$notifyActivityMsg='';
				foreach ($dbs->items as $item) {
					$lockDate=project_model::get_lock_report_date($item->tpid);
					if ($lockDate && $lockDate>=$item->to_date) continue;
		     	$notifyActivity++;
		 			$notifyActivityMsg.='<div><a href="'.url('paper/'.$item->tpid.'/member/owner').'"><strong>บันทึกกิจกรรมล่าช้า '.$item->late.' วัน<br />'.sg_date($item->to_date,'d-m-ปปปป').' : '.$item->calendar_title.'</strong><br />'.$item->title.'</a></div>'._NL;
				}
 			}
		}

		$ui=new Ui(NULL,'ui-nav -info');
		$ui->add('<a href="'.url('paper/'.$rs->tpid).'" title="รายละเอียดโครงการ"><i class="icon -viewdoc"></i><span>รายละเอียด</span></a>');
		if (projectcfg::enable('แผนภาพ')) $ui->add('<a href="'.url('paper/'.$rs->tpid.'/info/diagram').'" title="แผนภาพเชิงระบบของโครงการ"><i class="icon -diagram"></i><span>แผนภาพ</span></a>');
		if (projectcfg::enable('ปฏิทิน')) $ui->add('<a href="'.url('project/'.$rs->tpid.'/info.calendar').'" title="ปฏิทินกิจกรรมของโครงการ"><i class="icon -calendar"></i><span>ปฏิทินโครงการ</span></a>');
		$ret.=$ui->build()._NL;

		$ui=new Ui(NULL,'ui-nav -report');
		$ui->add('<a href="'.url('project/'.$rs->tpid.'/info.action').'" title="รายงานผู้รับผิดชอบโครงการ"><i class="icon -view"></i><span>รายงานผู้รับผิดชอบ</span></a>'
						.($notifyActivity ? '<div id="project-notify">'._NL.'<a href="#">'.$notifyActivity.'</a><div><h3>คำเตือน</h3>'.$notifyActivityMsg.'</div>'._NL.'</div>'._NL
															: ''));
		if (projectcfg::enable('trainer')) $ui->add('<a href="'.url('project/'.$rs->tpid.'/operate.trainer').'" title="รายงาของพี่เลี้ยงโครงการ" ><i class="icon -view"></i><span>รายงานพี่เลี้ยง</span></a>');
		$ui->add('<a href="'.url('project/'.$rs->tpid.'/eval.valuation').'" title="แบบฟอร์มการเก็บข้อมูลเพื่อการประเมินแบบมีโครงสร้าง"><i class="icon -material">assessment</i><span>'.tr('แบบประเมิน').'</span></a>');
		if ($isEdit) $ui->add('<a href="'.url('project/'.$tpid.'/info.dashboard').'"><i class="icon -dashboard"></i><span>แผงควบคุม</span></a>');
		$ret.=$ui->build()._NL;

		if (in_array($submenu, array('owner','info.action'))) {
			$ui=new Ui(NULL,'ui-nav -ownerreport');
			if (projectcfg::enable('ง.1')) $ui->add('<a href="'.url('project/'.$rs->tpid.'/operate').'">รายงาน ง.1</a>');
			if (projectcfg::enable('ส.1')) $ui->add('<a href="'.url('paper/'.$rs->tpid.'/owner/s1').'">รายงาน ส.1</a>');
			if (projectcfg::enable('ส.2')) $ui->add('<a href="'.url('paper/'.$rs->tpid.'/owner/s2').'">รายงาน ส.2</a>');
			if (projectcfg::enable('ง.2')) $ui->add('<a href="'.url('project/'.$rs->tpid.'/operate.m2').'">รายงาน ง.2</a>');
			if (projectcfg::enable('ส.3')) $ui->add('<a href="'.url('paper/'.$rs->tpid.'/owner/s3').'">รายงาน ส.3</a>');
			if (projectcfg::enable('ส.4')) $ui->add('<a href="'.url('paper/'.$rs->tpid.'/owner/s4').'">รายงาน ส.4</a>');

			$ui->add('<a href="'.url('paper/'.$rs->tpid.'/info/evaluation').'" title="การติดตามประเมินผลการดำเนินกิจกรรมของโครงการ">สรุปกิจกรรม</a>');

			if (cfg('project.sendfile') && substr($rs->project->agrno,0,2)=='54' && (user_access('administer projects') || project_model::is_owner_of($rs->tpid) || project_model::is_trainer_of($rs->tpid))) $ui->add('<a href="'.url('paper/'.$rs->tpid.'/owner/activity').'">'.tr('ส่งไฟล์รายงานกิจกรรม').'</a></li><li><a href="'.url('paper/'.$rs->tpid.'/owner/progress').'">'.tr('ส่งไฟล์รายงานความก้าวหน้า').'</a></li><li><a href="'.url('paper/'.$rs->tpid.'/owner/financial').'">'.tr('ส่งไฟล์รายงานการเงิน').'</a>');
			$ret.=$ui->build()._NL;
		} else if ($submenu=='trainer') {
			$ui=new Ui(NULL,'ui-nav -ownerreport');
			$ui->add('<a href="'.url('paper/'.$rs->tpid.'/trainer/follow').'" title="แบบบันทึกการติดตามสนับสนุนโครงการ">'.tr('บันทึกการติดตาม').'</a>');
			if (cfg('project.sendfile') && substr($rs->project->agrno,0,2)=='54' && (user_access('administer projects') || project_model::is_trainer_of($rs->tpid))) $ui->add('<a href="'.url('paper/'.$rs->tpid.'/trainer/activity').'">'.tr('ส่งไฟล์รายงานกิจกรรม').'</a></li><li><a href="'.url('paper/'.$rs->tpid.'/trainer/follow').'">'.tr('ส่งไฟล์รายงานการติดตาม').'</a>');
			$ret.=$ui->build()._NL;
		}
	} else {
		$ui=new Ui(NULL,'ui-nav -info');
		$ui->add('<a href="'.url('project').'"><i class="icon -home"></i><span class="-hidden">โครงการ</span></a>');
		$ui->add('<a href="'.url('project/list').'"><i class="icon -list"></i><span class="-hidden">รายชื่อโครงการ</span></a>');
		$ui->add('<a href="'.url('project/develop').'"><i class="icon -module"></i><span class="-hidden">พัฒนาโครงการ</span></a>');
		$ui->add('<a href="'.url('project/report').'"><i class="icon -report"></i><span class="-hidden">วิเคราะห์ภาพรวม</span></a>');
		if ($isAdmin) $ui->add('<a href="'.url('project/admin').'"><i class="icon -setting"></i><span class="-hidden">จัดการระบบ</span></a>');
		$ret.=$ui->build();
	}

	return $ret;
}
?>