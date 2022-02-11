<?php
/**
* Project View
*
* @param Object $self
* @param Int $tpid
* @return String
*/
function project_info_link($self, $projectInfo) {
	$tpid = $projectInfo->tpid;

	if (!$tpid) return message('error', 'PROCESS ERROR');

	$self->theme->class .= ' project-status-'.$projectInfo->info->project_statuscode;

	R::View('project.toolbar', $self, $projectInfo->title, $projectInfo->submodule, $projectInfo,'{showPrint: false}');

	$isAdmin = $projectInfo->info->isAdmin;
	$isEdit = $projectInfo->info->isEdit;

	$ret .= '<section id="project-info-link" class="project-info-link" data-url="'.url('project/'.$tpid.'/info.link').'">';
	$ret .= '<h3>รายชื่อโครงการที่ประเมิน</h3>';
	$tables = new Table();

	foreach ($projectInfo->link as $rs) {
		$tables->rows[] = array(
			'title -hover-parent' => '<a href="'.$rs->url.'" target="_blank">'.SG\getFirst($rs->title,$rs->url).'</a>'
				. ($isEdit ? '<nav class="nav -icons -hover"><a class="sg-action" href="'.url('project/'.$tpid.'/info/link.remove/'.$rs->linkId).'" data-rel="notify" data-done="remove:parent tr" data-title="ลบรายการ" data-confirm="ต้องการลบรายการนี้ กรุณายืนยัน?"><i class="icon -material -gray">cancel</i></a></nav>' : ''),
		);
	}

	$ret .= $tables->build();
	if ($isEdit) {
		$ret .= '<nav class="nav -icons -sg-text-right"><a class="sg-action btn -link" href="'.url('project/'.$tpid.'/info.link.form').'" data-rel="box" data-width="480"><i class="icon -material">add_circle</i><span>เพิ่มโครงการ</span></a></nav>';
	}
	//$ret .= print_o($projectInfo->link, '$link');
	$ret .= '</section>';

	return $ret;
}
?>
