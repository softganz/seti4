<?php
function r_project_develop_follow_create_extend($devInfo) {
	$tpid = $devInfo->tpid;

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
	return $ret;
}
?>