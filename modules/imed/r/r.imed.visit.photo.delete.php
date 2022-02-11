<?php
/**
 * Delete photo
 *
 * @param Integer $fid - file id
 * @return String
 */
function r_imed_visit_photo_delete($fid) {
	$result=array();
	$stmt='SELECT f.*, s.`pid` `psnid`
					FROM %imed_files% f
						LEFT JOIN %imed_service% s USING(`seq`)
					WHERE f.`fid`=:fid LIMIT 1';
	$rs=mydb::select($stmt,':fid',$fid);
	$result['query'][]=mydb()->_query;
	$result['rs']=$rs;

	if ($rs->file) {
		if ($rs->type=='photo') {
			$stmt='DELETE FROM %imed_files% WHERE fid=:fid LIMIT 1';
			mydb::query($stmt,':fid',$fid);
			$result['query'][]=mydb()->_query;

			$filename=cfg('folder.abs').cfg('upload_folder').'imed/photo/'.$rs->file;
			if (file_exists($filename) and is_file($filename)) {
				unlink($filename);
			}
			model::watch_log('imed','remove visit photo','Photo id '.$rs->fid.' - '.$rs->file.' was removed from '.$rs->psnid.' by '.i()->name.'('.i()->uid.')');
		} else if ($rs->type=='doc') {
			/*
			$stmt='DELETE FROM %topic_files% WHERE fid=:fid LIMIT 1';
			mydb::query($stmt,':fid',$fid);
			$result['query'][]=mydb()->_query;

			$filename=cfg('paper.upload.document.folder').$rs->file;
			if (file_exists($filename) and is_file($filename)) {
				unlink($filename);
			}
			model::watch_log('photo','remove doc','File id '.$rs->fid.' - '.$rs->file.' was removed from topic '.$rs->tpid.' by '.i()->name.'('.i()->uid.')');
			*/
		}
	}
	return $result;
}
?>