<?php
/**
* Paper   :: My Paper
* Created :: 2021-01-01
* Modify  :: 2025-09-09
* Version :: 3
*
* @return Widet
*
* @usage paper/my
*/

use Paper\Model\PaperModel;

class PaperMy extends Page {
	var $year;
	var $user;
	var $q;
	var $page;
	var $isAdminPaper = false;
	var $pageShow;

	function __construct() {
		parent::__construct([
			'year' => post('year'),
			'user' => post('user'),
			'q' => post('q'),
			'page' => post('page')
		]);
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
										-- {key: "year", value: "bcyear"}
										'
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
										-- {key: "uid", value: "name"}
										'
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
					$this->paperListWidget(),
					$this->pageShow,
					$isCreatePaper ? new FloatingActionButton(['children' => ['<a class="sg-action btn -floating" href="'.url('paper/post/story').'" data-rel="box" data-width="full"><i class="icon -material">add</i><span>Create New</span></a>']]) : NULL,
				], // children
			]), // Container
		]);
	}

	function paperListWidget() {
		return new Table([
			'thead' => ['Title', 'Date', '', 'Edit', 'status -center' => '', ''],
			'children' => (function() {
				$statusList = [_DRAFT => 'DRAFT', _PUBLISH => 'PUBLISH', _BLOCK => 'BLOCK', _LOCK => 'LOCK'];
				$rows = [];

				$condition = (Object) [
					'type' => 'story',
					'year' => $this->year,
					'user' => $this->user,
					'q' => $this->q,
					'options' => ['items' => 1000, 'page' => $this->page]
				];
				if (is_admin('paper')) {
					if ($this->user) $condition->user = $this->user;
				} else {
					$condition->user = i()->uid;
				}

				$dbs = PaperModel::items($condition);
				// debugMsg($dbs, '$dbs');

				foreach ($dbs->items as $rs) {
					$rows[] = [
						'<a href="'.url('paper/'.$rs->nodeId).'" target="_blank">'.$rs->title.'</a><br />'
						. '<em><small>By '.$rs->ownerName.'</small></em>',
						sg_date($rs->created, 'd/m/ปป'),
						'<a class="sg-action btn -link" href="'.url('paper/'.$rs->nodeId.'/edit.photo').'" data-rel="box" data-width="full"><i class="icon -material">photo</i></a>',
						'<a class="sg-action btn -link" href="'.url('paper/'.$rs->nodeId.'/edit.detail').'" data-rel="box" data-width="full"><i class="icon -material">edit</i></a>',
						'<a class="sg-action btn'.($rs->status == _PUBLISH ? ' -success' : '').'" href="'.url('paper/'.$rs->nodeId.'/edit.main').'" data-rel="box" data-width="full">'.$statusList[$rs->status].'</a>',
						(new Dropbox([
							// 'debug' => true,
							'position' => 'left',
							'children' => [
								$this->isAdminPaper ? '<a class="sg-action" href="'.url('paper/'.$rs->nodeId.'/edit.tag').'" data-rel="box" data-width="full"><i class="icon -material">category</i><span>จัดการหมวด</span></a>' : NULL,
								'<hr size="1" />',
								'<a class="sg-action" href="'.url('paper/'.$rs->nodeId.'/delete').'" data-rel="none" data-title="ลบหัวข้อ" data-confirm="ต้องกการลบหัวข้อนี้ (รวมทั้งภาพและเอกสารประกอบ) กรุณายืนยัน?" data-done="remove:parent tr"><i class="icon -material">delete</i><span>ลบหัวข้อ</span></a>',
							],
						]))->build(),
					];
				}
				$this->pageShow = $dbs->page->show;
				return $rows;
			})(),
		]);
	}
}
?>