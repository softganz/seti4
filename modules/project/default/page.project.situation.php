<?php
/**
* Project situation
*
* @param Object $self
* @return String
*/
function project_situation($self, $tpid = NULL) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get',$tpid, '{initTemplate: true}');
	$tpid = $projectInfo->tpid;

	//if (!$tpid) return message('error', 'ไม่มีข้อมูลโครงการที่ระบุ');

	R::View('project.toolbar', $self, $projectInfo->title, $projectInfo->submodule, $projectInfo);

	//project_model::set_toolbar($self,'สถานการณ์โครงการ');

	if ($tpid) {
		$stmt = 'SELECT `formid`,COUNT(*) amt FROM %project_tr% WHERE `tpid`=:tpid AND formid IN ("weight","schooleat","kamsaiindi","learn") AND `part`="title" GROUP BY `formid` ';
		$dbs=mydb::select($stmt,':tpid',$tpid);
		foreach ($dbs->items as $rs) $counts[$rs->formid]=$rs->amt;

		$tables = new Table();
		$tables->addConfig('showHeader',false);
		$tables->thead = array('','amt'=>'จำนวนบันทึก');
		$tables->rows[] = array('<td colspan="2"><h3>สถานการณ์โครงการ</h3></td>');
		$tables->rows[] = '<header>';

		$tables->rows[] = array('<a href="'.url('project/'.$tpid.'/info.eat').'">สถานการณ์การกินอาหารและการออกกำลังกายของนักเรียน</a>',number_format($counts['schooleat']));
		$tables->rows[] = array('<a href="'.url('project/'.$tpid.'/info.weight').'">สถานการณ์ภาวะโภชนาการนักเรียน</a>',number_format($counts['weight']));

		$ret .= $tables->build();
	} else {
		$ui=new ui('','-project -situation');
		$ui->add('<a href="'.url('project/situation/weight').'">สรุปข้อมูลภาวะโภชนาการ - จำแนกตามชั้นแรียน</a>');
		$ui->add('<a href="'.url('project/report/weightbyschool').'">สรุปข้อมูลภาวะโภชนาการ (น้ำหนักตามเกณฑ์ส่วนสูง) - จำแนกตามโรงเรียน</a> <img src="/library/img/new.1.gif" />');
		$ui->add('<a href="'.url('project/report/heightbyschool').'">สรุปข้อมูลภาวะโภชนาการ (ส่วนสูงตามเกณฑ์อายุ) - จำแนกตามโรงเรียน</a> <img src="/library/img/new.1.gif" />');
		$ui->add('<sep>');
		$ui->add('<a href="'.url('project/situation/eat').'">สถานการณ์การกินอาหารและการออกกำลังกายของนักเรียน</a>');
		$ui->add('<a href="">ข้อมูลการสำรวจสถานการณ์การกินอาหารและออกกำลังกายของนักเรียนโดย สอส.</a>');
		$ui->add('<sep>');
		$ui->add('<a href="'.url('project/report/checkweightinput').'">ตรวจสอบบันทึกสถานการณ์ภาวะโภชนาการ - การบันทึกของโรงเรียนแต่ละเทอม/ครั้งที่</a> <img src="/library/img/new.1.gif" />');
		$ui->add('<a href="'.url('project/report/weightcheck').'">ตรวจสอบบันทึกสถานการณ์ภาวะโภชนาการ - จำนวนนักเรียนผิดพลาด</a>');
		$ui->add('<a href="'.url('project/situation/list').'">สถานะการบันทึกข้อมูลรายงานสถานการณ์ภาวะโภชนาการนักเรียน</a>');

		$ret.=$ui->build(NULL,'-main');
	}
	return $ret;
}
?>