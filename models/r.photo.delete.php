<?php
/**
 * Delete photo
 *
 * @param Integer $fid - file id
 * @param Object $options
 * @return String
 */
function r_photo_delete($fid, $options = '{}') {
	$defaults = '{debug: false, deleteRecord: true, deleteFile: true}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = (Object) [
		'photoInused' => false,
		'msg' => NULL,
		'_query' => [],
	];


	$stmt = 'SELECT * FROM %topic_files% f WHERE f.`fid` = :fid LIMIT 1';
	$rs = mydb::select($stmt,':fid',$fid);
	$result->_query[] = mydb()->_query;

	if ($rs->file) {
		if ($rs->type == 'photo') {
			if ($options->deleteRecord) {
				$stmt = 'DELETE FROM %topic_files% WHERE fid = :fid LIMIT 1';
				mydb::query($stmt, ':fid', $fid);
				$result->_query[] = mydb()->_query;
			}

			$filename = cfg('folder.abs').cfg('upload_folder').'pics/'.$rs->file;

			if ($options->deleteFile && file_exists($filename) and is_file($filename)) {
				$stmt = 'SELECT COUNT(*) `total` FROM %topic_files% WHERE `file` = :file AND `fid` != :fid LIMIT 1';
				$is_photo_inused = mydb::select($stmt, $rs)->total;
				$result->_query[] = mydb()->_query;

				$result->photoInused = $is_photo_inused;
				if (!$is_photo_inused) unlink($filename);
				$result->msg = $is_photo_inused?'ภาพถูกใช้โดยคนอื่น':'ลบภาพเรียบร้อยแล้ว';
			}

			CommonModel::watch_log('photo', 'remove photo', 'Photo id '.$rs->fid.' - '.$rs->file.' was removed from topic '.$rs->tpid.' by '.i()->name.'('.i()->uid.')');
		} else if ($rs->type == 'doc') {
			if ($options->deleteRecord) {
				$stmt = 'DELETE FROM %topic_files% WHERE fid = :fid LIMIT 1';
				mydb::query($stmt, ':fid', $fid);
				$result->_query[] = mydb()->_query;
			}

			$filename = cfg('paper.upload.document.folder').$rs->file;
			if ($options->deleteFile && file_exists($filename) and is_file($filename)) {
				unlink($filename);
			}

			CommonModel::watch_log('photo', 'remove doc', 'File id '.$rs->fid.' - '.$rs->file.' was removed from topic '.$rs->tpid.' by '.i()->name.'('.i()->uid.')');
		}
	}
	return $result;
}
?>