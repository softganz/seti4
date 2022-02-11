<?php
/**
* Project Qtatation Perople View
*
* @param Object $self
* @param Int $tpid
* @return String
*/

function project_qt_board_view($self, $tpid = NULL, $qtref = NULL) {
	$projectInfo=R::Model('project.get', $tpid, '{data: "info"}');

	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;

	$data = R::Model('project.qt.board.get', $tpid, $qtref);

	$ret .= '<h2 class="title -box">เลขที่แบบสอบถาม '.$data->qtref.'/'.sg_date($data->qtdate, 'ปปปป').'</h2>';

	if ($isEdit) {
		$ret.='<div class="btn-floating -right-bottom"><a class="?-sg-action btn -floating -circle48" href="'.url('project/qt/'.$tpid.'/board/edit/'.$qtref).'" data-rel="#main"><i class="icon -edit -white"></i></a></div>';
	}

	$data = R::Model('project.qt.board.get', $tpid, $qtref);
	$ret .= R::View('project.qt.board.form', $projectInfo, $data, '{readonly:true}');


	//$ret .= print_o($data, '$data');

	return $ret;
}
?>