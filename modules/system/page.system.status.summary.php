<?php
/**
 * System. :: Information
 * Author  :: Little Bear<softganz@gmail.com>
 * Created :: 2026-03-05
 * Modify  :: 2026-03-05
 * Version :: 1
 *
 * @return Widget
 *
 * @usage system/status/summary
 */

use Softganz\DB;

class SystemStatusSummary extends Page {
	function rightToBuild() {
		if (!is_admin()) return error(_HTTP_ERROR_FORBIDDEN, 'Access denied');

		return true;
	}

	#[\Override]
	function build() {
		// return error(_HTTP_ERROR_BAD_REQUEST, 'Bad request');
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'System Status',
			]),
			'body' => new Widget([
				'children' => [
					new DashboardWidget([
						'class' => '-width-wide',
						'children' => [
							[
								'title' => 'Date',
								'value' => date('Y-m-d'),
								'class' => '-green'
							],
							[
								'title' => 'Time',
								'value' => date('H:i:s'),
								'class' => '-red'
							],
							[
								'title' => 'Users Online',
								'value' => number_format(CounterModel::onlineMemberCount()) . '/' . number_format(CounterModel::onlineCount()),
								'class' => '-yellow'
							],
							[
								'title' => 'IP',
								'value' => $_SERVER['REMOTE_ADDR'],
								'class' => '-blue'
							],
							// [
							// 	'title' => 'หลักสูตรอนุมัติ',
							// 	'value' => number_format($summaries->totalFollow),
							// 	'unit' => 'หลักสูตร <b>'.($summaries->totalProposal ? round($summaries->totalFollow*100/$summaries->totalProposal, 2) : '').'</b>%'
							// ],
						]
					]), // Dashboard				]
				],
			])
		]);
	}
}
?>