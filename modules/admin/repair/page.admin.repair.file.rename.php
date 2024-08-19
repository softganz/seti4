<?php
/**
* Admin   :: Repair File Rename
* Created :: 2024-07-10
* Modify  :: 2024-08-19
* Version :: 2
*
* @return Widget
*
* @usage admin/repair/file/rename
*/

use Softganz\DB;

class AdminRepairFileRename extends Page {
	function build() {
		return new Scaffold([

			'appBar' => new AdminAppBarWidget([
				'title' => 'Rename Files'
			]), // AdminAppBarWidget
			'body' => new Widget([
				'children' => [
					new Nav([
						'mainAxisAlignment' => 'center',
						'child' => new Button([
							'type' => 'primary',
							'class' => 'sg-action',
							'href' => url('admin/repair/file/rename..start'),
							'text' => 'Start Repair',
							'rel' => '#result',
							'attribute' => ['data-title' => 'Confirm', 'data-confirm' => 'Confirm?'],
						])
					]),
					$this->data(),
					new Container(['id' => 'result']),
				], // children
			]), // Widget
		]);
	}

	function data() {
		$data = DB::select([
			'SELECT `file`.`fid`, `file`.`tpid`, `topic`.`type` `nodeType`, `file`.`type` `fileType`, `file`.`tagName`, `file`.`file`
			FROM %topic_files% `file`
				RIGHT JOIN %topic% `topic` ON `file`.`tpid` = `topic`.`tpid`
				RIGHT JOIN %project% `project` ON `topic`.`tpid` = `project`.`tpid`
			WHERE `topic`.`type` = "project"
			ORDER BY `file`.`fid` ASC
			LIMIT 100',
		]);
debugMsg(mydb()->_query);
debugMsg($data, '$data');

		return new Table([
			'thead' => ['File Id', 'Node Id', 'Node Type', 'File Type', 'File Tag Name', 'File Name'],
			'children' => $data->items
		]);
	}

	function start() {
		DB::select([
			'SELECT *
			FROM %topic_file% `file`
				RIGHT JOIN %topic% `topic` ON `file`.`tpid` = `topic`.`tpid`
				RIGHT JOIN %project% `project` ON `topic`.`tpid` = `project`.`tpid`
			LIMIT 100',
		]);
		return mydb()->_query;
	}
}
?>