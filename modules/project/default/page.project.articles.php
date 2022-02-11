<?php
/**
* Project :: Article/Research Listing
* Created 2022-01-24
* Modify  2022-01-24
*
* @param Object $projectInfo
* @return Widget
*
* @usage proejct/{id}/info.articles
*/

import('widget:project.follow.nav.php');

class ProjectArticles extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'บทความ/งานวิจัย',
				'leading' => '<i class="icon -material">menu_book</i>',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new ListTile([
						'title' => 'บทความ',
						'leading' => '<i class="icon -material">menu_book</i>',
						'trailing' => new Row([
							'children' => [
								// $this->right->edit ? '<a class="sg-action btn -primary" href="'.url('project/'.$this->projectId.'/info.article.form').'" data-rel="box" data-width="480"><i class="icon -material">add</i><span>เพิ่ม</span></a>' : NULL,
							], // children
						]), // Row
					]), // ListTile

					new Table([
						'thead' => ['ชื่อบทความ', 'create -date' => 'วันที่'],
						'children' => array_map(
							function ($item) {
								return [
									'<a href="'.url('project/'.$item->projectId.'/info.article/'.$item->articleId).'">'.$item->title.'</a>',
									sg_date($item->created, 'ว ดด ปปปป'),
								];
							},
							mydb::select(
								'SELECT `trid` `articleId`, `tpid` `projectId`, `detail1` `title`, `created`
								FROM %project_tr%
								WHERE `formId` = "info" AND `part` = "article"
								ORDER BY `trid` DESC',
								['projectId' => $this->projectId]
							)->items
						), // children
					]),

					new ListTile([
						'title' => 'ผลงานวิจัย',
						'leading' => '<i class="icon -material">menu_book</i>',
						'trailing' => new Row([
							'children' => [
								// $this->right->edit ? '<a class="sg-action btn -primary" href="'.url('project/'.$this->projectId.'/info.article.form').'" data-rel="box" data-width="480"><i class="icon -material">add</i><span>เพิ่ม</span></a>' : NULL,
							], // children
						]), // Row
					]), // ListTile

					new Table([
						'thead' => ['ชื่องานวิจัย', 'create -date' => 'วันที่'],
						'children' => array_map(
							function ($item) {
								return [
									'<a href="'.url('project/'.$item->projectId.'/info.research/'.$item->articleId).'">'.$item->title.'</a>',
									sg_date($item->created, 'ว ดด ปปปป'),
								];
							},
							mydb::select(
								'SELECT `trid` `articleId`, `tpid` `projectId`, `detail1` `title`, `created`
								FROM %project_tr%
								WHERE `tpid` = :projectId AND `formId` = "info" AND `part` = "research"
								ORDER BY `trid` DESC',
								['projectId' => $this->projectId]
							)->items
						), // children
					]),

					new FloatingActionButton([
						'children' => [
								$this->right->edit ? '<a class="sg-action btn -floating" href="'.url('project/'.$this->projectId.'/info.article.new').'" data-rel="box" data-width="480"><i class="icon -material">add</i><span>เพิ่มบทความ</span></a>' : NULL,
								$this->right->edit ? '<a class="sg-action btn -floating" href="'.url('project/'.$this->projectId.'/info.research.new').'" data-rel="box" data-width="480"><i class="icon -material">add</i><span>เพิ่มงานวิจัย</span></a>' : NULL,
						],
					])
				], // children
			]), // Widget
		]);
	}
}
?>