<?php
/**
* Model Name
* Created 2019-01-01
* Modify  2019-01-01
*
* @param Object $conditions
* @return Object $options
*/

$debug = true;

function r_paper_photo_change($fileId, $photo) {
	$result->error = NULL;
	$result->complete = false;
	$result->file = NULL;

	$result->process[] = 'R::paper.photo.change request';

	if ($fileId && $photo) {
		$result->upload = R::Model('photo.save', (Object) $photo);
		//debugMsg($saveFile,'$saveFile');
		if ($result->upload->complete) {
			$oldFile = mydb::select('SELECT `file` FROM %topic_files% WHERE `fid` = :fid LIMIT 1', ':fid', $fileId)->file;
			//debugMsg('File Id = '.$fileId.' OLD FILE = '.$oldFile.'<br />'.mydb()->_query);

			//$fileCount = mydb::select('SELECT COUNT(*) `total` FROM %topic_files% WHERE `fid` != :fid AND `file` = :file AND `type` = "photo" LIMIT 1', ':fid', $fileId, ':file', $oldFile)->total;
			//debugMsg('File Count = '.$fileCount.' '.mydb()->_query);

			if ($result->upload->save->_file != $oldFile
				&& file_exists($result->upload->save->_location)
				&& is_file($result->upload->save->_location)
				&& $fileCount = mydb::select('SELECT COUNT(*) `total` FROM %topic_files% WHERE `fid` != :fid AND `file` = :file AND `type` = "photo" LIMIT 1', ':fid', $fileId, ':file', $oldFile)->total <= 0) {
				$result->process[] = 'Delete old photo file <em>'.$result->upload->save->_file.'</em>';
				$oldFileProp = CommonModel::get_photo_property($oldFile);
				//debugMsg($oldFileProp,'$oldFileProp');
				unlink($oldFileProp->_file);
			}

			$stmt = 'UPDATE %topic_files% SET `file` = :file WHERE `fid` = :fid LIMIT 1';
			mydb::query($stmt, ':fid', $fileId, ':file', $result->upload->save->_file);
			//$ret .= mydb()->_query;

			$result->query[] = mydb()->_query;
			if (mydb()->_error) $result->error[] = 'Query error';

			$result->file = CommonModel::get_photo_property($result->upload->save->_file);
			$result->complete = true;

		} else {
			$result->error[] = $result->upload->error;
		}

	}

	return $result;
}
?>