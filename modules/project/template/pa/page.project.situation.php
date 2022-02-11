<?php
/**
* Project situation
*
* @param Object $self
* @return String
*/
function project_situation($self) {
	R::View('project.toolbar',$self,'สถานการณ์');

	//$ret.='<h3>วิเคราะห์ภาพรวม</h3>';
	//$sidebar='<h2>สถานการณ์</h2>';
	$sidebar.='<h3>สถานการณ์การเคลื่อนไหวทางกาย</h3>';
	$ui=new ui('','-project -situation');
	$ui->add('<a href="'.url('project/situation/weight').'">จำแนกตามกลุ่มเป้าหมาย</a>');
	$ui->add('<a href="'.url('project/situation/weight').'">จำแนกตาม Setting (บ้าน โรงเรียน องค์กร ชุมชน)</a>');
	$sidebar.=$ui->build();

	$sidebar.='<h3>สถานการณ์การอื่นๆ</h3>';

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

	$sidebar.=$ui->build(NULL,'-main');
	$self->theme->sidebar=$sidebar;



	// Main situation
	$ret.='<p class="notify">หมายเหตุ : ข้อมูลที่นำมาแสดงด้านล่างเป็นข้อมูลจำลองเพื่อทดสอบการนำเสนอเท่านั้น</p>';
	$graphYear = new Table();
	$graphYearProject = new Table();
	$stmt='SELECT
					  p.`pryear`
					, COUNT(*) `totalProject`
					, SUM(p.`budget`) `totalBudget`
					FROM %project% p
						LEFT JOIN %topic% t USING(`tpid`)
					WHERE t.`orgid`=:orgid
					GROUP BY `pryear`
					ORDER BY `pryear` ASC';
	$dbs=mydb::select($stmt,':orgid',$fundInfo->orgid);
	//$ret.=print_o($dbs,'$dbs');
	foreach ($dbs->items as $rs) {
		$graphYear->rows[]=array('string:Year'=>$rs->pryear+543,'number:Project'=>$rs->totalProject,'number:Budget'=>$rs->totalBudget);
		$graphYearProject->rows[]=array('string:Year'=>$rs->pryear+543,'number:Project'=>$rs->totalProject);
	}
	$graphYear->rows[]=array('string:target'=>'วัยเด็ก-วัยเรียน','number:Project'=>10,'number:Budget'=>10000);
	$graphYear->rows[]=array('string:target'=>'วัยทำงาน','number:Project'=>14,'number:Budget'=>50000);
	$graphYear->rows[]=array('string:target'=>'ผู้สูงอายุ','number:Project'=>7,'number:Budget'=>8000);
	$graphYear->rows[]=array('string:target'=>'กลุ่มเฉพาะ','number:Project'=>5,'number:Budget'=>12000);
	$graphYear->rows[]=array('string:target'=>'อื่นๆ','number:Project'=>4,'number:Budget'=>3000);

	$ret.='<div class="container">';
	$ret.='<div class="row">';

	$ret.='<div id="year-project" class="sg-chart -project" data-chart-type="col" data-series="2"><h3>จำนวนโครงการ/งบประมาณแต่ละปี</h3>'._NL.$graphYear->build().'</div>';

	$chartPie = new Table();
	$chartPie->rows[]=array('string:label'=>'ประเภทที่ 1','number:1'=>10000);
	$chartPie->rows[]=array('string:label'=>'ประเภทที่ 2','number:2'=>25000);
	$chartPie->rows[]=array('string:label'=>'ประเภทที่ 3','number:3'=>8000);
	$chartPie->rows[]=array('string:label'=>'ประเภทที่ 4','number:4'=>9000);
	$chartPie->rows[]=array('string:label'=>'ประเภทที่ 5','number:5'=>2000);
	$ret.='<div id="fund-type" class="sg-chart -type" data-chart-type="pie"><h3>แผนภูมิแสดงการสนับสนุนงบประมาณตามแผนงาน</h3>'.$chartPie->build().'</div>'._NL;
	$ret.='</div><!-- row -->';
	$ret.='</div><!-- container -->';

	head('googlegraph','<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>');



	// 5x5x5 = 5 กลุ่มวัย 5 Setting 5 Intervention
	$tables = new Table();
	$tables->addClass('-center');
	$tables->thead=array('<h3>ช่วงวัย</h3>','การจัดการความรู้และนวัตกรรม','การสร้างพื้นที่สุขภาวะต้นแบบ','การขับเคลื่อนเชิงนโยบายและการพัฒนาระบบ','การพัฒนาขีดความสามารถของคน องค์กร และเครือข่าย');
	$tables->rows[]=array('วับเด็ก-วัยเรียน','<i class="icon -up"></i><br />Amount','<i class="icon -up"></i><br />Amount','<i class="icon -up"></i><br />Amount','<i class="icon -down"></i><br />Amount');
	$tables->rows[]=array('วัยทำงาน','<i class="icon -up"></i><br />Amount','<i class="icon -up"></i><br />Amount','<i class="icon -down"></i><br />Amount','<i class="icon -down"></i><br />Amount');
	$tables->rows[]=array('ผู้สูงอายุ','<i class="icon -down"></i><br />Amount','<i class="icon -up"></i><br />Amount','<i class="icon -down"></i><br />Amount','<i class="icon -down"></i><br />Amount');
	$tables->rows[]=array('กลุ่มเฉพาะ','<i class="icon -down"></i><br />Amount','<i class="icon -up"></i><br />Amount','<i class="icon -up"></i><br />Amount','<i class="icon -down"></i><br />Amount');
	$tables->rows[]=array('อื่นๆ','<i class="icon -up"></i><br />Amount','<i class="icon -up"></i><br />Amount','<i class="icon -down"></i><br />Amount','<i class="icon -up"></i><br />Amount');
	$ret.=$tables->build();

	$tables = new Table();
	$tables->addClass('-center');
	$tables->thead=array('<h3>Setting</h3>','การจัดการความรู้และนวัตกรรม','การสร้างพื้นที่สุขภาวะต้นแบบ','การขับเคลื่อนเชิงนโยบายและการพัฒนาระบบ','การพัฒนาขีดความสามารถของคน องค์กร และเครือข่าย');
	$tables->rows[]=array('บ้าน','<i class="icon -up"></i><br />Amount','<i class="icon -up"></i><br />Amount','<i class="icon -down"></i><br />Amount','<i class="icon -down"></i><br />Amount');
	$tables->rows[]=array('โรงเรียน','<i class="icon -down"></i><br />Amount','<i class="icon -up"></i><br />Amount','<i class="icon -down"></i><br />Amount','<i class="icon -down"></i><br />Amount');
	$tables->rows[]=array('องค์กร','<i class="icon -up"></i><br />Amount','<i class="icon -up"></i><br />Amount','<i class="icon -up"></i><br />Amount','<i class="icon -down"></i><br />Amount');
	$tables->rows[]=array('ชุมชน','<i class="icon -up"></i><br />Amount','<i class="icon -up"></i><br />Amount','<i class="icon -down"></i><br />Amount','<i class="icon -down"></i><br />Amount');
	$ret.=$tables->build();

	$tables = new Table();
	$tables->addClass('-center');
	$tables->thead=array('<h3>เครือข่าย</h3>','การจัดการความรู้และนวัตกรรม','การสร้างพื้นที่สุขภาวะต้นแบบ','การขับเคลื่อนเชิงนโยบายและการพัฒนาระบบ','การพัฒนาขีดความสามารถของคน องค์กร และเครือข่าย');
	$tables->rows[]=array('เดินวิ่ง','<i class="icon -down"></i><br />Amount','<i class="icon -up"></i><br />Amount','<i class="icon -down"></i><br />Amount','<i class="icon -down"></i><br />Amount');
	$tables->rows[]=array('จักรยาน','<i class="icon -up"></i><br />Amount','<i class="icon -up"></i><br />Amount','<i class="icon -up"></i><br />Amount','<i class="icon -down"></i><br />Amount');
	$tables->rows[]=array('กีฬา','<i class="icon -up"></i><br />Amount','<i class="icon -down"></i><br />Amount','<i class="icon -down"></i><br />Amount','<i class="icon -down"></i><br />Amount');
	$tables->rows[]=array('??','<i class="icon -up"></i><br />Amount','<i class="icon -up"></i><br />Amount','<i class="icon -up"></i><br />Amount','<i class="icon -down"></i><br />Amount');
	$ret.=$tables->build();
	$ret.='<p>Amount = จำนวนองค์กร/จำนวนโครงการ/งบประมาณ/กลุ่มเป้าหมาย/ผู้เข้าร่วม</p>';
	//$ret.='</div>';

	$ret.='<style type="text/css">
	.item td {width:20%; height:64px;vertical-align:middle;}
	.icon.-up {border:2px green solid;border-radius:50%;}
	.icon.-down {border:2px red solid;border-radius:50%;}
	</style>';


	return $ret;
}
?>