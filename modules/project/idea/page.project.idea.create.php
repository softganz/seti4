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
function project_idea_create($self,$parentid=NULL) {
	R::View('project.toolbar',$self,'Project Concept Paper','idea',$info);

	$data=(object)post('topic');
	if (post('save') && $data->title && $data->byname) {
		$tpid=R::Model('project.idea.create',$data);
		//$ret.='Save TPID='.$tpid;
		//$ret.=print_o(post(),'post()');
		location('project/idea/view/'.$tpid);
		return $ret;
	}

	// Show form
	$data=(object)post('topic');
	$data->parent=$parentid;
	$ret.='<div class="container">';
	$ret.='<div class="row">';
	$ret.='<div class="col -md-4"><h4>Concept Paper</h4><p>Concept Paper คือ แนวคิดที่จะทำโครงการ เป็นการบรรยายความเป็นมาและสถานการณ์ พร้อมกิจกรรมกิจกรรมหลัก ๆ ที่จะดำเนินการ หากแนวคิดเป็นที่น่าสนใจ แนวคิดนี้ก็จะเข้าสู่กระบวนการพัฒนาโครงการต่อไป</p><!-- <a href="'.url('project/idea').'" class="btn">Read more</a> --></div>';

	$ret.='<div class="col -md-8"><h4>เสนอโครงร่าง/แนวคิดโครงการเพื่อขอรับการสนับสนุนทุน</h4>';
	$ret.=R::View('project.idea.form',$data);
	$ret.='<p>กรุณาป้อนชื่อแนวคิดโครงการในช่องด้านบนแล้วเติมรายเอียดให้ครบถ้วน</p></div>';
	$ret.='</div>';
	$ret.='</div>';

	//$ret.=print_o(post(),'post()');

	return $ret;
}
?>