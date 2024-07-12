<?php
/**
* Admin   :: Repair Like Times
* Created :: 2024-07-10
* Modify  :: 2024-07-10
* Version :: 2
*
* @return Widget
*
* @usage admin/repair/like
*/

use Softganz\DB;

class AdminRepairLike extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Repair Like Times',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Nav([
						'mainAxisAlignment' => 'center',
						'child' => new Button([
							'type' => 'primary',
							'class' => 'sg-action',
							'href' => url('admin/repair/like..start'),
							'text' => 'Start Repair',
							'rel' => '#result',
							'attribute' => ['data-title' => 'Confirm', 'data-confirm' => 'Confirm?'],
						])
					]),
					new Container(['id' => 'result']),
				], // children
			]), // Widget
		]);
	}

	function start() {
		DB::query([
			'UPDATE
			%topic% AS t
			SET
				t.`liketimes` = (
					SELECT COUNT(*)
					FROM %reaction% m
					WHERE m.`refid` = t.`tpid`
					AND m.`action` IN ("PROJ.LIKE","PDEV.LIKE","TOPIC.LIKE")
				)',
		]);

		return mydb()->_query;
	}
}
?>