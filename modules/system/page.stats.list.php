<?php
/**
 * Stats    :: List Counter Log
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2018-12-15
 * Modified :: 2026-08-24
 * Version  :: 9
 *
 * @return Widget
 *
 * @uses stats/list
 */

use Softganz\DB;

class StatsList extends Page {
	var $id = 0;
	var $ip;
	var $user;
	var $date;
	var $url;
	// var $getEid;
	var $includeBot = false;
	var $items = 100;
	var $page = 1;
	var $right;

	function __construct() {
		parent::__construct([
			'ip' => post('ip'),
			'user' => SG\getFirstInt(post('user')),
			'date' => post('date'),
			'url' => post('url'),
			'items' => $items = SG\getFirst(post('items'), $this->items),
			'page' => $page = SG\getFirst(post('page'), $this->page),
			'id' => $this->getStartId(SG\getFirstInt(post('id'), $this->id), $page, $items),
			// 'getEid' => post('eid'),
			'includeBot' => post('bot'),
			'right' => (Object) [
				'admin' => user_access('administer contents,administer watchdogs')
			]
		]);

	}

	function build() {
		$totalItems = DB::select(['SHOW TABLE STATUS LIKE "%counter_log%"'])->items[0]->Rows;

		$counters = $this->data();

		$pagePara = [
			'user' => $this->user,
			'ip' => $this->ip,
			'date' => $this->date,
			'url' => $this->url,
			'id' => $this->id,
			'items' => $this->items,
			'bot' => $this->includeBot,
		];

		$pagenv = new PageNavigator($this->items, $this->page, $totalItems, q(), NULL, $pagePara);

		$no = 0;

		$tables = new Table([
			'thead' => ['no' => 'no', 'Id', 'Log Date', 'User','IP']
		]);
		foreach ($counters->items as $counter) {
			$tables->rows[] = [
				++$no,
				'<font color=brown>'.$counter->id.'</font>',
				'<em><font color=brown>'.$counter->log_date.'</font></em>'.($counter->new_user ? ' <i class="icon -material">new_releases</i>' : ''),
				($this->right->admin ? '<font color=brown><a href="'.url('stats/list', ['user' => $counter->user]).'" title="Statistics of user '.$counter->user_name.'">'.$counter->user_name.'</a></font>' : '<font color=brown>'.$counter->user_name.'</font>'),
				($this->right->admin ? '<a href="'.url('stats/list', ['ip' => long2ip($counter->ip)]).'" title="Statistics of ip '.long2ip($counter->ip).'"  data-width="full">'.long2ip($counter->ip).'</a>' : sg_sub_ip(long2ip($counter->ip))),
			];

			$tables->rows[] = [
				'<td></td>',
				'',
				'<td colspan="3">'
				. ($this->right->admin ? '<font color="#A7A7A7">url:</font><a href="'.$counter->url.'" target="_blank">'.urldecode($counter->url).'</a> <a href="'.url('stats/list', ['url' => $counter->url]).'"><i class="icon -material">view_list</i></a><br />':'')
				. (user_access(true) ? '<font color="#A7A7A7">referer:</font><a href="'.$counter->referer.'" target=_blank><font color=#A7A7A7>'.urldecode($counter->referer).'</font></a><br />':'')
				. '<font color=#A7A7A7>browser:'.$counter->browser.'</font></td>',
			];
		}

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Access log listing',
			]), // AppBar
			'body' => new Widget([
				'children' => [
					!$para->option->no_page && $pagenv->show ? new Form([
						'class' => 'form-report',
						'action' => url(q(), $pagePara),
						'children' => [
							'bot' => ['type' => 'checkbox', 'value' => $this->includeBot, 'options' => ['yes' => 'Include Bot']],
							'<button class="btn -link"><i class="icon -material">search</i></button>',
							'<spacer style="flex:1;"></spacer>',
							$pagenv
						]
					]) : NULL,
					$tables,
					!$para->option->no_page && $pagenv->show ? $pagenv->show : NULL,
				], // children
			]), // Widget
		]);
	}

	private function getStartId($id, $page, $items) {
		if (empty($id)) $id = DB::select(['SELECT MAX(`id`) `maxId` FROM %counter_log% LIMIT 1'])->maxId;
		$id = $id - ($page * $items);
		return $id > 0 ? $id : 0;
	}

	private function data() {
		$hasPara = SG\getFirst($this->user, $this->date, $this->ip, $this->url);
		return DB::select([
			'SELECT log.*, u.`name` `user_name`
			FROM
			(
				SELECT `id`, `log_date`, `user`, `ip`, `url`, `referer`, `browser`
				FROM %counter_log% l
				%WHERE%
				ORDER BY l.`id` $SUBORDER$
				$LIMIT$
			) AS log
				LEFT JOIN %users% AS u ON log.`user` = u.`uid`
			ORDER BY `id` DESC',
			'where' => [
				'%WHERE%' => [
					!$hasPara ? ['l.`id` >= :id', ':id' => $this->id] : NULL,
					$this->ip ? ['l.`ip` = :ip', ':ip' => ip2long($this->ip)] : NULL,
					$this->user ? ['l.`user` = :user', ':user' => $this->user] : NULL,
					$this->date ? ['DATE_FORMAT(l.`log_date`,"%Y-%m-%d") = :date', ':date' => $this->date] : NULL,
					$this->url ? ['l.`url` = :url', ':url' => $this->url] : NULL,
					!$this->includeBot ? ['l.`referer` NOT LIKE "%bot%"'] : NULL,
				]
			],
			'var' => [
				'$LIMIT$' => 'LIMIT '.($hasPara ? ($this->page-1)*$this->items.','.$this->items : $this->items),
				'$SUBORDER$' => $hasPara ? 'DESC' : 'ASC',
			],
		]);
	}
}
?>