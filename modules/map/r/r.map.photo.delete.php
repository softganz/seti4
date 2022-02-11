<?php
/**
* Delete photo
*
* @param Integer $fid - file id
* @return String
*/

$debug = true;

function r_map_photo_delete($fid, $options = '{}') {
	$defaults = '{debug: false}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = NULL;

	// delete photos
	//$ret.='Delete '.$fid.'<br />';
	$stmt = 'SELECT f.*, tr.`mapid` FROM %topic_files% f LEFT JOIN %map_networks% tr ON tr.`gallery`=f.`gallery` WHERE f.`fid` = :fid LIMIT 1';
	$rs = mydb::select($stmt,':fid',$fid);
	if ($rs->file) {
		if ($rs->type=='photo') {
			mydb::query('DELETE FROM %topic_files% WHERE fid='.$fid.' AND `type`="photo" LIMIT 1',':fid',$fid);
			$remain=mydb::select('SELECT COUNT(*) remain FROM %topic_files% WHERE gallery=:gallery LIMIT 1',':gallery',$rs->gallery)->remain;
			if ($remain==0) {
				mydb::query('UPDATE %map_networks% SET gallery=NULL WHERE mapid=:mapid LIMIT 1',':mapid',$rs->mapid);
			}
			$filename=cfg('folder.abs').cfg('upload_folder').'pics/'.$rs->file;
			if (file_exists($filename) and is_file($filename)) {
				$is_photo_inused=db_count('%topic_files%',' file="'.$rs->file.'" AND fid!='.$rs->fid);
				if (!$is_photo_inused) unlink($filename);
				$ret.=$is_photo_inused?'ภาพถูกใช้โดยคนอื่น':'ลบภาพเรียบร้อยแล้ว';
			}
			model::watch_log('project','remove photo','Photo id '.$rs->fid.' - '.$rs->file.' of activity '.$rs->trid.' was removed from project '.$rs->tpid.' by '.i()->name.'('.i()->uid.')');
		} else if ($rs->type=='doc') {
			mydb::query('DELETE FROM %topic_files% WHERE fid='.$fid.' AND `type`="doc" LIMIT 1',':fid',$fid);
			$remain=mydb::select('SELECT COUNT(*) remain FROM %topic_files% WHERE gallery=:gallery LIMIT 1',':gallery',$rs->gallery)->remain;
			if ($remain==0) {
				mydb::query('UPDATE %map_networks% SET gallery=NULL WHERE mapid=:mapid LIMIT 1',':mapid',$rs->mapid);
			}
			$filename=cfg('paper.upload.document.folder').$rs->file;
			if (file_exists($filename) and is_file($filename)) {
				unlink($filename);
			}
			model::watch_log('project','remove doc','File id '.$rs->fid.' - '.$rs->file.' of activity '.$rs->trid.' was removed from project '.$rs->tpid.' by '.i()->name.'('.i()->uid.')');
		}
	}
	return $result;
}
?>