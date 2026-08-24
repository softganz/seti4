<?php
/**
 * Watchdog :: Analysis
 * Author   :: Little Bear<softganz@gmail.com>
 * Created  :: 2020-01-01
 * Modified :: 2026-08-24
 * Version  :: 7
 *
 * @return Widget
 *
 * @uses watchdog/analysis
 */

use Softganz\DB;

class WatchdogAnalysis extends Page {
	var $module;
	var $keyword;
	var $userId;
	var $keyId;
	var $ip;
	var $message;
	var $delete;
	var $days = 30;
	var $items =  1000;
	var $right;

	function __construct() {
		parent::__construct([
			'module' => post('module'),
			'keyword' => post('keyword'),
			'userId' => post('user'),
			'keyId' => Request::all('keyId'),
			'ip' => Request::all('ip'),
			'message' => post('message'),
			'delete' => post('delete'),
			'days' => SG\getFirst(post('d'), $this->days),
			'items' => SG\getFirstInt(post('items'), $this->items),
			'right' => (Object)[
				'access' => user_access('access logs'),
				'admin' => user_access('administer watchdogs')
			]
		]);
	}

	function rightToBuild() {
		if (!$this->right->access) return error(_HTTP_ERROR_FORBIDDEN, _ERROR_MSG_ACCESS_DENIED);

		return true;
	}

	function build() {
		$summaryDbs = $this->getSummary();

		return new Scaffold([
			'appBar' => new AppBar([
				'title' => 'Watchdog Anlysis'.' '.number_format($summaryDbs->totalLog).' items in '.number_format($summaryDbs->totalDate).' days',
				'navigator' => new Form([
					'class' => 'form-report',
					'action' => url('watchdog/analysis'),
					'method' => 'GET',
					'children' => [
						'd' => ['type' => 'text', 'label' => 'Show Last ', 'class' => '-numeric', 'size' => 6, 'value' => $this->days, 'posttext' => ' days'],
						'debug' => ['type' => 'hidden', 'value' => post('debug')],
						'go' => ['type' => 'button', 'value' => '<i class="icon -material">search</i><span>GO</span>'],
						// $this->right->admin ? '<a class="sg-action btn -link" href="'.url('watchdog/analysis',array('delete'=>'emptymodule')).'" data-confirm="ต้องการลบข้อมูล กรุณายืนยัน?" data-rel="notify"><i class="icon -material">delete</i><span>Clear empty module on watchlog</span></a>&nbsp;' : NULL,
					], // children
				]), // Form
			]), // AppBar
			'body' => new Widget([
				'children' => [
					new Table([
						'class' => 'watchdog-keyword-list',
						'thead' => ['Module', 'Keyword', 'amt' => 'Count', 'icons' => ''],
						'children' => array_map(
							function($rs) {
								return [
									new Button([
										'class' => 'sg-action',
										'href' => Url::link('watchdog/analysis..logs', ['d' => $this->days, 'module' => $rs->module]),
										'text' => $rs->module,
										'rel' => 'box',
										'data-width' => 'full',
									]),
									new Button([
										'class' => 'sg-action',
										'href' => Url::link('watchdog/analysis..logs', ['d' => $this->days, 'module' => $rs->module, 'keyword' => urlencode($rs->keyword)]),
										'text' => $rs->keyword,
										'rel' => 'box',
										'data-width' => 'full',
									]),
									number_format($rs->totals),
									$this->right->admin ? new Button([
										'class' => 'sg-action',
										'href' => Url::link('watchdog/analysis..delete', ['delete' => $rs->module.':'.$rs->keyword]),
										'data-title' => 'Delete logs',
										'data-confirm' => 'ต้องการลบ log ชุดนี้ กรุณายืนยัน?',
										'rel' => 'none',
										'done' => 'remove:parent tr',
										'icon' => new Icon('delete'),
									]) : NULL,
								];
							},
							(Array) DB::select([
								'SELECT
								`module`
								, `keyword`
								, COUNT(*) `totals`
								FROM %watchdog%
								%WHERE%
								GROUP BY `module`, `keyword`',
								'where' => [
									'%WHERE%' => [
										is_numeric($this->days) ? ['`date` >= :date', ':date' => date('Y-m-d 00:00:00', strtotime('today - '.$this->days.' days'))] : NULL,
									]
								]
							])->items
						),
					]), // Table
				], // children
			]), // Widget
		]);
	}

	private function getSummary() {
		return DB::select([
			'SELECT
			COUNT(*) `totalLog`, COUNT(DISTINCT DATE(`date`)) `totalDate`
			FROM %watchdog%
			LIMIT 1'
		]);
	}

	function logs() {
			$logs = DB::select([
				'SELECT
					`watch`.`wid`, `watch`.`date`, `watch`.`module`, `watch`.`keyword`, `watch`.`message`
					, `watch`.`ip`, `watch`.`keyid`, `watch`.`fldname`, `watch`.`url`, `watch`.`referer`, `watch`.`browser`
					, `user`.`uid`, `user`.`username`
				FROM %watchdog% AS `watch`
					LEFT JOIN %users% AS `user` ON `watch`.`uid` = `user`.`uid`
				%WHERE%
				ORDER BY `watch`.`wid` DESC
				LIMIT $ITEMS$',
				'%WHERE%' => [
					is_numeric($this->days) ? ['`watch`.`date` >= :date', ':date' => date('Y-m-d 00:00:00', strtotime('today - '.$this->days.' days'))] : NULL,
					$this->module ? ['`watch`.`module` = :module', ':module' => $this->module] : NULL,
					$this->keyword ? ['`watch`.`keyword` = :keyword',':keyword' => $this->keyword] : NULL,
					$this->userId ? ['`watch`.`uid` = :userId',':userId' => $this->userId] : NULL,
					$this->keyId ? ['`watch`.`keyId` = :keyId',':keyId' => $this->keyId] : NULL,
					$this->ip ? ['`watch`.`ip` = :ip',':ip' => ip2long($this->ip)] : NULL,
					$this->message ? ['`watch`.`message` LIKE :message',':message' => '%'.$this->message.'%'] : NULL,
				],
				'var' => ['$ITEMS$' => $this->items]
			]);

			return new Scaffold([
				'appBar' => new AppBar([
					'title' => 'Watchdog List '.$logs->count.' items'.($logs->count === $this->items ? ' and mores ' : ''),
					'boxHeader' => true,
				]),
				'body' => R::View('watchdog.listing',$logs),
			]);
	}

	function delete() {
		if ($this->delete == 'emptymodule') {
			DB::query(['DELETE FROM %watchdog% WHERE `module` IS NULL OR `module` = ""']);
		} else {
			list($module, $keyword) = explode(':', $this->delete);
			DB::query([
				'DELETE FROM %watchdog%
				%WHERE%',
				'where' => [
					'%WHERE%' => [
 						$module ? ['`module` = :module', ':module' => $module] : ['`module` IS NULL'],
						$keyword ? ['`keyword` = :keyword', ':keyword' => $keyword] : NULL,
					]
				]
			]);
		}
		return apiSuccess('Delete watchdog completed.');
	}
}
?>