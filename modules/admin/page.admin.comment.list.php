<?php
/**
 * Admin    :: List Topic and Comment
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2020-01-01
 * Modified :: 2026-06-30
 * Version  :: 3
 *
 * @return Widget
 * 
 * @uses admin/comment/list
 */

use Softganz\DB;

class AdminCommentList extends Page {
	var $items = 100;
	var $page = 1;
	var $search;
	var $noConfirm;
	var $right;

	function __construct() {
		parent::__construct([
			'items' => \SG\getFirst(Request::all('item', 'int'), $this->items),
			'page' => \SG\getFirst(Request::all('page', 'int'), $this->page),
			'search' => \SG\getFirst(Request::all('search')),
			'noConfirm' => Request::all('noConfirm'),
			'right' => (Object) [
				'edit' => user_access('administer comments')
			]
		]);
	}

	/**
	 * Build page
	 *
	 * @return object
	 */
	#[\Override]
	function build(): object {
		$contents = $this->getContents();
		$page_nv = $this->pageNv($contents);

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Comment list',
				'leading' => new Icon('chat_bubble'),
				'navigator' => $this->filterForm(),
			]), // AppBar
			'body' => new Widget([
				'children' => [
					$page_nv,
					new Table([
						'id' => 'comments-list',
						'caption' => 'รายการความคิดเห็นล่าสุด',
						'thead' => ['id -amt' => 'หมายเลข', 'icon -center' => '', 'type -nowrap' => 'type', 'รายละเอียด', 'profile -icons' => '', 'name -nowrap' => 'ผู้โพสท์','create -date'=>'วันที่'],
						'children' => array_map(
							function($node) {
								if ($this->right->edit) {
									if ($node->cid) {
										$deleteBtn = '<a class="sg-action" href="'.Url::link('api/paper/' . $node->tpid . '/comment.delete', ['commentId' => $node->cid, 'confirm' => $this->noConfirm ? 'yes' : null]) . '" title="Delete this comment" data-rel="notify" '.($this->noConfirm ? '' : 'data-confirm="Delete this comment?"') . ' data-before="remove:parent tr"><i class="icon -material">cancel</i></a>';
									} else if ($node->type === 'forum') {
										$deleteBtn = '<a class="sg-action" href="' . Url::link('paper/' . $node->tpid . '/delete', ['confirm' => $this->noConfirm ? 'yes' : null]) . '" title="Delete this paper" data-rel="none" ' . ($this->noConfirm ? '' : 'data-confirm="Delete this paper?"') . ' data-before="remove:parent tr"><i class="icon -material">delete</i></a>';
									} else {
										$deleteBtn = '';
									}
								}
								$nodeUrl = '';

								switch ($node->type) {
									case 'project':
										$nodeUrl = Url::link('project/' . $node->tpid);
										break;
									
									case 'project-develop':
										$nodeUrl = Url::link('project/proposal/' . $node->tpid);
										break;

									default:
										$nodeUrl = Url::link('paper/' . $node->tpid, null, $node->cid ? 'comment-' . $node->cid : null);
										break;
								}
	
								return [
									'<a href="'.$nodeUrl.'" title="' . htmlspecialchars($node->title) . '" target="_blank">' . \SG\getFirst($node->cid, $node->tpid) . '</a>',
									$deleteBtn,
									$node->type,
									sg_text2html($node->comment),
									new ProfilePhoto($node->username),
									$node->uid ? new Button([
										'class' => 'sg-action',
										'href' => Url::link('profile/'.$node->uid),
										'rel' => 'box',
										'boxWidth' => '640',
										'text' => $node->name . ($node->userStatus != 'enable' ? ' (' . $node->userStatus . ')' : '')
									 ]) : $node->name,
									$node->timestamp
								];
							},
							$contents->items
						),
					]), // Table
					$page_nv
				], // children
			]), // Widget
		]);
	}

	private function filterForm() {
		return new Form([
			'class' => 'form-report',
			'method' => 'GET',
			'action' => Url::link('admin/comment/list', ['noconfirm' => $this->noConfirm]),
			'children' => [
				'<span>Search in comment </span>',
				'search' => [
					'type' => 'text',
					'value' => $this->search,
				],
					'submit' => [
					'type' => 'button',
					'icon' => new Icon('search'),
					'text' => 'GO'
				],
					'noConfirm' => [
					'type' => 'checkbox',
					'name' => 'noConfirm',
					'value' => $this->noConfirm,
					'choices' => ['yes' => 'No Confirm on Delete']
				]
			]
		]);
	}

	private function pageNv($contents) {
		return '<p>Page : '
		. '<a href="' . Url::link(q(), ['search' => $this->search, 'noConfirm' => $this->noConfirm]) . '">First</a> | '
		. ($this->page > 1 ? '<a href="' . Url::link(q(), ['page' => $this->page - 1, 'search' => $this->search]) . '">Previous</a> | ' : 'Previous | ')
		. '( <strong>' . $this->page . '</strong> )' . ($contents->count == $this->items ? ' | '
		. '<a href="'.Url::link(q(), ['page' => $this->page + 1, 'search' => $this->search, 'noConfirm' => $this->noConfirm]) . '">Next</a>' : '')
		. '</p>';
	}

	private function getContents() {
		return DB::select([
			'SELECT
				c.`tpid`
			, c.`cid`
			, t.`type`
			, t.`title`
			, c.`status`
			, c.`comment`
			, c.`timestamp`
			, c.`uid`
			, IFNULL(u.`name`, c.`name`) `name`
			, u.`status` `userStatus`
			, u.`username`
			FROM %topic_comments%  c
				LEFT JOIN %topic% t USING (tpid)
				LEFT JOIN %users% u ON c.`uid`=u.`uid`
			WHERE (TRIM(c.`subject`) = "")
			'.($this->search ? ' AND (c.`comment` LIKE :searchStr OR c.`name` LIKE :searchStr)' : '').'
			UNION
			SELECT
				t.`tpid`
			, NULL
			, t.`type`
			, t.`title`
			, NULL
			, t.`title` `comment`
			, t.`created` `timestamp`
			, t.`uid`
			, IFNULL(u.`name`, t.`poster`) `name`
			, u.`status` `userStatus`
			, u.`username`
			FROM %topic% t
				LEFT JOIN %users% u USING (`uid`)
			'
			. ($this->search ? 'WHERE t.`title` like :searchStr OR t.`poster` LIKE :searchStr' : '')
			. '
			ORDER BY `timestamp` DESC
			LIMIT '.(($this->page - 1) * $this->items) . ' , ' . $this->items,
			'var' => [
				':searchStr' => '%' . $this->search . '%'
			]
		]);
	}
}
?>