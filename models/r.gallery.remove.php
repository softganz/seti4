<?php
function r_gallery_remove($gallery) {
	debugMsg('DELETE gallery '.$gallery);

	$dbs=mydb::select('SELECT f.* FROM %topic_files% f WHERE f.`gallery`=:gallery',':gallery',$gallery);
	$ret.=print_o($dbs,'$dbs');
	if ($dbs->_empty) return;

	foreach ($dbs->items as $rs) {
		if ($rs->type=='photo') {
			$filename=cfg('folder.abs').cfg('upload_folder').'pics/'.$rs->file;
			if (file_exists($filename) and is_file($filename)) {
				$is_photo_inused=db_count('%topic_files%',' file="'.$rs->file.'" AND fid!='.$rs->fid);
				if (!$is_photo_inused) {
					unlink($filename);
					debugMsg($is_photo_inused?'ภาพถูกใช้โดยคนอื่น':'ลบภาพ '.basename($filename).' เรียบร้อยแล้ว');
					CommonModel::watch_log('project','remove photo','Photo id '.$rs->fid.' - '.$rs->file.' was removed from gallery by '.i()->name.'('.i()->uid.')');
				}
			}
		} else if ($rs->type=='doc') {
			$filename=cfg('paper.upload.document.folder').$rs->file;
			if (file_exists($filename) and is_file($filename)) {
				unlink($filename);
				debugMsg('ลบไฟล์ '.basename($filename).' เรียบร้อย');
				CommonModel::watch_log('project','remove doc','File id '.$rs->fid.' - '.$rs->file.' was removed from gallery by '.i()->name.'('.i()->uid.')');
			}
		}
	}

	$stmt='DELETE FROM %topic_files% WHERE `gallery`=:gallery';
	mydb::query($stmt,':gallery',$gallery);
}
?>