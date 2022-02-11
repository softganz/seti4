<?php
/**
* Paper Document Management
* Created 2019-06-02
* Modify  2019-06-02
*
* @param Object $self
* @param Object $topicInfo
* @return String
*/

$debug = true;

function paper_edit_docs($self, $topicInfo) {
	if (!$topicInfo->tpid) return message('error', 'PARAMETER ERROR');

	if (!user_access('upload document')) return message('error','Access denied');

	$tpid = $topicInfo->tpid;

	$uploadNav = '';
	if ((user_access('upload document'))) {
		$uploadNav = '<nav class="nav"><a class="sg-action btn -primary" href="'.url('paper/'.$tpid.'/edit.docs.add').'" data-rel="box" data-width="640"><i class="icon -material">cloud_upload</i><span>{tr:UPLOAD NEW DOCUMENT}</span></a></nav>';
	}

	$ret = '<header class="header -box"><nav class="nav -back"><a class="" href="'.url('paper/'.$tpid.'/edit').'"><i class="icon -material">arrow_back</i></a></nav><h3>{tr:Document Management}</h3>'.$uploadNav.'</header>';

	$upload_folder = cfg('paper.upload.document.folder');


	// show reference file list
	$docs = mydb::select('SELECT * FROM %topic_files% WHERE `tpid` = :tpid AND (`cid` = 0 OR `cid` IS NULL) AND `type`="doc"', ':tpid', $tpid);
	if ($docs->_num_rows) {
		$ret .= '<h3>All document relate to this topic</h3>';

		$tables = new Table();
		$tables->thead = array(
										'Document file',
										'amt -hover-parent' => 'File size (bytes)'
									);
		foreach ($docs->items as $key=>$doc) {
			$ui = new Ui();
			$ui->add('<a href="'.url('paper/info/api/'.$tpid.'/doc.delete/'.$doc->fid).'" class="sg-action" data-rel="none" data-title="Delete document!!!" data-confirm="Are you sure?" data-removeparent="tr"><i class="icon -material -gray">cancel</i></a>');
			$menu = '<nav class="nav -icons -hover">'.$ui->build().'</nav>';
			$tables->rows[] = array(
												'<a href="'.(cfg('files.log')?url('files/'.$doc->fid):cfg('url').'upload/forum/'.sg_urlencode($doc->file)).'" target="_blank">'.($doc->title?$doc->title.' ('.$doc->file.')':$doc->file).'</a>',
												number_format(filesize($upload_folder.$doc->file))
												. $menu,
											);
		}
		$ret .= $tables->build();
	} else {
		$ret .= 'No attach document';
	}

	//$ret .= print_o($topicInfo, '$topicInfo');
	return $ret;
}
?>