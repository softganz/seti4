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
function project_set_list($self,$para=NULL) {

	$projectset = SG\getFirst($para->set,$_REQUEST['set']);
	$year = $_REQUEST['year'];

	R::View('project.toolbar',$self,SG\getFirst(cfg('project.title'),'Project management'),'set');

	$stmt = 'SELECT
		p.`tpid`, t.`title`
		FROM %project% p
			LEFT JOIN %topic% t USING(`tpid`)
		WHERE `prtype`="ชุดโครงการ" AND `project_status` IN ("กำลังดำเนินโครงการ","ดำเนินการเสร็จสิ้น")
		ORDER BY t.`weight` ASC
		';

	$dbs = mydb::select($stmt);

	$img='calendar.png';

	if ($dbs->_num_rows) {
		$ret .= '<div class="project-set-plan-list">';
		$ret .= '<h3>ชุดโครงการ</h3>';
		$ret.='<div class="ui-card -sg-flex project-plan-card">';
		foreach ($dbs->items as $rs) {
			$ret.='<div class="ui-item col -md-4">';
			$ret.='<a href="'.url('project/set/'.$rs->tpid).'"><img src="//softganz.com/img/img/'.$img.'" width="80%" /></a><a class="btn -primary" href="'.url('project/set/'.$rs->tpid).'">'.$rs->title.'</a>';
			$ret.='</div>';
		}
		$ret.='</div>';
		$ret.='</div>';
	}

	return $ret;
}
?>