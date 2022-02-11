<?php
/**
* Project Quatation Board Controller call from project_qt
*
* @param Object $self
* @param Int $tpid
* @param String $action
* @param Int $trid
* @return String
* 
* qtgroup = 10 qtform = 105 แบบสำรวจความคิดเห็นของประชาชน
* Method new , edit , save, list , view , delete
*/

define(_PROJECT_QTFORM_BOARD, 104);

function project_qt_board($self, $tpid = NULL, $action = NULL, $trid = NULL) {
	$projectInfo=R::Model('project.get', $tpid, '{data: "info"}');

	$isEdit = $projectInfo->RIGHT & _IS_EDITABLE;

	//$ret .= 'QT People '.$action;

	switch ($action) {
		case 'new':
			$result = R::Model('project.qt.board.save', $data);
			$ret .= R::View('project.qt.board.form', $projectInfo, $data);
			break;

		case 'edit':
			$data = R::Model('project.qt.board.get', $tpid, $trid);
			$ret .= R::View('project.qt.board.form', $projectInfo, $data);
			break;

		case 'save':
			$data = (object) post('qt');
			$data->tpid = $tpid;
			$result = R::Model('project.qt.board.save', $data);
			//$ret .= print_o($result, '$result');
			//$ret .= print_o($data,'$data');
			location('project/qt/'.$tpid.'/board');
			break;
		
		default:
			if (empty($action)) $action='home';
			$ret.=R::Page('project.qt.board.'.$action, $self, $tpid, $trid);

			//$ret .= R::Page('project.qt.board.list', NULL, $tpid);
			break;
	}

	//$ret.=print_o($projectInfo,'$projectInfo');
	//$ret.=print_o(func_get_args(),'arg');
	return $ret;
}
?>