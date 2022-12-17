<?php
function module_project_install() {
	$ret='<h3>Project installation</h3>';

	// create content type
	$content->type='forum';
	$content->name='Forum';
	$content->module='forum';
	$content->has_title=1;
	$content->title_label='Title';
	$content->has_body=1;
	$content->body_label='Detail';
	$content->custom=1;
	$content->modify=1;
	$content->locked=1;
	$content->publish='publish';
	$content->comment=2;
	CommonModel::create_content_type($content);

	/*
	$stmt = 'INSERT INTO %topic_types%
		(`type`,`name`,`module`,`has_title`,`title_label`,`has_body`,`body_label`,`custom`,`modified`,`locked`)
		VALUES (
			"forum","Forum","forum",1,"Topic",1,"Body",1,1,1
		)';
	mydb::query($stmt);
	$queryResult[]=mydb()->_query;

	if (cfg('topic_options_forum') == NULL) {
		$topic_options->publish = 'publish';
		$topic_options->comment = 2;
		cfg_db('topic_options_forum',$topic_options);
	}
	*/



	$ret.='<p><strong>Installation completed.</strong></p>';
	$ret.='<ul><li>'.implode('</li><li>',$queryResult).'</li></ul>';

	return $ret;
}
?>