<?php
/**
* Model Name
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function on_project_calendar_edit($post, $para) {
	$post->targetpreset = intval(abs(sg_strip_money($post->targetpreset)));
	$post->budget = abs(sg_strip_money($post->budget));
	if ($post->mainact <= 0) $post->mainact = NULL;

 	$post->calid = $post->id;

	$stmt = 'INSERT INTO %project_activity%
		(`calid`, `calowner`, `mainact`, `targetpreset`, `target`, `budget`)
		VALUES
		(:calid, :calowner, :mainact, :targetpreset, :targetdetail, :budget)
		ON  DUPLICATE KEY UPDATE
		  `calowner` = :calowner
		, `mainact` = :mainact
		, `targetpreset` = :targetpreset
		, `target` = :targetdetail
		, `budget` = :budget';

	mydb::query($stmt, $post);

	$stmt = 'UPDATE %project_tr% SET
		  `detail1` = :title
		, `num1` = :budget
		, `num2` = :targetpreset
		, `date1` = :from_date
		, `date2` = :to_date
		, `text1` = :detail
		WHERE `tpid` = :tpid AND `formid` = "info" AND `part` = "activity" AND `calid` = :calid
		LIMIT 1';

	mydb::query($stmt, $post);

	//debugMsg('Project Calendar Edit '.mydb()->_query);
	return $ret;
}
?>