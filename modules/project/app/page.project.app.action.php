<?php
/**
* Project :: App Action
* Created 2021-01-21
* Modify  2021-12-14
*
* @return Widget
*
* @usage project/app/action
*/

import('widget:appbar.nav.php');

class ProjectAppAction extends Page {
	function build() {
		if (!i()->ok) return $this->signForm();

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'กิจกรรม',
				'navigator' => new AppBarNavWidget(['configName' => 'project.my', 'userSigned' => true]),
			]), // AppBar
			'body' => new Widget([
				'children' => [
					// Show Activity Post Button
					new Card([
						'id' => 'project-chat-box',
						'class' => 'ui-card project-chat-box',
						'children' => [
							'<div class="ui-item">'
							. '<div><img src="'.model::user_photo(i()->username).'" width="40" height="40" />&nbsp;'
							. '<a class="sg-action form-text" href="'.url('project/app/action/form').'" placeholder="เขียนบันทึกการทำกิจกรรม" data-rel="#project-activity-card" data-width="480" data-height="100%" x-data-webview="บันทึกการทำกิจกรรม">เขียนบันทึกการทำกิจกรรม</a>&nbsp;'
							. '<a class="sg-action btn -link" href="'.url('project/app/action/form').'" data-rel="#project-activity-card" data-width="480" data-height="100%" data-webview="บันทึกการทำกิจกรรม"><i class="icon -camera"></i><span>Photo</span></a></div>'
							. '</div>'
						],
					]), // Card

					// Show project activity
					'<section id="project-activity-card" class="sg-load" data-url="'.url('project/app/activity', ['u' => i()->uid]).'">'._NL,
					'<div class="loader -rotate" style="width: 64px; height: 64px; margin: 32px auto; display: block;"></div>',

					'</section><!-- project-app-activity -->',

					// '<section id="template" class="-hidden">'
					// 	. '<div id="project-app-action-select" class="-hidden"><header class="header">'._HEADER_BACK.'<h3>เลือกโครงการ</h3></header>'.$actionSelectCard->build().'</div>'
					// 	. '<div id="project-app-follow-select" class="-hidden"><header class="header">'._HEADER_BACK.'<h3>เลือกโครงการ</h3></header>'.$followSelectCard->build().'</div>'
					// 	. '</div>',

				], // children
			]), // Widget
		]);
	}

	function signForm() {
		return '<header class="header"><h3>Sign In</h3></header>'
			. R::View(
				'signform',
				'{
					time:-1,
					rel: "none",
					done: "reload"
				}'
			)
			. '<style type="text/css">
				.page.-main>header {text-align: center;}
				.login.-normal h3 {display: none;}
				.login .form-item.-edit-cookielength {display: none;}
				</style>';
	}
}
?>
<?php
/**
* Project :: App Action
* Created 2021-01-21
* Modify  2021-01-21
*
* @param Object $self
* @return String
*
* @usage project/app/action
*/

$debug = true;

function project_app_action($self) {
	$toolbar = new Toolbar($self,'กิจกรรม');

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
	$actionSelectCard = R::View('project.select', '{"data-rel": "'.(R()->appAgent ? '' : '').'", "data-done" : "close", "data-webview": "ส่งรายงานประจำเดือน", retUrl: "'.url('project/app/month/$id').'"}');
	$followSelectCard = R::View('project.select', '{"data-rel": "'.(R()->appAgent ? 'none' : '').'", "data-done" : "close", "data-webview": "โครงการ", retUrl: "'.url('project/app/follow/$id').'"}');

	$isEmployee = mydb::select(
		'SELECT p.`tpid` FROM %project% p LEFT JOIN %topic% t ON t.`tpid` = p.`tpid` LEFT JOIN %topic_user% u ON u.`tpid` = p.`tpid` AND u.`uid` = :uid
			WHERE u.`uid` = :uid AND p.`project_status` = "กำลังดำเนินโครงการ" AND p.`ownertype` IN ( :ownerType )',
		':uid', i()->uid,
		':ownerType', 'SET-STRING:'.implode(',', array(_PROJECT_OWNERTYPE_GRADUATE, _PROJECT_OWNERTYPE_STUDENT, _PROJECT_OWNERTYPE_PEOPLE))
	)->count();

	// View model
	$ret = '';

	$toolbar = new Toolbar($self,'กิจกรรม');

	// Show main navigator
	$toolbarNav = new Ui(NULL, 'ui-nav');
	//$toolbarNav->add('<a class="sg-action" href="'.url('project/app',array('u' => i()->uid)).'" data-webview="กิจกรรมของฉัน"><i class="icon -material">assignment_ind</i><span>กิจกรรม</span></a>');
	if ($isEmployee) {
		if ($actionSelectCard->count()) {
			$toolbarNav->add('<a class="sg-action" href="#project-app-action-select" data-rel="box" data-width="480"><i class="icon -material">add_task</i><span>ส่งรายงาน</span></a>');
		} else {
			$toolbarNav->add('<a class="sg-action" href="'.url('project/app/month').'" data-webview="ส่งรายงานประจำเดือน"><i class="icon -material">add_task</i><span>ส่งรายงาน</span></a>');
		}
	}
	if ($followSelectCard->count()) {
		$toolbarNav->add('<a class="sg-action" href="#project-app-follow-select" data-rel="box" data-width="480"><i class="icon -material">dashboard</i><span>โครงการ</span></a>');
	} else {
		$toolbarNav->add('<a class="sg-action" href="'.url('project/app/follow/my').'" data-webview="โครงการ"><i class="icon -material">dashboard</i><span>โครงการ</span></a>');
	}
	$toolbarNav->add('<a class="sg-action" href="'.url('project/app/calendar').'" data-webview="ปฎิทิน"><i class="icon -material">event</i><span>ปฎิทิน</span></a>');

	$toolbar->addNav('main', $toolbarNav);

	$toolbar->build();

	// Show Activity Post Button
	$ret .= '<div id="project-chat-box" class="ui-card project-chat-box">'
		. '<div class="ui-item">'
		. '<div><img src="'.model::user_photo(i()->username).'" width="40" height="40" />&nbsp;'
		. '<a class="sg-action form-text" href="'.url('project/app/action/form').'" placeholder="เขียนบันทึกการทำกิจกรรม" data-rel="#project-activity-card" data-width="480" data-height="100%" x-data-webview="บันทึกการทำกิจกรรม">เขียนบันทึกการทำกิจกรรม</a>&nbsp;'
		. '<a class="sg-action btn -link" href="'.url('project/app/action/form').'" data-rel="#project-activity-card" data-width="480" data-height="100%" data-webview="บันทึกการทำกิจกรรม"><i class="icon -camera"></i><span>Photo</span></a></div>'
		. '</div>'
		. '</div>';

	//$ret .= R::Page('project.app.activity', NULL);
	// Show project activity
	$ret .= '<section id="project-activity-card" class="sg-load" data-url="'.url('project/app/activity', array('u' => i()->uid)).'">'._NL;
	$ret .= '<div class="loader -rotate" style="width: 64px; height: 64px; margin: 32px auto; display: block;"></div>';

	$ret .= '</section><!-- project-app-activity -->';

	$ret .= '<section id="template" class="-hidden">'
		. '<div id="project-app-action-select" class="-hidden"><header class="header">'._HEADER_BACK.'<h3>เลือกโครงการ</h3></header>'.$actionSelectCard->build().'</div>'
		. '<div id="project-app-follow-select" class="-hidden"><header class="header">'._HEADER_BACK.'<h3>เลือกโครงการ</h3></header>'.$followSelectCard->build().'</div>'
		. '</div>';

	return $ret;
}
?>