<?php
/**
* iBuy :: Green Smile Comment Render
* Created 2020-06-29
* Modify  2020-06-29
*
* @param Object $self
* @param Int $msgId
* @param Int $commentId
* @return String
*/

$debug = true;

function ibuy_green_activity_comment($self, $threadId, $commentId = NULL) {
	$ret = '';

	mydb::where('m.`thread` = :thread AND m.`tagname` LIKE "GREEN,COMMENT" AND m.`touid` IS NULL', ':thread', $threadId);

	$stmt = 'SELECT
			m.*
		, u.`username`, u.`name` `posterName`
		FROM %msg% m
			LEFT JOIN %users% u USING(`uid`)
		%WHERE%
		ORDER BY m.`msgid` ASC
		';

	$dbs = mydb::select($stmt);
	//$ret.='<pre>'.mydb()->_query.'</pre>';

	$ui = new Ui('div','ui-card ibuy-green-activity-comment');
	$ui->addId('ibuy-green-activity-comment');

	foreach ($dbs->items as $rs) {
		$ui->add(
			R::View(
				'ibuy.green.activity.comment.render',
				$rs,
				'{page: "'.(R()->appAgent ? 'app' : '').'"}'
			),
			'{class: "-'.strtolower(str_replace(',', '-', $rs->tagname)).' -hover-parent", id: "ibuy-activity-comment-'.$rs->msgid.'"}'
		);
	}

	$ret .= $ui->build(true).'<!-- ibuy-green-activity-comment -->';

	//$ret .= print_o($dbs, '$dbs');

	return $ret;
}
?>