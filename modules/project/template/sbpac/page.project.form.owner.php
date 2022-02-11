<?php
/**
* Project owner
*
* @param Object $self
* @param Object $topic
* @param Object $para
* @param Object $body
* @return String
*/
function project_form_owner($self,$topic,$para,$body) {
	if ($topic->project->sector==1 || cfg('project.sector')=='same') {
		$ret.='<div class="sg-tabs">';
		$ret.='<ul class="tabs"><li class="-active"><a href="#activity">รายงานผลการทำกิจกรรม</a></li><li><a href="#monthly">รายงานผลการดำเนินงานประจำเดือน</a></li></ul>'._NL;
		$ret.='<div id="activity">';
		$ret.='<h3>บันทึกผลการทำกิจกรรม</h3>';
		$ret.=R::Page('project.form.send_activity',$self,$topic,$para,$body,'owner');
		$ret.='<h3>บันทึกรายงานกิจกรรมของพื้นที่</h3>';
		$ret.=R::Page('project.form.show_activity',$self,$topic,$para,$body,true,'owner');
		$ret.='</div>';
		$ret.='<div id="monthly" class="-hidden">'._NL;
		$ret.='<h3>บันทึกผลการดำเนินงานประจำเดือน</h3>';
		$ret.=R::Page('project.form.monthly',$self,$topic,$para,$body,true)._NL;
		$ret.='</div><!-- monthly -->'._NL;

		$ret.='</div><!-- sg-tabs-->';
	} else {
		$ret.='<h3>บันทึกผลการดำเนินงานประจำเดือน</h3>';
		$ret.='<div id="monthly">'._NL;
		$ret.=R::Page('project.form.monthly',$self,$topic,$para,$body,true)._NL;
		$ret.='</div><!-- monthly -->'._NL;
		//$ret.=print_o($topic,'$topic');
	}
	unset($body->comment,$body->comment_form,$body->docs);
	return $ret;
}
?>