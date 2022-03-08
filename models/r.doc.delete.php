<?php
/**
 * Delete document
 *
 * @param Integer $fid - file id
 * @param Object $options
 * @return String
 */
function r_doc_delete($fid, $options = '{}') {
	$defaults = '{debug: false, deleteRecord: true, deleteFile: true}';
	$options = sg_json_decode($options, $defaults);
	$debug = $options->debug;

	$result = (Object) [
		'_query' => [],
	];


	$stmt = 'SELECT * FROM %topic_files% f WHERE f.`fid` = :fid LIMIT 1';
	$rs = mydb::select($stmt,':fid',$fid);
	$result->_query[] = mydb()->_query;

	if ($rs->file && $rs->type == 'doc') {
		if ($options->deleteRecord) {
			$stmt = 'DELETE FROM %topic_files% WHERE fid = :fid LIMIT 1';
			mydb::query($stmt, ':fid', $fid);
			$result->_query[] = mydb()->_query;
		}

		$filename = cfg('paper.upload.document.folder').$rs->file;
		if ($options->deleteFile && file_exists($filename) and is_file($filename)) {
			unlink($filename);
		}

		model::watch_log('photo', 'remove doc', 'File id '.$rs->fid.' - '.$rs->file.' was removed from topic '.$rs->tpid.' by '.i()->name.'('.i()->uid.')');
	}
	return $result;
}
?>