<?php
/**
* Admin   :: Load Test Count
* Created :: 2024-10-03
* Modify  :: 2024-10-03
* Version :: 1
*
* @return Widget
*
* @usage admin/loadtest/count
*/

use Softganz\DB;

class AdminLoadtestCount extends Page {
	function build() {
		$data = $this->data();

		// debugMsg($data, '$data');
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Load Test Count',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Table([
						'class' => '-center',
						'thead' => ['Date', 'Amount'],
						'children' => $data->items,
					])
				], // children
			]), // Widget
		]);
	}

	private function data() {
		return DB::select([
			'SELECT DATE_FORMAT(`log_date`,"%Y-%m-%d %H:%i") `label`,COUNT(*) `amt`
			FROM %ztest_counter_log%
			GROUP BY `label`
			ORDER BY `label` DESC
			LIMIT 10000'
		]);
	}
}
?>