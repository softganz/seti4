<?php
/**
* paper :: Show download file
* Created 2021-01-06
* Modify  2021-01-06
*
* @param Object $self
* @param Object $topicInfo
* @return String
*
* @usage paper/{id}/info.file
*/

$debug = true;

function paper_info_file($self, $topicInfo) {
	// Data Model
	$tpid = $topicInfo->tpid;

	$ret = '';

	$stmt = 'SELECT f.*
		FROM %topic_files% f
		WHERE f.`tpid` = :tpid AND f.`type` = "doc" AND f.`tagname` IS NULL';

	$dbs = mydb::select($stmt, ':tpid', $tpid);

	// View Model
	$tables = new Table();
	foreach ($dbs->items as $rs) {
		$downloadUrl = url('paper/'.$tpid.'/info.file.download/'.$rs->fid);
		$tables->rows[] = array(
			'<a href="'.$downloadUrl.'" target="_blank">'.$rs->title.'</a>',
			'<a href="'.$downloadUrl.'" target="_blank"><i class="icon -material">cloud_download</i></a>',
		);
	}

	$ret .= $tables->build();

	//$ret .= print_o($dbs);

	//$ret .= print_o($topicInfo);
	return $ret;
}
?>