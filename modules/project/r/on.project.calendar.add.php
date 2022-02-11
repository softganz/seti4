<?php
/**
* Model Name
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function on_project_calendar_add($post, $para) {
	$post->targetpreset = intval(abs(sg_strip_money($post->targetpreset)));
	$post->budget = abs(sg_strip_money($post->budget));
	if ($post->mainact <= 0) $post->mainact = NULL;

	$post->calid = $post->id;

	$stmt = 'INSERT INTO %project_activity%
		(`calid`, `calowner`, `mainact`, `targetpreset`, `target`, `budget`)
		VALUES
		(:calid, :calowner, :mainact, :targetpreset, :targetdetail, :budget)';

	mydb::query($stmt, $post);

	$post->uid = i()->uid;
	$post->created = date('U');
	$post->sorder = mydb::select('SELECT MAX(`sorder`) `maxOrder` FROM %project_tr% WHERE `tpid` = :tpid AND `formid` = "info" AND `part` = "activity" LIMIT 1', $post)->maxOrder + 1;

	$stmt = 'INSERT INTO %project_tr%
		(`tpid`, `calid`, `sorder`, `formid`, `part`, `uid`, `date1`, `date2`, `num1`, `num2`, `detail1`, `text1`, `created`)
		VALUES
		(:tpid, :calid, :sorder, "info", "activity", :uid, :from_date, :to_date, :budget, :targetpreset, :title, :detail, :created)
		';

	mydb::query($stmt, $post);

	//debugMsg('Project Calendar Add '.mydb()->_query);
	//echo mydb()->_query; die;
	model::watch_log('project','Calendar add from calendar','เพิ่มกิจกรรมย่อย หมายเลข '.$post->id.' : ' .$post->title,NULL,$post->tpid);
	return $ret;
}
?>