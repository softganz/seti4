<?php
function school($self) {
	$ret.='<img class="banner" src="//dailycaller.com/wp-content/uploads/2014/03/kids-raising-hands-Getty-Images.jpg" />';
	$ret.='<h2 class="welcome">{tr:Welcome to School Kids Management}</h2>';

	$isRegister=mydb::select('SELECT COUNT(*) `amt` FROM %school% WHERE `uid`=:uid LIMIT 1',':uid',i()->uid)->amt;
	if (!$isRegister) {
		$ret.='<div class="school-register"><a class="btn -primary" href="'.url('school/register').'"><span>{tr:ลงทะเบียนโรงเรียนใหม่}</span></a></div>';
	}

	$ret.='<div class="container">';
	$ret.='<div class="row -flex">';
	$ret.='<div class="col -md-5">';

	$ui=new Ui(NULL,'ui-card school-main-menu');
	$ui->add('<a class="btn -primary" href="'.url('school/report').'"><img src="//softganz.com/img/img/school-analysis.jpg" /><span>รายงาน</span></a>');
	$ui->add('<a class="btn -primary" href="'.url('school/my').'"><img src="//softganz.com/img/img/school-kids.jpg" /><span>บันทึก</span></a>');

	//$ui->add('<a class="btn -primary" href="'.url('school/kids').'"><img src="//softganz.com/img/img/school-kids.jpg" /><span>{tr:Kids Personal}</span></a>');
	//$ui->add('<a class="btn -primary" href="'.url('school/summary').'"><img src="//softganz.com/img/img/school-summary.jpg" /><span>{tr:Kids Summary}</span></a>');
	//$ui->add('<a class="btn -primary" href="'.url('school/report').'"><img src="//softganz.com/img/img/school-analysis.jpg" /><span>{tr:Situation Analysis}</span></a>');
	if ($isRegister) {
		//$ui->add('<a class="btn -primary" href="'.url('school/my').'"><img src="//softganz.com/img/img/school-dashboard.jpg" /><span>{tr:Dashboard}</span></a>');
	}
	$ret.=$ui->build();

	$ret.='</div><!-- col -->';

	$ret.='<div class="col -md-7 -summary-report">';
	$ret.=R::Page('school.report.map.overview', NULL);
	$ret.='</div><!-- col -->';
	$ret.='</div><!-- row -->';


	$ret.='<h2 class="title -md-12">รายชื่อโรงเรียนที่เข้าร่วม</h2>';
	$stmt='SELECT o.* FROM %school% s LEFT JOIN %db_org% o USING(`orgid`)';
	$allSchool=mydb::select($stmt);
	$ui=new Ui('div','ui-card school-card row -x-flex');
	foreach ($allSchool->items as $rs) {
		$ui->add('<a href="'.url('school/info/'.$rs->orgid).'"><img src="//softganz.com/img/img/school-house.jpg" width="200" /><h3 class="card-title">'.$rs->name.'</h3></a><p class="card-detail">'.$rs->address.'</p>','{class:"col -md-4"}');
	}
	$ret.=$ui->build();
	$ret.='</div><!-- container -->';




	$ret.='<style type="text/css">
	.welcome {text-align:center; padding:32px 0; font-size:3em;}
	.banner {width: 100%; height:340px;}
	.school-register {text-align:center; padding:48px 0;}
	.school-register .btn.-primary {padding:32px 64px; font-size:2em;}
	.title {padding:16px; margin: 32px 0; text-align: center; background-color:#4A2CA7; color:#fff;}
	.reportbar {background-color: transparent;}
	.col.-summary-report h2 {display:none;}
	</style>';

	$ret.='<script type="text/javascript">
	$.ajax({ 
   type : "GET", 
   url : "https://iapi.bot.or.th/Stat/Stat-ReferenceRate/DAILY_REF_RATE_V1/?start_period=2002-01-12&end_period=2002-01-15", 
   //beforeSend: function(xhr){xhr.setRequestHeader("api-key", "U9G1L457H6DCugT7VmBaEacbHV9RX0PySO05cYaGsm");},
   success : function(result) { 
       //set your variable to the result 
   	console.log(result)
   }, 
   error : function(result) { 
   	console.log("Error")
     //handle the error 
   } 
 });
 </script>';
	return $ret;
}
?>