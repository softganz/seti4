<?php
/**
* Project Nxt :: News Main Page
* Created 2021-10-31
* Modify  2021-10-31
*
* @return Widget
*
* @usage project/nxt/news
*/

$debug = true;

class ProjectNxtNews extends Page {
	function build() {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'การสื่อสารประชาสัมพันธ์',
				'leading' => '<i class="icon -material">receipt</i>',
				'navigator' => [
					new Form([
						'action' => url(q()),
						'class' => 'sg-form -sg-flex',
						'rel' => '#paper-my',
						'children' => [
							'year' => [
								'type' => 'select',
								'options' => ['' => '== ทุกปี =='] + mydb::select('SELECT YEAR(`created`) `year`, CONCAT("พ.ศ.",YEAR(`created`)+543) `bcyear` FROM %topic% WHERE `created` > 0 GROUP BY `year` ORDER BY `year` DESC; -- {key: "year", value: "bcyear"}')->items,
								'value' => post('year'),
							],
							'user' => $this->isAdminPaper ? [
								'type' => 'select',
								'options' => ['' => '== ทุกผู้ส่ง =='] + mydb::select('SELECT u.`uid`, u.`name` FROM %topic% t LEFT JOIN %users% u USING(`uid`) WHERE u.`uid` IS NOT NULL ORDER BY CONVERT(`name` USING tis620) ASC; -- {key: "uid", value: "name"}')->items,
								'value' => post('user'),
							] : NULL,
							'q' => ['type' => 'text', 'placeholder' => 'ค้นหาหัวข้อข่าว', 'value' => post('q'),],
							'go' => ['type' => 'button', 'value' => '<i class="icon -material">search</i>'],
						], // children
					]), // Form
				], // navigator
			]),
			'body' => new Widget([
				'children' => [
					R::PageWidget('paper.my'),
				], // children
			]), // Row
		]);
	}
}
?>