<?php
/**
* Stats   :: Hit Per Month
* Created :: 2022-12-20
* Modify  :: 2022-12-20
* Version :: 1
*
* @return Widget
*
* @usage ststa/hits/per/month
*/

class StatsHitsPerMonth extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Hits Per Month',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new ListTile(['title' => 'Hits per month', 'leading' => new Icon('groups')]),
					R::View('stats.hits.per.month'),
				], // children
			]), // Widget
		]);
	}
}
?>
