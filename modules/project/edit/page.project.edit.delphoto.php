<?php
/**
 * Delete activity photo
 *
 * @param Integer $fid - file id
 * @return String
 */
function project_edit_delphoto($self,$fid) {
	$rs=mydb::select('SELECT f.*, tr.trid FROM %topic_files% f LEFT JOIN %project_tr% tr ON tr.gallery=f.gallery WHERE f.fid='.$fid.' LIMIT 1',':fid',$fid);

	if (!SG\confirm()) return 'Invalid';

	if ($rs->file) {
		if ($rs->type=='photo') {
			mydb::query('DELETE FROM %topic_files% WHERE fid='.$fid.' AND `type`="photo" LIMIT 1',':fid',$fid);
			$remain=mydb::select('SELECT COUNT(*) remain FROM %topic_files% WHERE gallery=:gallery LIMIT 1',':gallery',$rs->gallery)->remain;
			if ($remain==0) {
				if ($rs->tagname=='project,paiddoc') {
					mydb::query('UPDATE %project_paiddoc% SET `gallery`=NULL WHERE `gallery`=:gallery LIMIT 1',':gallery',$rs->gallery);
					$ret.=mydb()->_query;
				} else {
					mydb::query('UPDATE %project_tr% SET gallery=NULL WHERE trid=:trid LIMIT 1',':trid',$rs->trid);
				}
			}


			$filename=cfg('folder.abs').cfg('upload_folder').'pics/'.$rs->file;
			
			if (file_exists($filename) and is_file($filename)) {
				$is_photo_inused=mydb::select('SELECT `fid` FROM %topic_files% WHERE `file`=:file AND `fid`!=:fid LIMIT 1',':file',$rs->file, ':fid',$rs->fid)->fid;
				if (!$is_photo_inused) unlink($filename);

				$ret.=$is_photo_inused?'ภาพถูกใช้โดยคนอื่น':'ลบภาพเรียบร้อยแล้ว';
			}

			model::watch_log('project','remove photo','Photo id '.$rs->fid.' - '.$rs->file.' of activity '.$rs->trid.' was removed from project '.$rs->tpid.' by '.i()->name.'('.i()->uid.')');
		} else if ($rs->type=='doc') {
			mydb::query('DELETE FROM %topic_files% WHERE fid='.$fid.' AND `type`="doc" LIMIT 1',':fid',$fid);
			$remain=mydb::select('SELECT COUNT(*) remain FROM %topic_files% WHERE gallery=:gallery LIMIT 1',':gallery',$rs->gallery)->remain;

			if ($remain==0) {
				mydb::query('UPDATE %project_tr% SET gallery=NULL WHERE trid=:trid LIMIT 1',':trid',$rs->trid);
			}


			$filename=cfg('paper.upload.document.folder').$rs->file;
			if (file_exists($filename) and is_file($filename)) {
				unlink($filename);
			}

			model::watch_log('project','remove doc','File id '.$rs->fid.' - '.$rs->file.' of activity '.$rs->trid.' was removed from project '.$rs->tpid.' by '.i()->name.'('.i()->uid.')');
		}
	}
	return $ret;
}
?>