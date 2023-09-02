<?php
function module_paper_install() {

	$stmt = 'INSERT IGNORE INTO %topic_types%
					(`type`,`name`,`module`,`has_title`,`title_label`,`has_body`,`body_label`,`custom`,`modified`,`locked`)
						VALUES (
							"page","Page",NULL,1,"Topic",1,"Body",1,1,0
						)';

	mydb::query($stmt);
	$queryResult[]=mydb()->_query;


	// create podcast content type
	if (cfg('topic_options_page')==NULL) {
		$topic_options = (Object) [
			'publish' => 'publish',
			'promote' => 0,
			'comment' => 0,
		];
		cfg_db('topic_options_page',$topic_options);
	}

	mydb::query(
		'INSERT IGNORE INTO %topic_types%
		(`type`,`name`,`module`,`has_title`,`title_label`,`has_body`,`body_label`,`custom`,`modified`,`locked`)
		VALUES (
			"story","Story",NULL,1,"Topic",1,"Body",1,1,0
		)'
	);
	$queryResult[] = mydb()->_query;

	if (cfg('topic_options_story') == NULL) {
		$topic_options = (Object) [
			'publish' => 'publish',
			'promote' => 1,
			'comment' => 2,
		];
		cfg_db('topic_options_story',$topic_options);
	}


	$ret .= implode('<br /><br />'._NL, $queryResult);

	return $ret;
}
?>