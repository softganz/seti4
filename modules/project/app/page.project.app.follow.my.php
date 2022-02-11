<?php
/**
* Project :: My follow project
* Created 2021-01-27
* Modify  2021-01-27
*
* @param Object $self
* @return String
*
* @usage project/app/follow/my
*/

$debug = true;

function project_app_follow_my($self) {
	if (!i()->ok) {
		$ret = '<header class="header"><h3>Sign In</h3></header>'
			. R::View(
				'signform',
				'{
					time:-1,
					rel: "none",
					done: "reload"
				}'
			);
			//done: "'.(R()->appAgent ? 'load | ' : '').'load->clear:box:'.url(q()).'",

		$ret .= '<style type="text/css">
		.page.-main>header {text-align: center;}
		.login.-normal h3 {display: none;}
		.login .form-item.-edit-cookielength {display: none;}
		</style>';

		$retx .= '<style type="text/css">
		.form.signform .form-item {margin-bottom: 16px; position: relative;}
		.form.signform label {position: absolute; left: 8px; color: #666; font-style: italic; font-size: 0.9em; font-weight: normal;}
		.form.signform .form-text, .form.signform .form-password {padding-top: 24px;}
		.module-imed.-softganz-app .form-item.-edit-cookielength {display: none;}
		.login {padding: 0 32px; border: none; background-color: transparent;}
		.form.signform .ui-action>a {display: block;}
		</style>';
		return $ret;
	}

	// Data model
	$projectList = R::Model('project.follows', '{userId: "member"}');


	// View model
	$ret = '';

	new Toolbar($self, 'โครงการ');

	$setTables = new Table();
	$followTables = new Table();

	foreach ($projectList->items as $rs) {
		if ($rs->prtype == 'โครงการ') {
			$followTables->rows[] = array(
				'<a class="sg-action" href="'.url('project/app/follow/'.$rs->projectId).'" data-webview="'.$rs->title.'">'.$rs->title.'</a>',
			);
		} else {
			$setTables->rows[] = array(
				'<a class="sg-action" href="'.url('project/app/follow/'.$rs->projectId).'" data-webview="'.$rs->title.'">'.$rs->title.'</a>',
			);
		}
	}

	$ret .= $setTables->build();
	$ret .= $followTables->build();

	return $ret;
}
?>