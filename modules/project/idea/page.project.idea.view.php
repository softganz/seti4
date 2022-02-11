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
function project_idea_view($self, $tpid, $action = NULL) {
	$info=R::Model('project.idea.get',$tpid);

	if (empty($info)) return message('error','ไม่พบไอเดียที่ระบุ');

	$isAdmin=user_access('administer projects');

	R::View('project.toolbar',$self,$info->title,'idea',$info);

	switch ($action) {
		case 'todev':
			if ($isAdmin) R::Model('project.idea.createdev',$info);
			location('project/idea/view/'.$tpid);
			break;
	}

	$ret.='<div class="container __view">';
	$ret.='<div class="box">';
	$ret.='<h4>ชื่อโครงการ</h4>';
	$ret.='<p><strong>'.$info->title.'</strong></p>';

	$ret.='<h4>ความเป็นมาและสถานการณ์</h4>';
	$ret.=sg_text2html($info->problem);

	$ret.='<h4>กิจกรรมหลักที่จะดำเนินโครงการ</h4>';
	$ret.=sg_text2html($info->activity);

	$ret.='<h4>ผู้เสนอแนวคิด</h4>';
	$ret.=$info->byname;
	$ret.='</div>';

	$ret.='<div class="row -flex">';
	$ret.='<div class="col -md-6"><h3>ความเห็นของพี่เลี้ยง</h3><div class="form-item"><textarea class="form-text -fill"></textarea></div><div class="form-item"><button class="btn -primary">บันทึกความเห็น</button></div></div>';
	$ret.='<div class="col -md-1">&nbsp;</div>';
	$ret.='<div class="col -md-5"><h3>ความเห็นของผู้ทรงคุณวุฒิ</h3><div class="form-item"><textarea class="form-text -fill"></textarea></div><div class="form-item"><button class="btn -primary">บันทึกความเห็น</button></div></div>';
	$ret.='</div><!-- row -->';

	if ($isAdmin) {
		$ret.='<h3>ผลการพิจารณาแนวคิดเบื้องต้น/เอกสารเชิงหลักการ</h3>';
		$ret.='<div class="row -flex">';
		$ret.='<div class="col -md-6 -sg-text-center">';
		if (in_array($info->ideastatus,array(1,2,3))) {
			$ret.='<a class="btn" href="'.url('project/idea/view/'.$tpid.'/cancel').'"><i class="icon -cancel"></i><span>ไม่ผ่าน</a>';
		} else if ($info->ideastatus==5) {
			$ret.='<a class="btn -disabled" href="'.url('project/idea/view/'.$tpid.'/cancel').'"><i class="icon"></i><span>ผ่านการพิจารณาเรียบร้อย</a>';
		}
		$ret.='&nbsp;</div>';
		$ret.='<div class="col -md-1">&nbsp;</div>';
		$ret.='<div class="col -md-5 -sg-text-center">';
		if ($info->proposalId) {
			$ret.='<a class="btn -info" href="'.url('project/develop/'.$tpid).'"><i class="icon -viewdoc"></i><span>พัฒนาโครงการ</a>';
		} else {
			$ret.='<a class="sg-action btn -primary" href="'.url('project/idea/view/'.$tpid.'/todev').'"><i class="icon -save -white" data-confirm="โครงการผ่านเข้าสู่กระบวนการพัฒนาโครงการ กรุณายืนยัน?"></i><span>ผ่าน เข้าสู่กระบวนการพัฒนาโครงการ >></a>';
		}
		if ($info->followId) {
			$ret.='<br /><br /><a class="btn -info" href="'.url('paper/'.$tpid).'"><i class="icon -viewdoc"></i><span>ติดตามโครงการ</a>';
		}
		$ret.='</div>';
		$ret.='</div><!-- row -->';
	}
	$ret.='</div><!-- container -->';

	R::Model('reaction.add', $tpid, 'TOPIC.VIEW');

	//$ret.=print_o($info,'$info');
	$ret.='<style type="text/css">
	.__view .row { margin-bottom:48px;}
	</style>';
	return $ret;
}
?>