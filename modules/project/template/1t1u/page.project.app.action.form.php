<?php
/**
* Project :: Action Form
* Created 2021-01-21
* Modify  2021-01-21
*
* @param Object $self
* @return String
*/

$debug = true;
function project_app_action_form($self, $projectId = NULL, $actionId = NULL) {
	// Data Model

	head('<script type="text/javascript">
	function onWebViewComplete() {
		console.log("CALL onWebViewComplete FROM WEBVIEW")
		var options = {refreshOnBack: false}
		return options
	}
	function onWebViewBack() {
		var options = {processDomOnResume: "#project-activity-card"}
		return options
	}
	</script>');

	if (!i()->ok) {
		$ret = '<header class="header -hidden">'._HEADER_BACK.'<h3>Sign In</h3></header>'
			. R::View(
				'signform',
				'{
					time:-1,
					rel: "none",
					signret: "project/app/action/form",
					done: "'.(R()->appAgent ? 'load:#project-activity-card:'.url(q()).' | ' : '').'load:#project-activity-card:'.url(q()).'",
					regRel: "'.(R()->appAgent ? '#main' : '#main').'"
				}'
			);
					//done: "'.(R()->appAgent ? 'load | ' : '').'load->clear:box:'.url(q()).'",

		$ret .= '<style type="text/css">
		.toolbar.-main.-imed h2 {text-align: center;}
		.form.signform .form-item {margin-bottom: 16px; position: relative;}
		.form.signform label {position: absolute; left: 8px; color: #666; font-style: italic; font-size: 0.9em; font-weight: normal;}
		.form.signform .form-text, .form.signform .form-password {padding-top: 24px;}
		.module-imed.-softganz-app .form-item.-edit-cookielength {display: none;}
		.login {border: none; background-color: transparent;}
		.login.-normal h3 {display: none;}
		.form-item.-edit-cookielength {display: none;}
		.form.signform .ui-action>a {display: block;}
		</style>';
		return $ret;
	}

	if (empty($projectId)) {
		$selectProject = R::View('project.select', '{rel: "'.(R()->appAgent ? '#main' : '#project-activity-card').'", retUrl: "'.url('project/app/action/form/$id').'"}');
		if ($selectProject->error) {
			return message('error', 'ขออภัย '.$selectProject->error);
		} else if ($selectProject->projectId) {
			$projectId = $selectProject->projectId;
		} else {
			return '<header class="header -box -hidden">'._HEADER_BACK.'<h3>เลือกโครงการ</h3></header>'
				. $selectProject->build();
		}
	}


	$projectInfo = R::Model('project.get', $projectId);
	$isAdd = $projectInfo->right->isAdmin || $projectInfo->info->membershipType;
	if (!$isAdd) {
		return message('error', 'ขออภัย - โครงการนี้ท่านไม่สามารถเขียนบันทึกกิจกรรมได้');
	}

	$actionInfo = $actionId ? R::Model('project.action.get', array('tpid' => $projectId, 'actionId' => $actionId), '{debug: false}') : NULL;


	// View Model
	//$ret = '<header class="header -box -hidden">'._HEADER_BACK.'<h3>บันทึกการทำกิจกรรม</h3></header>';
	/*
	$ui = new Ui();
	$ui->addConfig('nav', '{class: "nav -selecttype"}');
	$ui->add('<a class="sg-action btn -active -dotype" data-type="GREEN,ACTIVITY"><i class="icon -material">directions_run</i><span>กิจกรรม</span></a>');
	if ($myShopList) {
		$ui->add('<a class="sg-action btn -dotype" href="'.url().'" data-type="GREEN,ONFIELD"><i class="icon -material">local_florist</i><span>ลงแปลง</span></a>');
		$ui->add('<a class="sg-action btn -dotype" href="'.url().'" data-type="GREEN,MOBILE"><i class="icon -material">directions_car</i><span>Green Mobile</span></a>');
		$ui->add('<a class="sg-action btn -dotype" href="'.url().'" data-type="GREEN,PLANT"><i class="icon -material">nature_people</i><span>ผลผลิตรอบใหม่</span></a>');
	}
	//$ret .= $ui->build();
	*/

	//$ret .= print_o($projectInfo, '$projectInfo');
	//$ret .= print_o($actionInfo, '$actionInfo');

	$actionOption = (Object) array(
		'rel' => 'none',
		'done' => R()->appAgent ? 'load->replace:.form.-action:'.url('project/app/activity',array('u' => i()->uid)).' | moveto: 0,0' : 'load->replace:.form.-action:'.url('project/app/activity',array('u' => i()->uid)).' | back | moveto: 0,0',
	);

	$ret .= R::View('project.action.form', $projectInfo, $actionInfo->activityId, $actionInfo, $actionOption);

	/*
	$ret .= '<style type="text/css">
		.green-activity-form .nav.-icons {padding: 8px;}
		.green-activity-form .btn.-primary {width: 100%; margin: 0; padding: 12px 0; border-radius: 0;}

		.green-activity-form .nav.-selecttype {padding: 0;}
		.green-activity-form .nav.-selecttype .ui-item {margin: 0; border-bottom: 1px #eee solid;}
		.green-activity-form .nav.-selecttype .btn {border: none; box-shadow: none; border-radius: 0; padding: 8px 0; color: #666; background-color: #fff;}
		.green-activity-form .nav.-selecttype .btn:hover {background-color: #eee;}
		.green-activity-form .nav.-selecttype .btn.-active {color: #c0ffc8; box-shadow: none; background-color: #20a200;}
		.green-activity-form .nav.-selecttype .btn.-active>.icon {color: green; border-radius: 50%; box-shadow: 0 0 0 1px green; background-color: #c1d6c1;}
		.green-activity-form .nav.-selecttype .ui-item {display: block; margin: 0;}
		.green-activity-form .nav.-selecttype .ui-item .btn {display: block; margin: 0;}
		.green-activity-form .form-item.-edit-locname .sg-dropbox>.-wrapper {width: 300px;}
		.green-activity-form .form-item.-edit-locname .sg-dropbox>.-wrapper>.-content {background-color: #fff;}
		.green-activity-form abbr {padding: 4px 8px; border-bottom: 1px #eee solid;}
		.green-activity-form abbr:hover {background-color: #f7f7f7;}
		.green-activity-form .form-item abbr label {margin: 0; font-weight: normal;}
		.green-activity-form .form-item abbr>label>.icon {position: absolute; right: 8px;}

		.box-page>.form.green-activity-form {padding-bottom: 88px;}
		.box-page .green-activity-form .form-item.-edit-save {margin: 0; position: absolute; bottom: 0; padding: 0; left: 0; right: 0;}
		.module.-softganz-app .form-item {padding: 8px 0;}
		.module.-softganz-app .form-textarea {border-radius: 0; box-shadow: none;}
		.module.-softganz-app .form-select {border-radius: 0; box-shadow: none;}
		.module.-softganz-app .form-select:focus {box-shadow: none;}
		.module.-softganz-app .green-activity-form .nav.-selecttype .btn {padding: 12px 0;}

	</style>';

	$ret .= '<script type="text/javascript">
	$(document).ready(function() {
		$("#edit-message").focus()
	})
	$(".btn.-dotype").click(function() {
		var $this = $(this)
		var dataType = $this.data("type").toLowerCase().replace(",","-")
		console.log(dataType)

		$(this).closest("ul").find("a").removeClass("-active")
		$(this).addClass("-active")
		$("#edit-tagname").val($(this).data("type"))
		$this.closest("form").find(".form-item.-for").addClass("-hidden").find(".-require").removeClass("-require")
		$this.closest("form").find(".form-item.-for.-"+dataType).removeClass("-hidden")
		$this.closest("form").find(".form-item.-for.-"+dataType+".-require").find(".form-text,.form-select").addClass("-require")
		return false
	});


	</script>';
	*/

	return $ret;
}
?>