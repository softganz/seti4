<?php
/**
* Project Nxt :: Knowladge Management Main Page
* Created 2021-10-31
* Modify  2021-10-31
*
* @return Widget
*
* @usage project/nxt/km
*/

$debug = true;

class ProjectNxtKm extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'คลังข้อมูล',
				'leading' => '<i class="icon -material">movie</i>'
			]),
			'body' => new Container([
				'children' => [
					R::PageWidget('paper.my', ['tags/1005,1006']),
				], // children
			]), // Container
		]);
	}
}
?>