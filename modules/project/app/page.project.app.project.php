<?php
/**
 * Project Application
 *
 * @param Object $topic
 */
function project_app_project($self) {
	project_model::init_app_mainpage();

	$stmt='SELECT
					  t.`tpid`
					, t.`title`
					, t.`uid`
					, u.`username`
					, u.`name` `poster`
					, t.`created`
					, p.`budget`
					, p.`date_from`
					, p.`date_end`
				FROM %project% p
					LEFT JOIN %topic% t USING(`tpid`)
					LEFT JOIN %users% u ON u.`uid`=t.`uid`
				ORDER BY `tpid` DESC';
$dbs=mydb::select($stmt);

	$ret.='<div class="card">'._NL;
	foreach ($dbs->items as $rs) {
		$ret.='<div class="carditem -activity">'._NL;
		$ret.='<div class="owner">';
		$ret.='<span class="owner-photo"><img class="owner-photo" src="'.model::user_photo($rs->username).'" width="32" height="32" alt="'.$rs->poster.'" /></span>';
		$ret.='<span class="owner-name">';
		$ret.=($rs->username?'<a href="'.url('profile/'.$rs->uid).'">':'').$rs->poster.($rs->username?'</a>':'');
		$ret.='</span>';
		$ret.='<span class="created">เมื่อ '.sg_date($rs->created,'ว ดด ปป H:i').'</span>';
		$ret.='</div><!-- owner -->'._NL;

		$ret.='<h3 class="title"><a class="sg-action" data-rel="#main" href="'.url('project/app/view/'.$rs->tpid).'">'.$rs->title.'</a></h3>'._NL;
		$ret.='<div class="summary">ระยะเวลา '.sg_date($rs->date_from,'ว ดด ปปปป').' - '.sg_date($rs->date_end,'ว ดด ปปปป').' <br />งบประมาณ '.number_format($rs->budget,2).' บาท<br /></div>'._NL;
		$ret.='<h4 class="subtitle">'.$rs->activityTitle.'</h4>'._NL;
		$ret.='<div class="timestamp">'.sg_date($rs->action_date,'ว ดด ปป').'</div>';
		$ret.='<div class="summary">'._NL.$rs->real_work._NL.'</div>'._NL;
		$ret.='<div class="photo"></div>'._NL;
		$ret.='<div class="status"></div>'._NL;
		$ret.='<div class="action"></div>'._NL;
		$ret.='</div><!-- carditem -->'._NL;
		//$ret.=print_o($rs,'$rs');
	}
	$ret.='</div>';

	//$ret.=print_o($dbs,'$dbs');
	return $ret;
}
?>