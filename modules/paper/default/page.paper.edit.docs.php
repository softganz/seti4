<?php
/**
* Paper   :: Document Management
* Created :: 2019-06-02
* Modify  :: 2023-12-24
* Version :: 2
*
* @param String $topicInfo
* @return Widget
*
* @usage paper/{nodeId}/edit.docs
*/

use Softganz\DB;

class PaperEditDocs extends Page {
	var $nodeId;
	var $topicInfo;

	function __construct($topicInfo = NULL) {
		parent::__construct([
			'nodeId' => $topicInfo->nodeId,
			'topicInfo' => $topicInfo,
		]);
	}

	function build() {
		if (empty($this->nodeId)) return error(_HTTP_ERROR_NOT_FOUND, 'ไม่มีข้อมูลตามที่ระบุ');

		if (!user_access('upload document')) return error(_HTTP_ERROR_FORBIDDEN,'Access denied');

		// show reference file list
		$docs = DB::select([
			'SELECT `fid`, `file`, `folder`, `title`
			FROM %topic_files%
			WHERE `tpid` = :tpid AND (`cid` = 0 OR `cid` IS NULL) AND `type`="doc"',
			'var' => [':tpid' => $this->nodeId]
		]);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Document Management',
				'leading' => _HEADER_BACK,
				'trailing' => new Row([
					'child' => '<a class="sg-action btn -primary" href="'.url('paper/'.$this->nodeId.'/edit.docs.add').'" data-rel="box" data-width="640"><i class="icon -material">cloud_upload</i><span>{tr:UPLOAD NEW DOCUMENT}</span></a>',
				]),
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new ListTile([
						'title' => 'All document relate to this topic',
						'trailing' => new Row([
							'child' => '<a class="sg-action btn -primary" href="'.url('paper/'.$this->nodeId.'/edit.docs.add').'" data-rel="box" data-width="640"><i class="icon -material">cloud_upload</i><span>{tr:UPLOAD NEW DOCUMENT}</span></a>',
						]),					]),
					$docs->count == 0 ? 'No attach document' : NULL,

					new Table([
						'thead' => [
							'Document file',
							'amt -hover-parent' => 'File size (bytes)'
						],
						'children' => array_map(
							function($doc) {
								$ui = new Ui();
								$ui->add('<a href="'.url('paper/info/api/'.$this->nodeId.'/doc.delete/'.$doc->fid).'" class="sg-action" data-rel="none" data-title="Delete document!!!" data-confirm="Are you sure?" data-removeparent="tr"><i class="icon -material -gray">cancel</i></a>');
								$menu = '<nav class="nav -icons -hover">'.$ui->build().'</nav>';
								$fileProperty = FileModel::docProperty($doc->file, $doc->folder);

								return [
									'<a href="'.(cfg('files.log')?url('files/'.$doc->fid):cfg('url').'upload/forum/'.sg_urlencode($doc->file)).'" target="_blank">'.($doc->title?$doc->title.' ('.$doc->file.')':$doc->file).'</a>',
									number_format($fileProperty->size)
									. $menu,
								];
							},
							$docs->items
						), // children
					]), // Table
				], // children
			]), // Widget
		]);
	}
}
?>