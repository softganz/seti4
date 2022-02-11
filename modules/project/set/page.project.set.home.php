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
function project_set_home($self,$para=NULL) {

	R::View('project.toolbar',$self, 'ชุดโครงการ','set');

	$stmt='SELECT
					p.`tpid`, t.`title`
					FROM %project% p
						LEFT JOIN %topic% t USING(`tpid`)
					WHERE `prtype`="ชุดโครงการ" AND `project_status` IN ("กำลังดำเนินโครงการ","ดำเนินการเสร็จสิ้น")
					ORDER BY t.`weight` ASC
					';
	$dbs=mydb::select($stmt);

	$img='project-01.png';

	if ($dbs->_num_rows) {
		$ret.='<div class="container">';
		$ret.='<h2>ชุดโครงการ</h2>';
		$ret.='<div class="ui-card row -flex project-set-card">';
		foreach ($dbs->items as $rs) {
			$ret.='<div class="ui-item">';
			$ret.='<a href="'.url('project/set/'.$rs->tpid).'"><img src="//softganz.com/img/img/'.$img.'" width="80%" /></a><nav><a class="btn -link" href="'.url('project/set/'.$rs->tpid).'">'.$rs->title.'</a></nav>';
			$ret.='</div>';
		}
		$ret.='</div>';
		$ret.='</div>';
	}

	$ret .= '<style type="text/css">
	.project-set-card {flex-wrap: wrap; justify-content: space-between;}
	.project-set-card .ui-item {width: 200px;}
	.project-set-card .ui-item img {display: block; margin: 0 auto 16px;}
	.project-set-card .ui-item nav {text-align: center;}
	</style>';
	return $ret;
}
?>