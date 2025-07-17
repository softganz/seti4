<?php
/**
* Stats   :: List Counter Log
* Created :: 2018-12-15
* Modify  :: 2025-07-17
* Version :: 3
*
* @return Widget
*
* @usage stats/list
*/

use Softganz\DB;

class StatsList extends Page {
	var $id = 0;
	var $ip;
	var $user;
	var $date;
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
			'id' => $this->id,
			'items' => $this->items,
			'bot' => $this->includeBot,
		];

		$pagenv = new PageNavigator($this->items, $this->page, $totalItems, q(), NULL, $pagePara);

		$no = 0;

		$tables = new Table([
			'thead' => ['no' => 'no', 'Id', 'Log Date', 'User','IP']
		]);
		foreach ($counters->items as $rs) {
			$tables->rows[] = [
				++$no,
				'<font color=brown>'.$rs->id.'</font>',
				'<em><font color=brown>'.$rs->log_date.'</font></em>'.($rs->new_user ? ' <i class="icon -material">new_releases</i>' : ''),
				($this->right->admin ? '<font color=brown><a class="sg-action" href="'.url('stats/list',array('user'=>$rs->user)).'" data-rel="box" title="Statistics of user '.$rs->user_name.'">'.$rs->user_name.'</a></font>':'<font color=brown>'.$rs->user_name.'</font>'),
				($this->right->admin ? '<a href="'.url('stats/list',array('ip'=>long2ip($rs->ip))).'" title="Statistics of ip '.long2ip($rs->ip).'">'.long2ip($rs->ip).'</a>' : sg_sub_ip(long2ip($rs->ip))),
			];

			$tables->rows[] = [
				'<td></td>',
				'',
				'<td colspan="3">'
				. ($this->right->admin ? '<font color=#A7A7A7>url:</font><a class="sg-action" href="'.$rs->url.'" data-rel="box" data-width="100%" data-height="100%">'.urldecode($rs->url).'</a><br />':'')
				. (user_access(true) ? '<font color=#A7A7A7>referer:</font><a href="'.$rs->referer.'" target=_blank><font color=#A7A7A7>'.urldecode($rs->referer).'</font></a><br />':'')
				. '<font color=#A7A7A7>browser:'.$rs->browser.'</font></td>',
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
		return DB::select([
			'SELECT log.*, u.`name` `user_name`
			FROM
			(
				SELECT *
				FROM %counter_log% l
				%WHERE%
				ORDER BY l.`id` ASC
				$LIMIT$
			) AS log
				LEFT JOIN %users% AS u ON log.`user` = u.`uid`
			ORDER BY `id` DESC',
			'where' => [
				'%WHERE%' => [
					['l.`id` >= :id', ':id' => $this->id],
					$this->ip ? ['l.`ip` = :ip', ':ip' => ip2long($this->ip)] : NULL,
					$this->user ? ['l.`user` = :user', ':user' => $this->user] : NULL,
					$this->date ? ['DATE_FORMAT(l.`log_date`,"%Y-%m-%d") = :date', ':date' => $this->date] : NULL,
					!$this->includeBot ? ['l.`referer` NOT LIKE "%bot%"'] : NULL,
				]
			],
			'var' => [
				'$LIMIT$' => 'LIMIT '.$this->items,
			],
			'options' => ['key' => 'id']
		]);



		// Unused code
		// $isEmptyPara = !$this->ip && !$this->user && !$this->date;

		// if ($this->ip) mydb::where('l.`ip` = :ip',':ip',ip2long($this->ip));
		// if ($this->user) mydb::where('l.`user` = :user',':user',$this->user);
		// if ($this->date) mydb::where('DATE_FORMAT(l.`log_date`,"%Y-%m-%d") = :date',':date',$this->date);
		// if (!$this->includeBot) mydb::where('l.`referer` NOT LIKE "%bot%"');

		// if ($isEmptyPara) {
		// 	$rs = mydb::select('SELECT MIN(`id`) `minid`, MAX(`id`) `maxid` FROM %counter_log% LIMIT 1; -- {reset: false}');
		// 	$minId = $rs->minid;
		// 	$maxId = $rs->maxid;

		// 	//if ($isEmptyPara) {
		// 		$startId = $maxId - ($this->page * $this->items) + 1;
		// 		mydb::where('l.`id` >= :id', ':id', $startId);
		// 		mydb::value('$LIMIT$', 'LIMIT '.$this->items);
		// 	//} else {
		// 	//	$minId = \SG\getFirst($getEid,$rs->minid);
		// 	//	$startId = $minId + (($this->page - 1) * $this->items);
		// 	//	mydb::value('$LIMIT$', 'LIMIT '.$this->items);
		// 	//}

		// 	//$ret .= 'Min id = '.$minId.' Max id = '.$maxId.' Srart id = '.$startId.'<br />';

		// 	// FAST Query but bug on condition, cannot get all found rows
		// 	$dbs = mydb::select(
		// 		'SELECT log.*, u.`name` `user_name`
		// 		FROM
		// 		(
		// 			SELECT *
		// 			FROM %counter_log% l
		// 			%WHERE%
		// 			ORDER BY l.`id` ASC
		// 			$LIMIT$
		// 		) AS log
		// 		LEFT JOIN %users% AS u ON log.`user` = u.`uid`
		// 		ORDER BY `id` DESC;
		// 		-- {key: "id"}
		// 		'
		// 	);

		// 	//if ($isEmptyPara) {
		// 	//	$totalItems = $maxId - $minId;
		// 	//} else {
		// 		//$totalItems = $dbs->count() < $this->items ? $this->page*$this->items :  $maxId - $minId;
		// 	//}
		// 	$pagePara['eid'] = end($dbs->items)->id;
		// } else {
		// 	$start = ($this->page - 1) * $this->items;
		// 	mydb::value('$LIMIT$', 'LIMIT '.$start.','.$this->items);

		// 	$dbs = mydb::select(
		// 		'SELECT
		// 		log.*
		// 		, u.`name` `user_name`
		// 		FROM
		// 		(SELECT
		// 			l.*
		// 			FROM %counter_log% AS l
		// 			%WHERE%
		// 			ORDER BY l.`id` DESC
		// 			$LIMIT$
		// 		) log
		// 			LEFT JOIN %users% AS u ON log.`user` = u.`uid`'
		// 	);
		// }
		// // debugMsg(mydb()->_query);

		// return $dbs;
	}
}
?>