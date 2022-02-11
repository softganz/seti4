<?php
function project_doc($self, $tpid = NULL, $action = NULL, $tranId = NULL) {
	$projectInfo = is_object($tpid) ? $tpid : R::Model('project.get', $tpid, '{initTemplate:true}');

	$tpid = $projectInfo->tpid;

	if (empty($projectInfo)) return message('error','ERROR : No Project');

	$isMember = $projectInfo->info->membershipType;
	$isEdit = ($projectInfo->RIGHT & _IS_ADMIN) || $isMember;

	//$ret .= $isEdit ? 'EDITABLE' : 'NO EDIT';

	switch ($action) {
		case 'upload':
			if ($isEdit && $tpid && $_FILES['doc']['tmp_name']) {
				$data = (object) post();
				//$stmt = 'SELECT * FROM %topic_files% WHERE `tpid` = :tpid AND `tagname` = :tagname LIMIT 1';
				//$rs = mydb::select($stmt, ':tpid', $tpid, ':tagname', 'project_cmboard_'.$tranId);

				if ($tranId) {
					$data->fid = $tranId;
					$result = R::Model('doc.delete', $tranId, '{deleteRecord: false}');
				}

				$data->tpid = $tpid;
				if (empty($data->tagname)) $data->tagname = NULL;
				if (empty($data->prename)) $data->prename = $data->tagname ? $data->tagname.'_' : 'project_';
				if (empty($data->title)) $data->title = NULL;
				$data->deleteurl = 'project/doc/'.$tpid.'/remove/';

				$uploadResult = R::Model('doc.upload', $_FILES['doc'], $data, '{debug: true}');

				$ret .= $uploadResult->link;
			} else {
				$ret .= 'ERROR ON UPLOADING';
			}

			//$ret .= '<div class="-sg-text-left">'.print_o($data,'$data').print_o($uploadResult, '$uploadResult').print_o($_FILES, '$_FILES').'</div>';
			return $ret;
			break;

		case 'remove':
			if ($isEdit && SG\confirm() && $tranId) {
				$result = R::Model('doc.delete', $tranId);
				//$ret .= print_o($result, '$result');
				$ret .= 'ลบไฟล์เรียบร้อย';
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