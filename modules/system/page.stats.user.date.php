<?php
/**
* Stats   :: List of stats by date
* Created :: 2023-07-27
* Modify  :: 2023-07-27
* Version :: 1
*
* @param String $date
* @return Widget
*
* @usage stats/user/date
*/

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

	function build() {
		$dbs = mydb::select(
			'SELECT a.*, u.`name`
			FROM
				(SELECT DISTINCT
					DATE(w.`date`), w.`uid`
				FROM %watchdog% w
				WHERE w.`uid` IS NOT NULL AND w.`date` between :start AND :end
				) a
				LEFT JOIN %users% u USING(`uid`)
			ORDER BY u.`name` ASC',
			[
				':start' => $this->date.' 00:00:00',
				':end' => $this->date.' 23:59:59'
			]
		);

		$ret .= '<ol>';
		foreach ($dbs->items as $rs) {
			$ret .= '<li>'
				. ($this->right->accessProfile ? '<a href="'.url('profile/'.$rs->uid).'">'.$rs->name.'</a>' : $rs->name)
				. '</li>';
		}
		$ret .= '</ol>';

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Member list',
				'boxHeader' => true,
			]), // AppBar
			'body' => new Widget([
				'children' => [$ret], // children
			]), // Widget
		]);
	}
}
?>