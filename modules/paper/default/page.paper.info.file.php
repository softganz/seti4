<?php
/**
* paper   :: Show download file
* Created :: 2021-01-06
* Modify  :: 2025-06-13
* Version :: 2
*
* @param String $nodeInfo
* @return Widget
*
* @usage paper/{nodeId}/info.file
*/

use Softganz\DB;

class paperInfoFile extends Page {
	var $nodeId;
	var $right;
	var $nodeInfo;

	function __construct($nodeInfo = NULL) {
		parent::__construct([
			'nodeId' => $nodeInfo->nodeId,
			'nodeInfo' => $nodeInfo,
			'right' => (Object) [
				'edit' => $nodeInfo->right->edit
			]
		]);
	}

	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'File Download',
				'trailing' => new Row([
					'child' => $this->right->edit ? new Button([
						'class' => 'sg-action',
						'href' => url('paper/'.$this->nodeId.'/edit.docs'),
						'icon' => new Icon('edit'),
						'rel' => 'box',
						'attribute' => ['data-width' => 'full']
					]) : NULL
				]),
			]), // AppBar
			'body' => new Table([
				'thead' => ['no' => '', 'ไฟล์', 'ext -center' => '', 'times -amt' => 'จำนวนดาวน์โหลด', 'last -date' => 'ล่าสุด', ''],
				'children' => array_map(
					function ($rs) {
						static $no = 0;
						$downloadUrl = url('paper/'.$rs->nodeId.'/info.file.download/'.$rs->fileId);
						$ext = strtoupper(sg_file_extension($rs->file));
						return [
							++$no,
							new Button([
								'href' => $downloadUrl,
								'text' => $rs->title,
								'target' => '_blank'
							]),
							$ext,
							number_format($rs->download),
							$rs->last_download ? sg_date($rs->last_download, 'ว ดด ปปปป H:i') : '',
							new Button([
								'href' => $downloadUrl,
								'icon' => new Icon('cloud_download'),
								'target' => '_blank'
							]),
						];
					},
					(Array) DB::select([
						'SELECT `file`.`tpid` `nodeId`, `file`.`title`, `file`.`download`, `file`.`last_download`, `file`.`fid` `fileId`, `file`.`file`
							FROM %topic_files% `file`
							WHERE `file`.`tpid` = :nodeId AND `file`.`type` = "doc" AND `file`.`tagname` IS NULL',
							'var' => [':nodeId' => $this->nodeInfo->nodeId]
					])->items
				),
			]), // Table
		]);
	}
}
?>