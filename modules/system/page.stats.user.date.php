<?php
/**
 * Stats    :: List of user hits by date
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2023-07-27
 * Modified :: 2026-08-24
 * Version  :: 2
 *
 * @param String $date
 * @return Widget
 *
 * @uses stats/user/date
 */

use Softganz\DB;

class StatsUserDate extends Page {
	var $date;
	var $right;

	function __construct($date = NULL) {
		parent::__construct([
			'date' => SG\getFirst($date, date('Y-m-d')),
			'right' => (Object) [
				'accessProfile' => user_access('access user profiles')
			]
		]);
	}

	/**
	 * Build page`
	 *
	 * @return object
	 */
	function build(): object {
		$dbs = DB::select([
			'SELECT `log`.`user` AS `userId`, `user`.`name`, COUNT(*) AS `hits`
			FROM %counter_log% AS `log`
				LEFT JOIN %users% AS `user` ON `log`.`user` = `user`.`uid`
			WHERE `log`.`user` IS NOT NULL AND `log`.`log_date` between :start AND :end
			GROUP BY `log`.`user`
			ORDER BY `hits` DESC, CONVERT(`user`.`name` USING tis620) ASC',
			'%WHERE%' => [
			],
			'var' => [
				':start' => $this->date . ' 00:00:00',
				':end' => $this->date . ' 23:59:59'
			]
		]);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Member list',
				'boxHeader' => true,
				'leading' => _HEADER_BACK
			]), // AppBar
			'body' => new Table([
				'thead' => ['no' => '', 'Name', 'hits -amt' => 'Hits'],
				'children' => array_map(
					function($rs) {
						static $no = 0;

						return [
							++ $no,
							($this->right->accessProfile ? '<a href="'.url('profile/'.$rs->userId).'">'.$rs->name.'</a>' : $rs->name),
							number_format($rs->hits)
						];
					},
					$dbs->items
				)
			]), // Table
		]);
	}
}
?>