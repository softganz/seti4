<?php
/**
* Paper :: My Paper
* Created 2021-01-01
* Modify  2021-06-18
*
* @return Widet
*
* @usage paper/my
*/

$debug = true;

class PaperMy extends Page {
	var $isAdminPaper = false;
	var $pageShow;

	function __construct($args = []) {
		parent::__construct($args);
		$this->isAdminPaper = is_admin('paper');
		// debugMsg($this,'$this');
	}

	function build() {
		if (!i()->ok) return message('error', 'Access Denied');

		$isCreatePaper = user_access('create story paper');

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Paper Management',
				'navigator' => [
					new Form([
						'action' => url(q()),
						'class' => 'form-report sg-form -sg-paddingnorm',
						'rel' => '#paper-my',
						'children' => [
							'year' => [
								'type' => 'select',
								'onChange' => 'submit',
								'options' => ['' => '== ทุกปี ==']
									+ $dbs = mydb::select(
										'SELECT YEAR(`created`) `year`, CONCAT("พ.ศ.",YEAR(`created`)+543) `bcyear`
										FROM %topic%
										WHERE `created` IS NOT NULL AND `type` IN ("story", "page")
										GROUP BY `year`
										ORDER BY `year` DESC;
										-- {key: "year", value: "bcyear"}'
									)->items,
								'value' => post('year'),
							],
							'user' => $this->isAdminPaper ? [
								'type' => 'select',
								'onChange' => 'submit',
								'options' => ['' => '== ทุกผู้ส่ง ==']
									+ mydb::select(
										'SELECT u.`uid`, u.`name`
										FROM %topic% t
											LEFT JOIN %users% u USING(`uid`)
										WHERE u.`uid` IS NOT NULL AND `type` IN ("story", "page")
										ORDER BY CONVERT(`name` USING tis620) ASC;
										-- {key: "uid", value: "name"}'
									)->items,
								'value' => post('user'),
							] : NULL,
							'q' => ['type' => 'text', 'placeholder' => 'ค้นหาหัวข้อข่าว', 'value' => post('q'),],
							'go' => ['type' => 'button', 'value' => '<i class="icon -material">search</i>'],
						], // children
					]), // Form
				], // navigator
			]), //AppBar
			// 'sideBar' => new Container([
			// 	'children' => [
			// 		'TAG LIST'
			// 	], // children
			// ]), // SideBar
			'child' => new Container([
				'id' => 'paper-my',
				'children' => [
					$this->_paperList(),
					$this->pageShow,
					$isCreatePaper ? new FloatingActionButton(['children' => ['<a class="sg-action btn -floating" href="'.url('paper/post/story').'" data-rel="box" data-width="full"><i class="icon -material">add</i><span>Create New</span></a>']]) : NULL,
				], // children
			]), // Container
		]);
	}

	function _paperList() {
		$tables = new Table([
			'thead' => ['Title', 'Date', '', 'Edit', 'status -center' => '', ''],
			'rows' => (function() {
				$statusList = [_DRAFT => 'DRAFT', _PUBLISH => 'PUBLISH', _BLOCK => 'BLOCK', _LOCK => 'LOCK'];
				$rows = [];

				$condition = (Object) ['type' => 'story'];
				$options = (Object) ['items' => 50];
				if (is_admin('paper')) {
					if (post('user')) $condition->user = post('user');
				} else {
					$condition->user = i()->uid;
				}
				if (post('year')) $condition->year = post('year');
				if (post('q')) $condition->q = post('q');
				if (post('page')) $options->page = post('page');

				$dbs = R::Model('paper.get.topics', $condition, $options);
				// debugMsg($dbs, '$dbs');

				foreach ($dbs->items as $rs) {
					$rows[] = [
						'<a href="'.url('paper/'.$rs->tpid).'" target="_blank">'.$rs->title.'</a><br />'
						. '<em><small>By '.$rs->owner.'</small></em>',
						sg_date($rs->created, 'd/m/ปป'),
						'<a class="sg-action btn -link" href="'.url('paper/'.$rs->tpid.'/edit.photo').'" data-rel="box" data-width="full"><i class="icon -material">photo</i></a>',
						'<a class="sg-action btn -link" href="'.url('paper/'.$rs->tpid.'/edit.detail').'" data-rel="box" data-width="full"><i class="icon -material">edit</i></a>',
						'<a class="sg-action btn'.($rs->status == _PUBLISH ? ' -success' : '').'" href="'.url('paper/'.$rs->tpid.'/edit.main').'" data-rel="box" data-width="full">'.$statusList[$rs->status].'</a>',
						(new DropBox([
							// 'debug' => true,
							'position' => 'left',
							'children' => [
								$this->isAdminPaper ? '<a class="sg-action" href="'.url('paper/'.$rs->tpid.'/edit.tag').'" data-rel="box" data-width="full"><i class="icon -material">category</i><span>จัดการหมวด</span></a>' : NULL,
								'<hr size="1" />',
								'<a class="sg-action" href="'.url('paper/'.$rs->tpid.'/delete').'" data-rel="none" data-title="ลบหัวข้อ" data-confirm="ต้องกการลบหัวข้อนี้ (รวมทั้งภาพและเอกสารประกอบ) กรุณายืนยัน?" data-done="remove:parent tr"><i class="icon -material">delete</i><span>ลบหัวข้อ</span></a>',
							],
						]))->build(),
					];
				}
				$this->pageShow = $dbs->page->show;
				return $rows;
			})(),
		]);
		return $tables;
	}
}
?>