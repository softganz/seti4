<?php
/**
* Project Action Join Setting
* Created 2019-02-22
* Modify  2019-07-30

*
* @param Object $self
* @param Int $tpid
* @param Int $calid
* @return String
*/

function project_join_setting_group_import($self, $p = NULL, $doid = NULL) {

	if ($doid) {
		$stmt = 'SELECT `paidgroup` FROM %org_doings% WHERE `doid` = :doid LIMIT 1';
		return mydb::select($stmt, ':doid', $doid)->paidgroup;
	}

	$ret = '<h3 class="title -box">นำเข้าจากกิจกรรมอื่น</h3>';


	$stmt = 'SELECT `doid`, `doings`, `paidgroup` FROM %org_doings% WHERE `paidgroup` != ""';
	$dbs = mydb::select($stmt);

	$ret .= '<div class="project-join-setting-group-import">';
	$ui = new Ui(NULL,'ui-menu');
	foreach ($dbs->items as $rs) {
		$ui->add('<a class="sg-action" href="'.url('project/0/join.setting.group.import/'.$rs->doid).'" data-rel="#importstr">'.$rs->doings.'</a>');
	}
	$ret .= $ui->build();

	$ret .= '<textarea id="importstr" class="form-text -fill" rows="20"></textarea>';

	//$ret .= print_o($dbs);
	$ret .= '</div>';

	$ret .= '<nav class="nav -page -sg-text-right"><a class="sg-action btn -primary" href="javascript:void(0)" onclick="projectJoinSettingGroupImport()" data-rel="close"><i class="icon -save -white"></i><span>นำเข้า</span></a></nav>';

	$ret .= '<style type="text/css">
	.project-join-setting-group-import {display: flex; height: 400px;}
	.project-join-setting-group-import .ui-menu {width: 300px; height: 100%; overflow: scroll;}
	.form-text {}
	</style>';

	$ret .= '<script type="text/javascript">
	function projectJoinSettingGroupImport() {
		$("#edit-value").val($("#importstr").val())
	}
	</script>';
	return $ret;
}
?>