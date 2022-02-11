<?php
/**
* Project idea
* Content :
* 	- ข้อมูลโครงการ
*			- ชื่อโครงการ
*			- ความเป็นมาและสถานการณ์
*			- กิจกรรมหลักที่จะดำเนินโครงการ
* 	- ข้อมูลผู้ขอทุน
*			- ชื่อ หน่วยงาน โทร อีเมล์
*/
function project_idea($self) {
	R::View('project.toolbar',$self,'Project Concept Paper','idea',$info);

	$ret.='<div class="container __home">';
	$ret.='<div class="row">';
	//$ret.='<div class="col -md-4 -info"><h4>Concept Paper</h4><p>Concept Paper คือ แนวคิดที่จะทำโครงการ บลา บลา ....<span class="notify">(รอแก้ไขข้อความ)</span></p><a href="'.url('project/idea').'" class="btn">Read more</a></div>';

	/*
	$ret.='<div class="col -md-0"><h4>เสนอแนวคิดเบื้องต้น/เอกสารเชิงหลักการ</h4><p>เสนอแนวคิดเบื้องต้น/เอกสารเชิงหลักการ เพื่อให้คณะกรรมการพิจารณาความเป็นไปได้ในการเข้าร่วมโครงการกิจกรรมทางกาย</p>';
	$ret.=R::View('project.idea.form');
	$ret.='<p>คลิกในช่อง "ชื่อโครงการ" ด้านบน แล้วป้อนรายเอียดในแบบฟอร์มให้ครบถ้วน</p><p align="right"><a id="form-toogle" href="javascript:void(0)" style="display:none;"><i class="icon -upload"></i></a></p></div>';
	*/
	$ret.='</div>';
	$ret.='</div>';


	$ret.='<div class="container">';
	$ret.='<div class="row">';
	$ret.='<div class="col -md-6">';
	$ret.='<h4>Great Idea</h4>';
	$stmt='SELECT i.`tpid`,i.`title`,i.`byname`,i.`created` FROM %project_idea% i LEFT JOIN %topic% t USING(`tpid`) ORDER BY t.`view` DESC';
	$dbs=mydb::select($stmt);

	$tables = new Table();
	$tables->thead=array('no'=>'','ชื่อแนวคิดโครงการ','center -by'=>'เสนอโดย','date -created'=>'เมื่อ');
	$no=0;
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
											++$no,
											'<a href="'.url('project/idea/view/'.$rs->tpid).'">'.$rs->title.'</a>',
											$rs->byname,
											sg_date($rs->created,'d ดด ปปปป')
											);
	}
	$ret.=$tables->build();
	$ret.='</div><!-- col -->';

	$ret.='<div class="col -md-1">&nbsp;</div>';

	$ret.='<div class="col -md-5">';
	$ret.='<h4>Incoming Idea</h4>';
	$stmt='SELECT i.`tpid`,i.`title`,i.`byname`,i.`created` FROM %project_idea% i LEFT JOIN %topic% t USING(`tpid`) ORDER BY `tpid` DESC';
	$dbs=mydb::select($stmt);

	$tables = new Table();
	$tables->thead=array('no'=>'','ชื่อแนวคิดโครงการ','center -by'=>'เสนอโดย','date -created'=>'เมื่อ');
	$no=0;
	foreach ($dbs->items as $rs) {
		$tables->rows[]=array(
											++$no,
											'<a href="'.url('project/idea/view/'.$rs->tpid).'">'.$rs->title.'</a>',
											$rs->byname,
											sg_date($rs->created,'d ดด ปปปป')
											);
	}
	$ret.=$tables->build();
	$ret.='</div><!-- col -->';
	$ret.='</div><!-- row -->';
	$ret.='</div><!-- container -->';

	$ret.='<style type="text/css">
	.module-project .__idea .__home {margin:20px 0;}
	.module-project .__idea .__home .col {margin-bottom:32px;}
	.module-project .__idea .__home .col.-info {text-align:center;}
	.module-project .__idea .__home .col>h4,.container.-project-idea .col>p {margin:0 10px;}
	.module-project .__idea .__home .col.-info>a {margin:32px 0;}
	.module-project .__idea .__home .col>form {margin:24px 16px;}
	.module-project .__idea .__home .__formcreate .form-item {display:none;}
	.module-project .__idea .__home .__formcreate .form-item.-edit-topic-title,
	.module-project .__idea .__home .__formcreate .form-item.-edit-save {display: block;}
	</style>';
	$ret.='<script type="text/javascript">
	$("#project-idea-form #edit-topic-title").click(function() {
		$("#project-idea-form .form-item").css({display:"block"});
		$("#form-toogle").show();
	});
	$("#form-toogle").click(function() {
		$("#project-idea-form .form-item").hide()
		$("#form-item-edit-topic-title,#form-item-edit-save").show()
		$("#form-toogle").hide();
		console.log("click")
	});
	</script>';
	return $ret;
}
?>