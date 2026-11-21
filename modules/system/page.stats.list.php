<?php
/**
 * Stats    :: List Counter Log
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2018-12-15
 * Modified :: 2026-11-21
 * Version  :: 10
 *
 * @uses stats/list
 */

declare(strict_types=1);

namespace Stats;

use Softganz\DB;

class StatsList extends \Page {
	public $id = 0;
	public ?string $ip = null;
	public ?int $user = null;
	public ?string $date = null;
	public ?string $url = null;
	public bool $includeBot = false;
	public int $items = 100;
	public int $page = 1;
	// public $getEid;
	public object $right;

	function __construct() {
		parent::__construct([
			'ip' => \Request::all('ip', 'int'),
			'user' => \SG\getFirstInt(post('user')),
			'date' => \Request::all('date', 'date'),
			'url' => \Request::all('url'),
			'items' => ($items = \Request::all('items', 'int', $this->items)),
			'page' => ($page = \Request::all('page', 'int', $this->page)),
			'id' => $this->getStartId(
				\Request::all('id', 'int', $this->id),
				$page,
				$items
			),
			// 'getEid' => post('eid'),
			'includeBot' => \Request::all('bot', 'en', 'no') === 'yes',
			'right' => (object) [
				'admin' => user_access('administer contents,administer watchdogs')
			]
		]);
	}

	function build() {
		$totalItems = DB::select(['SHOW TABLE STATUS LIKE "%counter_log%"'])
			->items[0]->Rows;

		$counters = $this->getCounterData();

		$pagePara = [
			'user' => $this->user,
			'ip' => $this->ip,
			'date' => $this->date,
			'url' => $this->url,
			'id' => $this->id,
			'items' => $this->items,
			'bot' => $this->includeBot
		];

		$pagenv = new \PageNavigator(
			$this->items,
			$this->page,
			$totalItems,
			q(),
			null,
			$pagePara
		);

		return new \Scaffold([
			'appBar' => new \AppBar([
				'title' => 'Access log listing'
			]), // AppBar
			'body' => new \Widget([
				'children' => [
					$pagenv->show
						? new \Form([
							'class' => 'form-report',
							'action' => \Url::link(q(), $pagePara),
							'children' => [
								'bot' => [
									'type' => 'checkbox',
									'value' => $this->includeBot,
									'options' => ['yes' => 'Include Bot']
								],
								'<button class="btn -link"><i class="icon -material">search</i></button>',
								'<spacer style="flex:1;"></spacer>',
								$pagenv
							]
						])
						: null,
					$this->list($counters),
					$pagenv->show
				] // children
			]) // Widget
		]);
	}

	private function list(object $counters): object {
		return new \Table(
			header: ['no' => 'no', 'Id', 'Log Date', 'User', 'IP'],
			children: array_map(function ($counter) {
				static $no = 0;
				return new \Children(
					children: [
						[
							++$no,
							'<font color=brown>' . $counter->id . '</font>',
							'<em><font color=brown>'
							. $counter->log_date
							. '</font></em>'
							. ($counter->newUser
								? ' <i class="icon -material" title="New comming user">person_eye</i>'
								: ''),
							$this->right->admin
								? '<font color=brown><a href="'
									. \Url::link('stats/list', ['user' => $counter->user])
									. '" title="Statistics of user '
									. $counter->user_name
									. '">'
									. $counter->user_name
									. '</a></font>'
								: '<font color=brown>' . $counter->user_name . '</font>',
							$this->right->admin
								? '<a href="'
									. \Url::link('stats/list', ['ip' => long2ip($counter->ip)])
									. '" title="Statistics of ip '
									. long2ip($counter->ip)
									. '"  data-width="full">'
									. long2ip($counter->ip)
									. '</a>'
								: sg_sub_ip(long2ip($counter->ip))
						],
						[
							'<td></td>',
							'',
							'<td colspan="3">'
							. ($this->right->admin
								? '<font color="#A7A7A7">url:</font><a href="'
									. $counter->url
									. '" target="_blank">'
									. urldecode($counter->url)
									. '</a> <a href="'
									. \Url::link('stats/list', ['url' => $counter->url])
									. '"><i class="icon -material">view_list</i></a><br />'
								: '')
							. (user_access(true)
								? '<font color="#A7A7A7">referer:</font><a href="'
									. $counter->referer
									. '" target=_blank><font color=#A7A7A7>'
									. urldecode($counter->referer ?? '')
									. '</font></a><br />'
								: '')
							. '<font color=#A7A7A7>browser:'
							. $counter->browser
							. '</font></td>'
						]
					]
				);
			}, $counters->items)
		);
	}

	/**
	 * Get start ID
	 *
	 * @param integer $id
	 * @param integer $page
	 * @param integer $items
	 * @return integer
	 */
	private function getStartId(int $id, int $page, int $items): int {
		if (empty($id)) {
			$id = \DB::select(
				'SELECT MAX(`id`) `maxId` FROM %counter_log% LIMIT 1'
			)->valueOf('maxId');
		}
		$id = $id - $page * $items;
		return $id > 0 ? $id : 0;
	}

	/**
	 * Get counter data list
	 *
	 * @return object
	 */
	private function getCounterData(): object {
		$hasPara = \SG\getFirst($this->user, $this->date, $this->ip, $this->url);
		return \DB::select(
			'SELECT `log`.*, `user`.`name` AS `user_name`
			FROM
			(
				SELECT
				`log`.`id`
				, `log`.`log_date`
				, `log`.`new_user` AS `newUser`
				, `log`.`user`
				, `log`.`ip`
				, `log`.`url`
				, `log`.`referer`
				, `log`.`browser`
				FROM %counter_log% AS `log`
				%WHERE%
				ORDER BY `log`.`id` $SUBORDER$
				$LIMIT$
			) AS `log`
				LEFT JOIN %users% AS `user` ON `log`.`user` = `user`.`uid`
			ORDER BY `log`.`id` DESC',
			where: [
				!$hasPara ? ['`log`.`id` >= :id', ':id' => $this->id] : null,
				$this->ip ? ['`log`.`ip` = :ip', ':ip' => ip2long($this->ip)] : null,
				$this->user ? ['`log`.`user` = :user', ':user' => $this->user] : null,
				$this->date
					? [
						'DATE_FORMAT(`log`.`log_date`, "%Y-%m-%d") = :date',
						':date' => $this->date
					]
					: null,
				$this->url ? ['`log`.`url` = :url', ':url' => $this->url] : null
				// !$this->includeBot ? ['`log`.`referer` NOT LIKE "%bot%"'] : null
			],
			var: [
				'$LIMIT$' =>
					'LIMIT '
					. ($hasPara
						? ($this->page - 1) * $this->items . ',' . $this->items
						: $this->items),
				'$SUBORDER$' => $hasPara ? 'DESC' : 'ASC'
			]
		);
	}
}
?>
