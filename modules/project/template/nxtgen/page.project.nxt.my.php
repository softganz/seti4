<?php
/**
* Project Nxt :: My
* Created 2021-11-02
* Modify  2021-11-02
*
* @return Widget
*
* @usage project/nxt/my
*/

$debug = true;

class ProjectNxtMy extends Page {
	function build() {
		if (!i()->ok) return R::View('signform');

		mydb::where('of.`uid` = :uid', ':uid', i()->uid);

		$dbs = mydb::select(
			'SELECT
			o.`orgId`, o.`name`
			, of.`uid`, of.`membership`
			FROM %org_officer% of
				RIGHT JOIN %db_org% o USING(`orgid`)
			%WHERE%
			GROUP BY `orgid`
			ORDER BY CONVERT(`name` USING tis620) ASC'
		);

		if ($dbs->_empty) {
			return message('error', 'ขออภัย ท่านยังไม่ได้เป็นเจ้าหน้าที่ขององค์กร');
		} else if ($dbs->_num_rows == 1) {
			location('org/'.$dbs->items[0]->orgId);
		} else {
			// Select orgid
			return new Scaffold([
				'appBar' => new AppBar([
					'title' => 'หลักสูตรของฉัน',
				]), // AppBar
				'body' => new Widget([
					'children' => [
						new Container([
							'children' => (function($items) {
								$widgets = [];
								foreach ($items as $item) {
									$widgets[] = new Card([
										'class' => 'sg-action -sg-paddingmore',
										'href' => url('org/'.$item->orgId),
										'children' => [
											new ListTile([
												'title' => $item->name,
												'leading' => '<i class="icon -material">home</i>',
											]),
										], // children
									]);
								}
								return $widgets;
							})($dbs->items),
						]),
					], // children
				]), // Container
			]);
		}
	}
}
?>