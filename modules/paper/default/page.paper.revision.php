<?php
/**
* Module Method
*
* @param Object $self
* @param Int $var
* @return String
*/

$debug = true;

function paper_revision($self, $tpid = NULL, $revid = NULL) {
	if ($revid) {
		$topic = paper_BasicModel::get_topic_by_id($tpid,NULL,$revid);
		$topic->reply=0;
		unset($topic->comment);
		//$ret .= print_o($topic, '$tpic');
		$ret .= R::Page('paper.view', NULL, $topic);
		//foreach (R::Page('paper.view', NULL, $topic) as $str)
		// if (is_string($str)) $ret.=$str;
		return $ret;
	}

	$topic = paper_BasicModel::get_topic_by_id($tpid);

	$stmt = 'SELECT r.revid,r.timestamp , u.name
							FROM %topic_revisions% r
								LEFT JOIN %users% u ON u.uid=r.uid
							WHERE tpid = :tpid ORDER BY r.revid DESC';
	$revisions=mydb::select($stmt, ':tpid', $tpid);

	$self->theme->title='Revisions for '.$topic->title;
	$ret.='<ul class="tabs"><li><a href="'.url('paper/'.$tpid).'">View</a></li><li><a href="'.url('paper/revision/'.$tpid).'">Revisions</a></li></ul>';
	$ret.='<ul>';
	foreach ($revisions->items as $rev) {
		$ret.='<li><a class="sg-action" href="'.url('paper/revision/'.$tpid.'/'.$rev->revid).'" data-rel="#revision-body">'.$rev->timestamp.' by '.$rev->name.'</a></li>';
	}
	$ret.='</ul>';
	$ret.='<div id="revision-body"></div>';

	return $ret;
}
?>