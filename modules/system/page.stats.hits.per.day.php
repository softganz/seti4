<?php
/**
* Stats   :: Hit Per Day
* Created :: 2022-12-20
* Modify  :: 2022-12-20
* Version :: 2
*
* @param String $year
* @return Widget
*
* @usage ststa/hits/per/day
*/

class StatsHitsPerDay extends Page {
	var $year;

	function __construct($year = NULL) {
		parent::__construct([
			'year' => $year
		]);
	}

	function build() {
		if ($this->year) {
			list($year,$month) = explode('-', $this->year);
		} else {
			$month = date('m');
			$year = date('Y');
		}

		// $self->theme->navigator=user_menu();

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Hits Per Day',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new ListTile(['title' => 'Hits per day', 'leading' => new Icon('groups')]),
					R::View('stats.hits.per.day',$year,$month),
				], // children
			]), // Widget
		]);
	}
}
?>
