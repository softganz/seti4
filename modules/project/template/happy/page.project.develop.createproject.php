<?php
function project_develop_createproject_wait($self,$tpid) {


	$devInfo=R::Model('project.develop.get',$tpid);

	R::View('project.toolbar',$self,$devInfo->info->title,'develop',$devInfo->info);

	$isAdmin=$devInfo->RIGHT & _IS_ADMIN;

	if (empty($devInfo->tpid)) return 'No project';

	if (!$isAdmin) return message('error','access denied');


	if (SG\confirm()) {
		$stmt='INSERT IGNORE INTO %project%
						(`tpid`, `projectset`, `prtype`, `pryear`, `prid`, `budget`
						, `changwat`, `date_from`, `date_end`, `date_approve`
						, `prtrainer` )
						SELECT d.`tpid`, t.`parent`, "โครงการ", `pryear`, `prid`, `budget`, t.`changwat`
							, d.`date_from`, d.`date_end`, d.`date_from`
							, (SELECT GROUP_CONCAT(`name`) FROM %topic_user% tu LEFT JOIN %users% u USING(`uid`) WHERE `tpid`=:tpid AND `membership`="Trainer" GROUP BY `tpid`)
							FROM %project_dev% d
								LEFT JOIN %topic% t USING(`tpid`)
							WHERE `tpid`=:tpid LIMIT 1';
		mydb::query($stmt,':tpid',$tpid);
		//$ret.=mydb()->_query.'<br />';

		$data=project_model::get_develop_data($tpid);

		$project['prowner']=trim($data['owner-prename'].' '.$data['owner-name'].' '.$data['owner-lastname']);
		if ($data['owner-phone']) $prphone[]=$data['owner-phone'];
		if ($data['owner-mobile']) $prphone[]=$data['owner-mobile'];
		$project['prphone']=implode(',',$prphone);
		if ($data['coowner-1-name']) $prteam[]=trim($data['coowner-1-prename'].' '.$data['coowner-1-name'].' '.$data['coowner-1-lastname']);
		if ($data['coowner-2-name']) $prteam[]=trim($data['coowner-2-prename'].' '.$data['coowner-2-name'].' '.$data['coowner-2-lastname']);
		if ($data['coowner-3-name']) $prteam[]=trim($data['coowner-3-prename'].' '.$data['coowner-3-name'].' '.$data['coowner-3-lastname']);
		if ($data['coowner-4-name']) $prteam[]=trim($data['coowner-4-prename'].' '.$data['coowner-4-name'].' '.$data['coowner-4-lastname']);
		if ($data['coowner-5-name']) $prteam[]=trim($data['coowner-5-prename'].' '.$data['coowner-5-name'].' '.$data['coowner-5-lastname']);
		$project['prteam']=implode(' , ',$prteam);

		$project['target']='กลุ่มเป้าหมายหลัก'._NL._NL.$data['project-target']._NL._NL.(trim($data['target-secondary-detail'])!='' ? 'กลุ่มเป้าหมายรอง'._NL._NL.$data['target-secondary-detail']:'');
		$project['totaltarget']=$data['target-main-total']+$data['target-secondary-total'];

		$project['area']=$data['project-commune'];

		$stmt='UPDATE %project% SET
						`prowner`=:prowner, `prphone`=:prphone, `prteam`=:prteam
						, `target`=:target, `totaltarget`=:totaltarget
						, `area`=:area
					WHERE `tpid`=:tpid LIMIT 1';
		mydb::query($stmt,':tpid',$tpid,$project);
		//$ret.=mydb()->_query.'<br />';

		mydb::query('UPDATE %topic% SET `type`="project", `status`='._LOCKDETAIL.' WHERE `tpid`=:tpid LIMIT 1',':tpid',$tpid);
		//$ret.=mydb()->_query.'<br />';

		mydb::query('INSERT IGNORE INTO %topic_user% (`tpid`,`uid`,`membership`) SELECT `tpid`,`uid`,"Owner" FROM %topic% WHERE `tpid`=:tpid LIMIT 1',':tpid',$tpid);
		//$ret.=mydb()->_query.'<br />';

		mydb::query('UPDATE %project_tr% SET `flag`=1, `num2`=IFNULL(`num3`,0)+IFNULL(`num4`,0)+IFNULL(`num5`,0)+IFNULL(`num6`,0) WHERE `tpid`=:tpid AND `formid`="info" AND `part`="mainact"',':tpid',$tpid);
		//$ret.=mydb()->_query.'<br />';


		if (cfg('project.develop.autoaddmainact')) {
			// เพิ่มวัตถุประสงค์ และ กิจกรรมหลักของ สสส.
			$isObjectiveAdd=mydb::select('SELECT COUNT(*) amt FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="info" AND `part`="objective" AND `flag`=11 LIMIT 1',':tpid',$tpid)->amt;
			//$ret.='$isObjectiveAdd='.$isObjectiveAdd.'<br />';
			if ($isObjectiveAdd<=0) {
				$nextOrder=mydb::select('SELECT MAX(`sorder`) AS maxSorder FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="info" AND `part`="objective" LIMIT 1',':tpid',$tpid)->maxSorder+1;
				//$ret.='nextSorder='.$nextOrder.'<br />'.mydb()->_query.'<br />';

				// เพิ่มวัตถุประสงค์ของ สสส.
				// flag 0 = not lock, 1-10 = internal lock 11-?? = external lock
				$stmt='INSERT INTO %project_tr% (`tpid`,`parent`, `sorder`, `formid`, `part`, `flag`, `text1`, `text2`, `created`) VALUES (:tpid, 2, :sorder, "info", "objective", 11, "เพื่อพัฒนาศักยภาพผู้รับผิดชอบโครงการ และสนับสนุนการทำงานของโครงการ", "1. มีการเข้าร่วมการประชุมกับ สสส. สจรส.ม.อ. ไม่น้อยกว่าร้อยละ 75 ของจำนวนครั้งที่จัด'._NL.'2. มีการจัดทำป้าย \"สถานที่นี้ปลอดบุหรี่\" ติดตั้งในสถานที่จัดกิจกรรม'._NL.'3. มีการถ่ายภาพการดำเนินงานทุกกิจกรรม'._NL.'4. มีการจัดทำรายงานส่ง สสส. ตามระยะเวลาที่กำหนด", UNIX_TIMESTAMP() )';
				mydb::query($stmt,':tpid',$tpid, ':sorder',$nextOrder);
				$objectiveId=mydb()->insert_id;
				//$ret.=mydb()->_query.'<br />';


				$nextOrder=mydb::select('SELECT MAX(`sorder`) AS maxSorder FROM %project_tr% WHERE `tpid`=:tpid AND `formid`="info" AND `part`="mainact" LIMIT 1',':tpid',$tpid)->maxSorder+1;
				//$ret.='nextSorder='.$nextOrder.'<br />'.mydb()->_query.'<br />';

				// เพิ่ม 3 กิจกรรม ของ สสส.
				$stmt='INSERT INTO %project_tr% (`tpid`, `parent`, `formid`, `part`, `flag`, `sorder`, `detail1`, `num1`, `num2`) VALUES (:tpid, :parent, "info", "mainact", 11, :sorder, "การประชุมร่วมกับ สสส. สจรส.ม.อ. และพี่เลี้ยงผู้ติดตาม", 10000, 2)';
				mydb::query($stmt,':tpid',$tpid, ':parent',$objectiveId, ':sorder',$nextOrder);
				$actid=mydb()->insert_id;
				//$ret.=mydb()->_query.'<br />';
				$stmt='INSERT INTO %project_tr% (`tpid`,`parent`,`gallery`,`formid`,`part`,`created`) VALUES (:tpid,:objectiveId,:actid,"info","actobj",:created)';
				mydb::query($stmt,':tpid',$tpid,':objectiveId',$objectiveId,':actid',$actid,':created',date('U'));
				//$ret.=mydb()->_query.'<br />';

				$exp->expid=NULL;
				$exp->tpid=$tpid;
				$exp->mainactid=$actid;
				$exp->expcode=99;
				$exp->amt=1;
				$exp->times=1;
				$exp->unitprice=10000;
				$exp->unitname='ครั้ง';
				$exp->total=10000;
				$exp->detail='การประชุมร่วมกับ สสส. สจรส.ม.อ. และพี่เลี้ยงผู้ติดตาม';
				$exp->uid=NULL;
				$exp->flag=11;
				project_model::create_exptr($exp).mydb()->_query;

				$stmt='INSERT INTO %project_tr% (`tpid`, `parent`, `formid`, `part`, `flag`, `sorder`, `detail1`, `num1`, `num2`) VALUES (:tpid, :parent, "info", "mainact", 11, :sorder, "ทำป้ายสัญลักษณ์เขตปลอดบุหรี่ ถ่ายภาพกิจกรรม และจัดทำรายงาน", 3000, 2)';
				mydb::query($stmt,':tpid',$tpid, ':parent',$objectiveId, ':sorder',$nextOrder+1);
				$actid=mydb()->insert_id;
				//$ret.=mydb()->_query.'<br />';
				$stmt='INSERT INTO %project_tr% (`tpid`,`parent`,`gallery`,`formid`,`part`,`created`) VALUES (:tpid,:objectiveId,:actid,"info","actobj",:created)';
				mydb::query($stmt,':tpid',$tpid,':objectiveId',$objectiveId,':actid',$actid,':created',date('U'));
				//$ret.=mydb()->_query.'<br />';

				$exp->mainactid=$actid;
				$exp->unitprice=3000;
				$exp->total=3000;
				$exp->detail='ทำป้ายสัญลักษณ์เขตปลอดบุหรี่ ถ่ายภาพกิจกรรม และจัดทำรายงาน';
			}

			project_model::create_exptr($exp).mydb()->_query;
		}
		mydb::query('UPDATE %project% SET `budget`=(SELECT SUM(b.`num1`) FROM %project_tr% b WHERE `tpid`=:tpid AND `formid`="info" AND `part`="mainact") WHERE `tpid`=:tpid',':tpid',$tpid);
		//$ret.=mydb()->_query.'<br />';


		$ret.='<a href="'.url('paper/'.$tpid).'" target="_blank">ติดตามโครงการ</a>';

		//$ret.=print_o($data,'$data');
		return $ret;
	}


	$ret.='<nav><a class="sg-action btn -primary" data-rel="this" href="'.url('project/develop/'.$tpid.'/createproject').'" data-confirm="ยืนยันการสร้างโครงการติดตาม"><i class="icon -save -white"></i><span>ยืนยันการสร้างโครงการติดตาม</span></a></nav>';
	return $ret;
}
?>