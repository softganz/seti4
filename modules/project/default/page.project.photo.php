<?php
function project_photo($self, $tpid = NULL, $action = NULL, $tranId = NULL) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get', $tpid, '{initTemplate:true}');

	$tpid = $projectInfo->tpid;

	if (empty($projectInfo)) return message('error','ERROR : No Project');

	$isMember = $projectInfo->info->membershipType;
	$isEdit = ($projectInfo->RIGHT & _IS_ADMIN) || $isMember;

	//$ret .= $isEdit ? 'EDITABLE' : 'NO EDIT';

	switch ($action) {
		case 'upload':
			if ($isEdit && $tpid && $_FILES['photo']['tmp_name']) {
				$data = (object) post();
				//$stmt = 'SELECT * FROM %topic_files% WHERE `tpid` = :tpid AND `tagname` = :tagname LIMIT 1';
				//$rs = mydb::select($stmt, ':tpid', $tpid, ':tagname', 'project_cmboard_'.$tranId);

				if ($tranId) {
					$data->fid = $tranId;
					$result = R::Model('photo.delete', $tranId, '{deleteRecord: false}');
				}

				$data->tpid = $tpid;
				if (empty($data->tagname)) $data->tagname = NULL;
				if (empty($data->prename)) $data->prename = $data->tagname ? $data->tagname.'_' : 'project_';
				if (empty($data->title)) $data->title = NULL;
				$data->deleteurl = 'project/photo/'.$tpid.'/remove/';

				$uploadResult = R::Model('photo.upload', $_FILES['photo'], $data);

				$ret .= $uploadResult->link;
				//$ret .= '<div class="-sg-text-left">'.print_o($data,'$data').print_o($uploadResult, '$uploadResult').'</div>';
			}

			//$ret .= print_o($_FILES, '$_FILES');
			return $ret;
			break;

		case 'remove':
			if ($isEdit && $tranId && SG\confirm()) {
				$result = R::Model('photo.delete', $tranId);
				//$ret .= print_o($result, '$result');
				$ret .= 'ลบภาพเรียบร้อย';
			}
			return $ret;

		default:
			# code...
			break;
	}

	//$ret .= print_o($projectInfo, '$projectInfo');

	return $ret;
}
?>