<?php
/**
 * Paper    :: My Paper
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2021-01-01
 * Modified :: 2026-07-12
 * Version  :: 4
 *
 * @return Widet
 *
 * @uses paper/my
 */

use Paper\Model\PaperModel;
use Softganz\DB;

class PaperMy extends Page {
	var $year;
	var $user;
	var $search;
	var $page = 1;
	var $right;
	var $pageShow;

	function __construct() {
		parent::__construct([
			'year' => Request::all('year', 'int'),
			'user' => Request::all('user', 'int'),
			'search' => Request::all('q'),
			'page' => \SG\getFirst(Request::all('page', 'int'), $this->page),
			'right' => (Object) [
				'admin' => is_admin('paper'),
				'createPaper' => user_access('create story paper')
			]
		]);
	}

	/**
	 * Right to build
	 *
	 * @return object|boolean
	 */
	function rightToBuild(): object|bool {
		if (!i()->ok) return new SignForm();

		return true;
	}

	/**
	 * Build page
	 *
	 * @return object
	 */
	#[\Override]
	function build(): object {
		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Paper Management',
				'navigator' => [
					new Form([
						'action' => url(q()),
						'class' => 'form-report sg-form -sg-paddingnorm -full-width',
						'rel' => '#paper-my',
						'children' => [
							'year' => [
								'type' => 'select',
								'onChange' => 'submit',
								'options' => ['' => '== ทุกปี ==']
									+ (Array) DB::select([
										'SELECT YEAR(`created`) `year`, CONCAT("พ.ศ.",YEAR(`created`)+543) `bcyear`
										FROM %topic%
										WHERE `created` IS NOT NULL AND `type` IN ("story", "page")
										GROUP BY `year`
										ORDER BY `year` DESC',
										'options' => ['key' => 'year', 'value' => 'bcyear']
								])->items,
								'value' => $this->year,
							],
							'user' => $this->right->admin ? [
								'type' => 'select',
								'onChange' => 'submit',
								'options' => ['' => '== ทุกผู้ส่ง ==']
									+ (Array) DB::select([
										'SELECT u.`uid`, u.`name`
										FROM %topic% t
											LEFT JOIN %users% u USING(`uid`)
										WHERE u.`uid` IS NOT NULL AND `type` IN ("story", "page")
										ORDER BY CONVERT(`name` USING tis620) ASC',
										'options' => ['key' => 'uid', 'value' => 'name']
									])->items,
								'value' => $this->user,
							] : NULL,
							'q' => ['type' => 'text', 'placeholder' => 'ค้นหาหัวข้อข่าว', 'value' => $this->search,],
							'go' => ['type' => 'button', 'value' => '<i class="icon -material">search</i>'],
						], // children
					]), // Form
				], // navigator
			]), //AppBar
			'child' => new Container([
				'id' => 'paper-my',
				'children' => [
					$this->paperListWidget(),
					$this->pageShow,
					$this->right->createPaper ? new FloatingActionButton(['children' => ['<a class="sg-action btn -floating" href="'.url('paper/post/story').'" data-rel="box" data-width="full"><i class="icon -material">add</i><span>Create New</span></a>']]) : NULL,
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
					'searchText' => $this->search,
					'options' => ['items' => 1000, 'page' => $this->page, 'debug' => true]
				];
				if (is_admin('paper')) {
					if ($this->user) $condition->user = $this->user;
				} else {
					$condition->user = i()->uid;
				}

				$dbs = PaperModel::items($condition);

				foreach ($dbs->items as $rs) {
					$rows[] = [
						'<a href="'.url('paper/'.$rs->nodeId).'" target="_blank">'.$rs->title.'</a><br />'
						. '<em><small>By '.$rs->ownerName.'</small></em>',
						sg_date($rs->created, 'd/m/ปป'),
						'<a class="sg-action btn -link" href="'.url('paper/'.$rs->nodeId.'/edit.photo').'" data-rel="box" data-width="full"><i class="icon -material">photo</i></a>',
						'<a class="sg-action btn -link" href="'.url('paper/'.$rs->nodeId.'/edit.detail').'" data-rel="box" data-width="full"><i class="icon -material">edit</i></a>',
						'<a class="sg-action btn'.($rs->status == _PUBLISH ? ' -success' : '').'" href="'.url('paper/'.$rs->nodeId.'/edit.main').'" data-rel="box" data-width="full">'.$statusList[$rs->status].'</a>',
						(new Dropbox([
							'position' => 'left',
							'children' => [
								$this->right->admin ? '<a class="sg-action" href="'.url('paper/'.$rs->nodeId.'/edit.tag').'" data-rel="box" data-width="full"><i class="icon -material">category</i><span>จัดการหมวด</span></a>' : NULL,
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