<?php
use Softganz\DB;

function module_forum_install() {
	$queryResult = [];

	$ret = '<h3>Project installation</h3>';

	// create content type
	$content = (object) [
		'type' => 'forum',
		'name' => 'Forum',
		'module' => 'forum',
		'has_title' => 1,
		'title_label' => 'Title',
		'has_body' => 1,
		'body_label' => 'Detail',
		'custom' => 1,
		'modify' => 1,
		'locked' => 1,
		'publish' => 'publish',
		'comment' => 2
	];

	BasicModel::create_content_type($content);

	DB::query([
		'INSERT INTO %topic_types%
		(`type`,`name`,`module`,`has_title`,`title_label`,`has_body`,`body_label`,`custom`,`modified`,`locked`)
		VALUES (
			"forum","Forum","forum",1,"Topic",1,"Body",1,1,1
		)
		ON DUPLICATE KEY UPDATE
		`type` = "forum"'
	]);

	$queryResult[] = R('query');

	if (cfg('topic_options_forum') === NULL) {
		$topic_options->publish = 'publish';
		$topic_options->comment = 2;
		cfg_db('topic_options_forum',$topic_options);
	}

	$ret.='<p><strong>Installation completed.</strong></p>';
	$ret.='<ul><li>'.implode('</li><li>',$queryResult).'</li></ul>';

	return $ret;
}
?>