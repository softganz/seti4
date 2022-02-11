<?php
/**
* Project evaluation
*
* @param Object $self
* @param Object $topic
* @param Object $para
* @param Object $body
* @return String
*/
function project_form_evaluation($self,$topic,$para,$body) {
	$tpid=$topic->tpid;
	$self->theme->title=$topic->title;

	$stmt='SELECT tr.trid, c.id, c.title, c.location, c.from_date, c.to_date, tr.part,
			tr.date1 date_do,
			IFNULL(tr.text1,c.detail) goal_plan,
			tr.text2 goal_do,
			tr.text3 result_plan,
			tr.text4 result_do,
			tr.text5 problem,
			a.mainact,
			ma.detail1 mainact_title
		FROM %calendar% c
			LEFT JOIN %project_tr% tr ON tr.calid=c.id
			LEFT JOIN %project_activity% a ON a.calid=c.id
			LEFT JOIN %project_tr% ma ON ma.trid=a.mainact
		WHERE c.tpid=:tpid
		ORDER BY `from_date` ASC';
	$dbs=mydb::select($stmt,':tpid',$tpid);

	$is_edit=($topic->project->project_statuscode==1) && (user_access('administer projects','edit own project content',$topic->uid) || project_model::is_owner_of($topic->tpid) || project_model::is_trainer_of($topic->tpid));
	$editclass=$is_edit?'editable':'';

	$ret.='<div id="project-evaluation" class="inline-edit" url="'.url('project/edit/tr').'">'._NL;

	$tables = new Table();
	$tables->thead='<thead>'._NL.'<tr><th colspan="2">ระยะเวลา</th><th colspan="2">เป้าหมาย/วิธีการ</th><th colspan="2">ผลการดำเนินงาน</th><th rowspan="2">ปัญหา/อุปสรรค/แนวทางแก้ไข</th></tr>'._NL.'<tr><th>ตามแผน</th><th>ปฏิบัติจริง</th><th>ตามแผน</th><th>ปฏิบัติจริง</th><th>ตามแผน</th><th>ปฏิบัติจริง</th></tr>'._NL.'</thead>'._NL;
	foreach ($dbs->items as $rs) {
		list($yy,$mm,$dd)=explode('-',$rs->date_do);
		$tables->rows[]=array(
			'<td colspan="7">'
			.'<h4>'.($rs->trid?'<a href="'.url('paper/'.$topic->tpid.'/member/'.$rs->part,NULL,'tr-'.$rs->trid).'">':'').++$no.'. '.$rs->title.($rs->trid?'</a>':'').'</h4> '
			.'<p>กิจกรรมหลัก : '.(is_null($rs->mainact_title)?'ไม่ระบุ':$rs->mainact_title).'</p>'
			.'</td>'
		);

		$tables->rows[]=array(
			sg_date($rs->from_date,'ววว ว ดด ปป').($rs->to_date && $rs->to_date!=$rs->from_date ? ' - '.sg_date($rs->to_date,'ววว ว ดด ปป') : ''),
			!$rs->trid && $is_edit?'<p><a class="button" href="'.url('paper/'.$topic->tpid.'/member/owner','calid='.$rs->id).'" title="เขียนบันทึกกิจกรรม">บันทึกกิจกรรม</a></p>':
			($rs->date_do?sg_date($rs->date_do,'ววว ว ดด ปป'):''),
			sg_text2html($rs->goal_plan),
			sg_text2html($rs->goal_do),
			sg_text2html($rs->result_plan),
			sg_text2html($rs->result_do),
			sg_text2html($rs->problem),
			'config'=>array('calid'=>$rs->id,'tr'=>$rs->trid)
		);
	}

	$ret .= $tables->build();

	$ret.='</div><!--project-evaluation-->';

	unset($body->comment,$body->comment_form,$body->docs);
	return $ret;
}
?>